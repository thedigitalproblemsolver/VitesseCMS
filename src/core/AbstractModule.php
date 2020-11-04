<?php declare(strict_types=1);

namespace VitesseCms\Core;

use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;

abstract class AbstractModule implements ModuleDefinitionInterface
{
    public function registerAutoloaders(DiInterface $di = null) {}

    public function registerServices(DiInterface $di, string $module = null)
    {
        $di->set(
            'dispatcher',
            function () use ($module): Dispatcher {
                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace('VitesseCms\\' . $module . '\\Controllers');

                return $dispatcher;
            }
        );
    }
}
