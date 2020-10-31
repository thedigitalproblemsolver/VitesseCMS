<?php declare(strict_types=1);

namespace VitesseCms\Form\Listeners;

use Phalcon\Events\Manager;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach(
            'AdminsubmissionController',
            new AdminsubmissionControllerListener()
        );
    }
}
