<?php declare(strict_types=1);

namespace VitesseCms\Media;

use VitesseCms\Media\Helpers\BootstrapAssetsService;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../core/services/BootstrapService.php';
require_once __DIR__.'/services/BootstrapAssetsService.php';

$bootstrap = new BootstrapAssetsService();
$bootstrap->router()->view();

echo $bootstrap->application()->handle()->getContent();
