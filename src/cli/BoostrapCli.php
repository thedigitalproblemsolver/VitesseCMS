<?php declare(strict_types=1);

namespace VitesseCms\Cli;

use VitesseCms\Core\Services\ConfigService;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Configuration\Utils\AccountConfigUtil;
use VitesseCms\Core\Utils\BootstrapUtil;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Configuration\Utils\DomainConfigUtil;
use VitesseCms\Core\Utils\SystemUtil;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\Loader;
use Phalcon\http\Request;

class BoostrapCli extends Cli
{
    /**
     * @var string
     */
    protected $systemDir;

    public function __construct()
    {
        parent::__construct();

        $this->systemDir = str_replace('cli', '', __DIR__);
    }

    public function loaderSystem(): Loader
    {
        $loader = new Loader();
        $loader->registerDirs([$this->systemDir.'core/helpers/', $this->systemDir.'core/utils/',])->register();
        $loader->registerNamespaces(
            [
                'VitesseCms\\Core\\Helpers' => $this->systemDir.'core/helpers/',
                'VitesseCms\\Core\\Utils'   => $this->systemDir.'core/utils/',
            ]
        );
        $loader = BootstrapUtil::addModulesToLoader(
            $loader,
            SystemUtil::getModules($this->getConfiguration()),
            $this->getConfiguration()->getAccount()
        );

        return $loader;
    }

    public function loadConfig(): BoostrapCli
    {
        $domainConfig = new DomainConfigUtil();
        $domainConfig->merge(new AccountConfigUtil($domainConfig->get('account')));
        $domainConfig->setDirectories();
        $domainConfig->setTemplate();

        $this->setShared('config', $domainConfig);
        $this->setShared('configuration', new ConfigService($domainConfig, $this->get('url')));

        return $this;
    }

    public function setUrl(): BoostrapCli
    {
        $this->setShared('url', new UrlService(new Request()));

        return $this;
    }

    public function getConfiguration(): ConfigService
    {
        return $this->get('configuration');
    }
}
