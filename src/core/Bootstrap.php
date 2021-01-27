<?php declare(strict_types=1);

namespace VitesseCms\Core;

use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Core\Services\BootstrapService;
use Phalcon\Exception;
use VitesseCms\Core\Utils\DebugUtil;
use VitesseCms\Core\Utils\SystemUtil;

require_once __DIR__.'/services/BootstrapService.php';
require_once __DIR__ . '/../core/AbstractInjectable.php';
require_once __DIR__ . '/../core/services/AbstractInjectableService.php';
require_once __DIR__ . '/../core/services/CacheService.php';
require_once __DIR__ . '/../core/services/UrlService.php';
require_once __DIR__ . '/../../../configuration/src/services/ConfigService.php';
require_once __DIR__ . '/../core/utils/DirectoryUtil.php';
require_once __DIR__ . '/../core/utils/SystemUtil.php';
require_once __DIR__ . '/../core/utils/BootstrapUtil.php';
require_once __DIR__ . '/../../../configuration/src/utils/AbstractConfigUtil.php';
require_once __DIR__ . '/../../../configuration/src/utils/AccountConfigUtil.php';
require_once __DIR__ . '/../../../configuration/src/utils/DomainConfigUtil.php';
require_once __DIR__ . '/../core/utils/DebugUtil.php';

$cacheLifeTime = 604800;
if (DebugUtil::isDocker($_SERVER['SERVER_ADDR'])) :
    $cacheLifeTime = 1;
endif;


$cacheKey = null;
$bootstrap = (new BootstrapService())
    ->setSession()
    ->setCache($cacheLifeTime)
    ->setUrl()
    ->loadConfig();

if (
    empty($_POST)
    && empty($_SESSION)
    && (count($_GET) === 0 || isset($_GET['_url']))
    && !substr_count('admin', $_SERVER['REQUEST_URI'])
    && !$bootstrap->getConfiguration()->hasMovedTo()
) :
    $cacheKey = str_replace('/', '_', $_SERVER['REQUEST_URI']);
    $cacheResult = $bootstrap->getCache()->get($cacheKey);
    if ($cacheResult !== null) :
        echo $cacheResult;
        die();
    endif;
endif;

$bootstrap
    ->loaderSystem()
    ->database()
    ->setLanguage()
    ->setCookies()
    ->security()
    ->flash()
    ->user()
    ->view()
    ->queue()
    ->events()
    ->setting()
    ->content()
    ->mailer()
    ->shop()
    ->log()
    ->router()
    ->acl()
    ->assets()
    ->block()
    ->form()
    ->search()
;

$application = $bootstrap->application();

try {
    if (!AdminUtil::isAdminPage()) :
        $content = $application->content->parseContent($application->handle()->getContent());
        if ($cacheKey !== null) :
            $application->cache->save($cacheKey, $content);
        endif;

        echo $content;
    else :
        echo $application->content->parseContent(
            $application->handle()->getContent(),
            false,
            false
        );
    endif;
} catch (Exception $e) {
    if(DebugUtil::isDocker($_SERVER['SERVER_ADDR'])) :
        var_dump($e->getMessage());
        die();
    endif;
    $application->router->doRedirect($application->url->getBaseUri());
}
