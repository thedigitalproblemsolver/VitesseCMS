<?php declare(strict_types=1);

namespace VitesseCms\Admin\Listeners;

use Phalcon\Events\Manager;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach('AdmindatagroupController', new AdmindatagroupControllerListener());
    }
}
