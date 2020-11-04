<?php declare(strict_types=1);

namespace VitesseCms\Media\Helpers;

use VitesseCms\Core\Services\BootstrapService;
use Phalcon\Mvc\Router;

class BootstrapAssetsService extends BootstrapService
{
    public function router(): BootstrapService
    {
        $this->setShared(
            'router',
            function (): Router {
                $router = new Router();
                $router->setDefaultNamespace('VitesseCms\Media\Controllers');
                $router->setDefaultModule('media');
                $router->setDefaultController('assets');
                $router->setDefaultAction('index');

                return $router;
            }
        );

        return $this;
    }
}
