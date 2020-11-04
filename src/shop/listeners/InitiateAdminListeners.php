<?php declare(strict_types=1);

namespace VitesseCms\Shop\Listeners;

use Phalcon\Events\Manager;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach('AdminorderController', new AdminorderControllerListener());
        $eventsManager->attach('AdminorderstateController', new AdminorderstateControllerListener());
    }
}
