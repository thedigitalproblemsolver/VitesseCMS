<?php declare(strict_types=1);

namespace VitesseCms\Media\Helpers;

use VitesseCms\Core\Services\BootstrapService;
use Phalcon\Mvc\Router;

class BootstrapMediaService extends BootstrapService
{
    public function router(): BootstrapService
    {
        $this->setShared(
            'router',
            function (): Router {
                $router = new Router();
                $router->setDefaultNamespace('VitesseCms\Media\Controllers');
                $router->setDefaultModule('media');
                $router->setDefaultController('image');
                $router->setDefaultAction('index');

                return $router;
            }
        );

        return $this;
    }
}
