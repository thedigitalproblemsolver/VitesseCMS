<?php declare(strict_types=1);

namespace VitesseCms\Core;

use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Core\Services\BootstrapService;
use Phalcon\Exception;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/services/BootstrapService.php';

$bootstrap = new BootstrapService();
$cacheKey = null;

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

$bootstrap->setCookies()
    ->security()
    ->database()
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
    ->assets();

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
    $application->router->doRedirect($application->url->getBaseUri());
}
