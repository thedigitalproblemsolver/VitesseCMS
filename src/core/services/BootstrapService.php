<?php declare(strict_types=1);

namespace VitesseCms\Core\Services;

use Phalcon\Http\Request;
use Phalcon\Loader;
use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Communication\Services\MailerService;
use VitesseCms\Content\Services\ContentService;
use VitesseCms\Core\CoreApplicaton;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Core\Utils\AccountConfigUtil;
use VitesseCms\Core\Utils\DebugUtil;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Configuration\Utils\DomainConfigUtil;
use VitesseCms\Core\Utils\SystemUtil;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Language\Models\Language;
use VitesseCms\Media\Services\AssetsService;
use VitesseCms\Language\Services\LanguageService;
use VitesseCms\Mustache\Engine;
use VitesseCms\Mustache\Loader_FilesystemLoader;
use VitesseCms\Mustache\MustacheEngine;
use VitesseCms\Setting\Services\SettingService;
use VitesseCms\Shop\Helpers\CartHelper;
use VitesseCms\Shop\Helpers\CheckoutHelper;
use VitesseCms\Shop\Helpers\DiscountHelper;
use VitesseCms\Shop\Listeners\DiscountListener;
use VitesseCms\Shop\Services\ShopService;
use VitesseCms\User\Factories\UserFactory;
use VitesseCms\User\Models\User;
use VitesseCms\User\Services\AclService;
use MongoDB\Client;
use Phalcon\Crypt;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Manager;
use Phalcon\Http\Response\Cookies;
use Phalcon\Security;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Mvc\Collection\Manager as CollectionManager;

require_once __DIR__ . '/../interfaces/InjectableInterface.php';

class BootstrapService extends FactoryDefault implements InjectableInterface
{
    /**
     * @var string
     */
    protected $systemDir;

    /**
     * @var int
     */
    protected $mtime;

    public function __construct()
    {
        parent::__construct();

        $this->systemDir = str_replace('core/services', '', __DIR__);
        $this->mtime = (int)filemtime(__FILE__);

        require_once $this->systemDir . '/core/AbstractInjectable.php';
        require_once $this->systemDir . '/core/services/AbstractInjectableService.php';
        require_once $this->systemDir . '/core/services/CacheService.php';
        require_once $this->systemDir . '/core/services/UrlService.php';
        require_once $this->systemDir . '/core/services/ConfigService.php';
        require_once $this->systemDir . '/core/utils/DirectoryUtil.php';
        require_once $this->systemDir . '/core/utils/SystemUtil.php';
        require_once $this->systemDir . '/core/utils/AccountConfigUtil.php';
        require_once $this->systemDir . '/configuration/utils/DomainConfigUtil.php';
        require_once $this->systemDir . '/core/utils/DebugUtil.php';

        $this->setSession()
            ->setCache()
            ->setUrl()
            ->loadConfig()
            ->loaderSystem()
            ->database()
            ->setLanguage();
    }

    public function loadConfig(): BootstrapService
    {
        $cacheKey = $this->getCache()->getCacheKey('bootstrap-config-' . $this->mtime);
        $domainConfig = $this->getCache()->get($cacheKey);
        if (!$domainConfig) :
            $domainConfig = new DomainConfigUtil();
            $domainConfig->merge(new AccountConfigUtil($domainConfig->get('account')));
            $domainConfig->setDirectories();
            $domainConfig->setTemplate();

            $this->getCache()->save($cacheKey, $domainConfig);
        endif;

        $this->setShared('config', $domainConfig);
        $this->setShared('configuration', new ConfigService($domainConfig, $this->get('url')));
        $this->getUrl()->checkProtocol((bool)$domainConfig->get('https'));

        if ($this->getConfiguration()->hasMovedTo()) :
            $requestUri = substr($this->getRequest()->getURI(), 1);
            $link = $this->getConfiguration()->getMovedTo() . $requestUri;
            if (substr_count($requestUri, 'uploads') > 0) :
                $link = str_replace('/' . $this->getConfiguration()->getLanguageShort() . '/', '/', $link);
            endif;

            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $link);
            die();
        endif;

        return $this;
    }

    public function setLanguage(): BootstrapService
    {
        $domainConfig = $this->getConfiguration();
        if (!$domainConfig->hasLanguage()) :
            $uri = explode('/', $this->getRequest()->getURI());
            Language::setFindValue(
                'domain',
                $this->getUrl()->getProtocol() . '://' . $domainConfig->getHost() . '/' . $uri[1] . '/'
            );
            /** @var Language $language */
            $language = Language::findFirst();
            if (!$language) :
                Language::setFindValue(
                    'domain',
                    $this->getUrl()->getProtocol() . '://' . $domainConfig->getHost()
                );

                $language = Language::findFirst();
                if (!$language && DebugUtil::isDocker($this->getRequest()->getServerAddress())):
                    Language::setFindValue(
                        'domain',
                        'https://' . $domainConfig->getHost() . '/' . $uri[1] . '/'
                    );
                    $language = Language::findFirst();
                    if (!$language) :
                        Language::setFindValue(
                            'domain',
                            'https://' . $domainConfig->getHost()
                        );
                        $language = Language::findFirst();
                    endif;
                endif;
            endif;

            if ($language) :
                $this->getUrl()->setBaseUri($this->getUrl()->getBaseUri() . $uri[1] . '/');
                $this->getConfiguration()->setLanguage($language);
            endif;
        endif;
        $this->setShared('language', new LanguageService());

        return $this;
    }

    public function loaderSystem(): BootstrapService
    {
        $loaderCacheKey = $this->getCache()->getCacheKey('bootstrap-loader-' . $this->mtime);
        $loader = $this->getCache()->get($loaderCacheKey);

        if ($loader !== null) :
            $loader->register();

            return $this;
        endif;

        $loader = new Loader();
        $loader->registerDirs([$this->systemDir . 'core/helpers/'], true)
            ->registerDirs([$this->systemDir . 'core/utils/'], true)
            ->registerNamespaces(['VitesseCms\\Core\\Helpers' => $this->systemDir . 'core/helpers/'], true)
            ->registerNamespaces(['VitesseCms\\Core\\Utils' => $this->systemDir . 'core/utils/'], true);

        //TODO also handle models subdirs
        foreach (SystemUtil::getModules($this) as $moduleDir) :
            $moduleDirParts = explode('/', $moduleDir);
            $moduleDirParts = array_reverse($moduleDirParts);
            $moduleNamespace = ucfirst($moduleDirParts[0]);
            if ($moduleDirParts[2] === $this->get('config')->account) :
                $moduleNamespace = ucfirst($moduleDirParts[2]) . '\\' . $moduleNamespace;
            endif;

            $loader->registerDirs([$moduleDir], true);
            $loader->registerNamespaces(['VitesseCms\\' . $moduleNamespace => $moduleDir], true);
            $subDirs = DirectoryUtil::getChildren($moduleDir);
            foreach ($subDirs as $subDir) :
                $subDirParts = explode('/', $subDir);
                $subDirParts = array_reverse($subDirParts);
                $loader->registerDirs([$subDir], true);
                $loader->registerNamespaces(['VitesseCms\\' . $moduleNamespace . '\\' . ucfirst($subDirParts[0]) => $subDir], true);
            endforeach;
        endforeach;
        $this->getCache()->save($loaderCacheKey, $loader);

        $loader->register();

        return $this;
    }

    public function setCache(): BootstrapService
    {
        $this->setShared(
            'cache',
            new CacheService(
                $this->systemDir .
                '../cache/' .
                strtolower($this->getRequest()->getHttpHost()) .
                '/',
                604800
            )
        );

        return $this;
    }

    public function setUrl(): BootstrapService
    {
        $this->setShared('url', new UrlService($this->get('request')));

        return $this;
    }

    public function setSession(): BootstrapService
    {
        $this->setShared('session', function (): Session {
            $session = new Session();
            $session->start();

            return $session;
        });

        return $this;
    }

    public function setCookies(): BootstrapService
    {
        $this->set('crypt', (new Crypt())->setKey('Bk03%#2NBePi*fKj28CpsWM'));

        $this->setShared('cookies', new Cookies(true));

        return $this;
    }

    public function security(): BootstrapService
    {
        $this->setShared('security', function (): Security {
            $security = new Security();
            $security->setWorkFactor(12);

            return $security;
        });

        return $this;
    }

    public function database(): BootstrapService
    {
        $configuration = $this->getConfiguration();

        $this->setShared(
            'mongo',
            (new Client($configuration->getMongoUri()))
                ->selectDatabase($configuration->getMongoDatabase())
        );

        $this->setShared('collectionManager', new CollectionManager());

        return $this;
    }

    public function flash(): BootstrapService
    {
        $this->setShared('flash', new FlashService ([
                'error' => 'alert alert-danger',
                'success' => 'alert alert-success',
                'notice' => 'alert alert-info',
                'warning' => 'alert alert-warning',
            ])
        );

        return $this;
    }

    public function user(): BootstrapService
    {
        $this->setShared('user', function (): User {
            $user = UserFactory::createGuest();
            if ($this->get('session')->get('auth') !== null) :
                $result = User::findById($this->get('session')->get('auth')['id']);
                if ($result) :
                    $user = $result;
                endif;
            endif;

            return $user;
        });

        //initialize user to use in Bootstrap
        $this->getUser();

        return $this;
    }

    public function view(): BootstrapService
    {
        $this->setShared('view', function (): ViewService {
            $view = new ViewService($this->getConfiguration());
            $view->setViewsDir($this->getConfiguration()->getTemplateDir() . 'views/');
            $view->setPartialsDir($this->getConfiguration()->getTemplateDir() . 'views/partials/');
            $view->registerEngines(
                [
                    '.mustache' => function (ViewService $view): MustacheEngine {
                        return new MustacheEngine(
                            $view,
                            new Engine(['partials_loader' => new Loader_FilesystemLoader($this->getConfiguration()->getCoreTemplateDir() . 'views/partials/')]),
                            null
                        );
                    },
                ]
            );

            return $view;
        });

        return $this;
    }

    public function queue(): BootstrapService
    {
        $this->setShared('jobQueue', function (): BeanstalkService {
            $beanstalk = new BeanstalkService(
                [
                    'host' => $this->getConfiguration()->getBeanstalkHost(),
                    'port' => $this->getConfiguration()->getBeanstalkPort(),
                ]
            );
            $beanstalk->choose(md5($this->getUrl()->getBaseUri()));

            return $beanstalk;
        });

        return $this;
    }

    public function events(): BootstrapService
    {
        $this->getEventsManager()->attach('discount', new DiscountListener());
        $this->getEventsManager()->attach('user', new DiscountListener());

        foreach (SystemUtil::getModules($this) as $path) :
            $listenerPath = $path . '/listeners/InitiateListeners.php';
            if (AdminUtil::isAdminPage()):
                $listenerPath = $path . '/listeners/InitiateAdminListeners.php';
            endif;

            if (is_file($listenerPath)) :
                SystemUtil::createNamespaceFromPath($listenerPath)::setListeners($this->getEventsManager());
            endif;
        endforeach;

        return $this;
    }

    public function content(): BootstrapService
    {
        $this->setShared('content', new ContentService($this->getView()));

        return $this;
    }

    public function mailer(): BootstrapService
    {
        $this->setShared(
            'mailer',
            new MailerService(
                $this->getSetting(),
                $this->getConfiguration(),
                $this->get('content'),
                $this->getView()
            )
        );

        return $this;
    }

    public function shop(): BootstrapService
    {
        $this->setShared('shop',
            new ShopService(
                new CartHelper(),
                new DiscountHelper(),
                new CheckoutHelper()
            )
        );

        return $this;
    }

    public function log(): BootstrapService
    {
        $this->setShared('log', new LogService());

        return $this;
    }

    public function setting(): BootstrapService
    {
        $this->setShared('setting', new SettingService(
            $this->getCache(),
            $this->getConfiguration()
        ));

        return $this;
    }

    public function router(): BootstrapService
    {
        $this->setShared('router', new RouterService(
            $this->getUser(),
            $this->getRequest(),
            $this->getConfiguration(),
            $this->getUrl(),
            $this->getCache(),
            $this->getView(),
            new ItemRepository()
        ));

        if (
            !empty($this->getView()->getVar('currentItem'))
            && !ItemHelper::checkAccess(
                $this->getUser(),
                $this->getView()->getVar('currentItem')
            )
        ) :
            $this->get('flash')->_('USER_NO_ACCESS', 'error');
            header('HTTP/1.1 401 Unauthorized');
            header('Location: ' . $this->get('url')->getBaseuri());
            die();
        endif;

        $this->set('currentItem', $this->getView()->getVar('currentItem'));

        return $this;
    }

    public function assets(): BootstrapService
    {
        $this->setShared('assets', new AssetsService($this->getConfiguration()->getWebDir()));

        return $this;
    }

    public function acl(): BootstrapService
    {
        $this->setShared('acl', new AclService(
            $this->getUser(),
            $this->getRouter()
        ));

        return $this;
    }

    public function application(): CoreApplicaton
    {
        $application = new CoreApplicaton($this);

        $cacheKey = $this->getCache()->getCacheKey('bootstrap-application-' . $this->mtime);
        $registerModules = $this->getCache()->get($cacheKey);
        if (!$registerModules) :
            $modules = DirectoryUtil::getChildren($this->systemDir);

            $registerModules = [];
            foreach ($modules as $moduleName => $dir) :
                $registerModules[$moduleName] = [
                    'className' => 'VitesseCms\\' . ucfirst($moduleName) . '\\Module',
                    'path' => $dir . '/Module.php',
                ];
            endforeach;
            $this->getCache()->save($cacheKey, $registerModules);
        endif;
        $application->registerModules($registerModules);
        $this->set('app', $application);

        return $application;
    }

    public function getConfiguration(): ConfigService
    {
        return $this->get('configuration');
    }

    public function getSetting(): SettingService
    {
        return $this->get('setting');
    }

    public function getCache(): CacheService
    {
        return $this->get('cache');
    }

    public function getUrl(): UrlService
    {
        return $this->get('url');
    }

    public function getRequest(): Request
    {
        return $this->get('request');
    }

    public function getSession(): Session
    {
        return $this->get('session');
    }

    public function getEventsManager(): Manager
    {
        return $this->get('eventsManager');
    }

    public function getUser(): User
    {
        return $this->get('user');
    }

    public function getRouter(): RouterService
    {
        return $this->get('router');
    }

    public function getView(): ViewService
    {
        return $this->get('view');
    }
}
