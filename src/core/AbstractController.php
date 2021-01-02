<?php declare(strict_types=1);

namespace VitesseCms\Core;

use VitesseCms\Admin\Repositories\DatagroupRepository;
use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Block\Models\BlockPosition;
use VitesseCms\Block\Repositories\BlockPositionRepository;
use VitesseCms\Block\Repositories\BlockRepository;
use VitesseCms\Communication\Helpers\CommunicationHelper;
use VitesseCms\Content\Factories\OpengraphFactory;
use VitesseCms\Core\Helpers\HtmlHelper;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Core\Utils\DebugUtil;
use VitesseCms\Core\Utils\TimerUtil;
use VitesseCms\Core\Utils\UrlUtil;
use VitesseCms\Export\Helpers\RssExportHelper;
use VitesseCms\Export\Models\ExportType;
use VitesseCms\Media\Enums\AssetsEnum;
use VitesseCms\Setting\Models\Setting;
use VitesseCms\User\Utils\PermissionUtils;
use MongoDB\BSON\ObjectID;
use Phalcon\Assets\Filters\Cssmin;
use Phalcon\Assets\Filters\Jsmin;
use Phalcon\Mvc\Controller;
use Phalcon\Tag;

abstract class AbstractController extends Controller implements InjectableInterface
{
    /**
     * @var array
     */
    protected $parseAsJob = [];

    /**
     * @var bool
     */
    protected $isJobProcess = false;

    /**
     * constructor
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function onConstruct()
    {
        if (PermissionUtils::check(
            $this->user,
            $this->view->getVar('aclModulePrefix').$this->router->getModuleName(),
            $this->router->getControllerName(),
            $this->router->getActionName()
        )) :
            if (\in_array($this->router->getActionName(), $this->parseAsJob, true)) :
                $this->jobQueue->createByController($this);
                $this->redirect();
                die();
            else :
                $settings = Setting::findAll();
                foreach ($settings as $setting) :
                    $this->view->set($setting->_('calling_name'), $setting->_('value'));
                    $this->view->setVar($setting->_('calling_name'), $setting->_('value'));
                endforeach;
                $this->view->set('ACCOUNT', $this->config->get('account'));
                $this->view->set('BASE_URI', $this->url->getBaseUri());
                $this->view->set('UPLOAD_URI', $this->configuration->getUploadUri());
                $this->view->set('ECOMMERCE', (string)$this->configuration->isEcommerce());
            endif;
        else :
            $this->log->write(
                new ObjectID($this->view->getVar('currentId')),
                Item::class,
                'access denied for : '.$this->view->getVar('aclModulePrefix').$this->router->getModuleName().'/'.$this->router->getControllerName().'/'.$this->router->getActionName()
            );

            $this->flash->_('USER_NO_ACCESS', 'error');
            $this->response->setStatusCode(401, 'Unauthorized')->redirect('');
            die();
        endif;
    }

    public function redirect(
        ?string $url = null,
        array $ajaxParams = [],
        bool $showAlert = true,
        bool $forcePageReload = false
    ): void {
        $result = true;
        if ($this->flash->has('error')) :
            $result = false;
        endif;

        CommunicationHelper::sendRedirectEmail($this->router, $this->view);

        if ($url === null) :
            $url = $this->request->getServer('HTTP_REFERER');
        endif;

        if ($this->request->hasQuery('embedded')) :
            $url = $this->url->addParamsToQuery('embedded', '1', $url);
        endif;

        if (!$forcePageReload && $this->request->isAjax()) :
            $ajaxParams['result'] = $result;
            if ($showAlert) :
                $ajaxParams['alert'] = $this->getFlash();
            endif;

            if ($url !== null && !isset($ajaxParams['successFunction'])) :
                $ajaxParams['successFunction'] = "redirect('".$url."')";
            endif;

            $this->cache->setNoCacheHeaders();
            $this->response->setContentType('application/json', 'UTF-8');
            echo json_encode($ajaxParams);
            $this->response->send();
            die();
        elseif (!$this->isJobProcess) :
            $this->response->redirect($url);
            $this->response->send();
            die();
        endif;

        $this->disableView();
    }

    /**
     * @param array $data
     * @param bool $result
     */
    protected function prepareJson(array $data, bool $result = true): void
    {
        $this->response->setContentType('application/json', 'UTF-8');
        echo json_encode(array_merge(['result' => $result], $data));
        $this->response->send();
        die();
    }

    /**
     * prepare all HTML stuff for view
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    protected function prepareView(): void
    {
        $this->view->setVar('siteUrl', $this->url->getBaseUri());
        if ($this->request->isAjax()) :
            $this->prepareAjaxView();
        elseif ((bool)$this->request->get('embedded', 'int', 0)) :
            $this->prepareEmbeddedView();
        else :
            $this->prepareHtmlView();
        endif;
    }

    //TODO to admin controller?
    protected function adminToolbar(): string
    {
        if (!$this->user->hasAdminAccess()) :
            return '';
        endif;

        $this->view->setVar('bodyClass', 'admin');

        return (new AdminUtil(
            $this->setting,
            $this->user,
            $this->eventsManager,
            $this->view,
            new DatagroupRepository()
        ))->toolbar();
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    protected function parsePositions(): void
    {
        foreach ($this->configuration->getTemplatePositions() as $position => $tmp) :
            if (
                $position !== 'maincontent'
                || (
                    $position === 'maincontent'
                    && $this->view->hasCurrentItem()
                )
            ) :
                $this->parsePosition($position);
            endif;
        endforeach;
    }

    /**
     * @param string $position
     *
     * parse template position
     *
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    protected function parsePosition($position): void
    {
        $content = '';

        $dataGroups = ['all'];
        if ($this->view->hasCurrentItem()) :
            $dataGroups[] = 'page:'.$this->view->getCurrentItem()->getId();
            $dataGroups[] = $this->view->getCurrentItem()->getDatagroup();
        endif;

        $blockPositions = (new BlockPositionRepository())->getByPositionNameAndDatagroup($position, $dataGroups);;
        while ($blockPositions->valid() ) :
            $blockPosition = $blockPositions->current();

            $content .= $blockPosition->render(
                $this->view,
                $this->user,
                new BlockRepository(),
                $this->cache
            );

            $blockPositions->next();
        endwhile;

        $classes = ['container-'.$position];
        $layoutClass = $this->setting->get('layout_blockposition-class'.$position);
        if (!empty($layoutClass)):
            if (is_string($layoutClass)):
                $classes[] = $layoutClass;
            elseif (is_array($layoutClass)) :
                $layoutClass = reset($layoutClass);
                if (!empty($layoutClass)) :
                    $classes[] = $layoutClass;
                endif;
            endif;
        endif;

        if (empty($content)) :
            $this->view->setVar($position, false);
        else :
            $contentExtra = '';
            if (PermissionUtils::check(
                $this->user,
                'block',
                'adminblockposition',
                'edit')
            ) :
                BlockPosition::setFindPublished(false);
                $contentExtra = '<div class="position-toolbar row">
                <div class="col-12">
                    <a
                        class="fa fa-plus btn btn-info openmodal"
                        href="admin/block/adminblockposition/edit"
                    ></a>
                    Position : '.$position.'
                </div>
            </div>';
            endif;

            $this->view->setVar(
                $position,
                '<div '.HtmlHelper::makeAttribute($classes, 'class').' >'
                .$contentExtra.$content.
                '</div>'
            );
        endif;
    }

    /**
     * loadAssets
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    protected function loadAssets(): void
    {
        $this->assets->load(AssetsEnum::JQUERY);
        $this->assets->load(AssetsEnum::BOOTSTRAP_JS);
        $this->assets->load(AssetsEnum::MUSTACHE_JS);
        $this->assets->load(AssetsEnum::FONT_AWESOME);
        $this->assets->load(AssetsEnum::SHOP);

        if (!DebugUtil::isDev()) :
            if ($this->setting->has('FACEBOOK_PIXEL_ID')) :
                $this->assets->load(AssetsEnum::FACEBOOK);
            endif;
            if ($this->setting->has('GOOGLE_ANALYTICS_TRACKINGID')) :
                $this->assets->load(AssetsEnum::GOOGLE);
            endif;

            if (
                !$this->user->hasAdminAccess()
                && $this->view->getVar('embedded') !== 1
                && $this->setting->has('COOKIECONSENT_POPUP_BACKGROUNDCOLOR')
                && $this->setting->has('COOKIECONSENT_POPUP_TEXTCOLOR')
                && $this->setting->has('COOKIECONSENT_BUTTON_BACKGROUNDCOLOR')
                && $this->setting->has('COOKIECONSENT_BUTTON_TEXTCOLOR')
                && $this->setting->has('COOKIECONSENT_CONTENT_MESSAGE')
                && $this->setting->has('COOKIECONSENT_CONTENT_DISMISS')
                && $this->setting->has('COOKIECONSENT_CONTENT_LINK')
                && $this->setting->has('COOKIECONSENT_CONTENT_URL')
                && (
                    $this->cookies->has('cookieconsent_status')
                    && $this->cookies->get('cookieconsent_status')->getValue() !== 'dismiss'
                )
            ) :
                $this->assets->load(AssetsEnum::COOKIECONSENT);
                $this->view->setVar('showCookieConsent', true);
            endif;
        else :
            $this->assets->load(AssetsEnum::GOOGLE);
        endif;

        if ($this->user->hasAdminAccess()) :
            $this->assets->loadAdmin();
        endif;
        $this->assets->load('site');
        $this->buildAssets('js');
        $this->buildAssets('css');
    }

    /**
     * @param string $type
     */
    protected function buildAssets(string $type): void
    {
        $collection = $this->assets->collection($type);
        $collectionExternal = $this->assets->collection('external'.$type);
        $fileBase = 'assets/'.$this->configuration->getAccount().'/'.$type.'/site.'.$type;
        $cacheHash = '';
        $addFunction = 'add'.ucfirst($type);

        if (is_file($this->configuration->getWebDir().$fileBase)) :
            $cacheHash .= filemtime($this->configuration->getWebDir().$fileBase);
            $collection->$addFunction($fileBase);
        endif;

        foreach ($this->assets->getByType($type) as $file) :
            $link = 'assets/default/'.$type.'/'.$file;
            if (is_file($link)) :
                $cacheHash .= filemtime($link);
                if (substr_count($file, '.'.$type) === 0) :
                    $link = $this->url->getBaseUri().$file.'?v='.filemtime($link);
                endif;
                $collection->$addFunction($link);
            else :
                $collectionExternal->$addFunction($file, false);
            endif;
        endforeach;

        $filename = md5($cacheHash);
        $combinedFile = 'assets/'.$this->configuration->getAccount().'/'.$type.'/cache/'.$filename.'.'.$type;

        $collection->join(true);
        $collection->setTargetPath($this->configuration->getWebDir().$combinedFile);
        $collection->setTargetUri($combinedFile);
        switch ($type) :
            case 'js':
                if (!is_file($this->configuration->getWebDir().$combinedFile)) :
                    $collection->addFilter(new Jsmin());
                    $this->assets->outputJs($type);
                endif;

                $tags = '';
                ob_start();
                $this->assets->outputJs('external'.$type);
                $tags .= ob_get_contents();
                ob_end_clean();
                $tags .= Tag::javascriptInclude($combinedFile);

                $this->view->setVar('javascript', $tags);
                break;
            case 'css':
                if (!is_file($this->configuration->getWebDir().$combinedFile)) :
                    $collection->addFilter(new Cssmin());
                    $this->assets->outputCss($type);
                endif;

                $tags = '';
                ob_start();
                $this->assets->outputCss('external'.$type);
                $tags .= ob_get_contents();
                ob_end_clean();
                $tags .= Tag::stylesheetLink($combinedFile);

                $this->view->setVar('stylesheet', $tags);
                break;
        endswitch;
    }

    /**
     * prepare for AJAX view
     */
    public function prepareAjaxView(): void
    {
        echo $this->getFlash();
        echo $this->view->getVar('content');
        $this->view->disable();
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function prepareHtmlView(): void
    {
        $this->view->setVar('adminToolbar', $this->adminToolbar());
        if ($this->view->hasCurrentItem() && $this->view->getCurrentItem()->isHomepage()) :
            $this->view->setVar('bodyClass', $this->view->getVar('bodyClass').' home');
        endif;

        $this->parsePositions();
        $this->loadAssets();
        $this->prepareViewValues();

        ExportType::setFindValue('type', RssExportHelper::class);
        $this->view->setVar('rssFeeds', ExportType::findAll());
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function prepareEmbeddedView(): void
    {
        $this->prepareViewValues();
        $this->view->setVar('embedded', 1);
        $this->view->setVar('bodyClass', $this->view->getVar('bodyClass').' embedded container-fluid');

        if ($this->view->getVar('content') === null) :
            $this->parsePosition('maincontent');
            $this->view->setVar('content', $this->view->getVar('maincontent'));
        endif;

        $this->loadAssets();
    }

    /**
     * @return string
     */
    public function getFlash(): string
    {
        /** @noinspection PhpUndefinedMethodInspection */
        if ($this->flash->has()) :
            ob_start();
            $this->flash->output();
            $flash = ob_get_contents();
            ob_end_clean();

            return $flash;
        endif;

        return '';
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    protected function prepareViewValues(): void
    {
        $this->view->setVar('flash', $this->getFlash());
        $this->view->setVar('currentItem', $this->view->getVar('currentItem'));
        $this->view->setVar('BASE_URI', $this->view->getVar('BASE_URI'));
        $this->view->setVar('UPLOAD_URI', $this->configuration->getUploadUri());
        $this->view->setVar('ACCOUNT', $this->config->get('account'));
        $this->view->setVar('ECOMMERCE', (string)$this->configuration->isEcommerce());
        $this->view->setVar('hrefLanguages', $this->view->getVar('hrefLanguages'));
        $this->view->setVar('timer', TimerUtil::Results(false));
        $this->view->setVar('isDev', DebugUtil::isDev());
        $this->view->setVar('hideAsideMenu', $this->config->get('hideAsideMenu'));
        $this->view->setVar('languageLocale', $this->configuration->getLanguageLocale());
        if (is_string($this->setting->get('SITE_LABEL_MOTTO'))) {
            $this->view->setVar('SITE_TITLE_LABEL_MOTTO', strip_tags(
                    str_replace(
                        '<br>',
                        ' ',
                        $this->setting->get('SITE_LABEL_MOTTO')
                    )
                )
            );
        }

        if ($this->view->getVar('currentId')) :
            $this->view->setVar(
                'opengraph',
                OpengraphFactory::createFormItem(
                    $this->view->getVar('currentItem'),
                    $this->setting,
                    $this->configuration
                )->renderTags()
            );
        endif;

        $this->view->setVar('SEO_ROBOTS', 'index, follow');
        if (AdminUtil::isAdminPage()) :
            $this->view->setVar('IS_ADMIN', true);
            $this->view->setVar('SEO_ROBOTS', 'noindex, nofollow');
        endif;
    }

    public function disableView(): void
    {
        CommunicationHelper::sendRedirectEmail($this->router, $this->view);
        $this->flash->output();
        $this->view->disable();
    }

    public function setIsJobProcess(bool $bool): void
    {
        $this->isJobProcess = $bool;
    }
}
