<?php declare(strict_types=1);

namespace VitesseCms\Communication\Listeners;

use Phalcon\Events\Manager;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach('AdminnewsletterController', new AdminnewsletterControllerListener());
        $eventsManager->attach('AdminnewsletterqueueController', new AdminnewsletterqueueControllerListener());
        $eventsManager->attach('AdminnewsletterlistController', new AdminnewsletterlistControllerListener());
        $eventsManager->attach('AdminemailController', new AdminemailControllerListener());
    }
}
