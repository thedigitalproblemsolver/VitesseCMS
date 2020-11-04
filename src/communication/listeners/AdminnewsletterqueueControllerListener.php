<?php declare(strict_types=1);

namespace VitesseCms\Communication\Listeners;

use VitesseCms\Communication\Controllers\AdminnewsletterqueueController;
use VitesseCms\Communication\Models\NewsletterQueue;
use Phalcon\Events\Event;

class AdminnewsletterqueueControllerListener
{
    public function adminListItem(
        Event $event,
        AdminnewsletterqueueController $controller,
        NewsletterQueue $newsletterQueue
    ): void {
        $newsletterQueue->setAdminListExtra($controller->view->renderModuleTemplate(
            'communication',
            'newsletterQueueAdminListItem',
            'admin/',
            ['newsletterQueue' => $newsletterQueue]
        ));

        $newsletterQueue->setAdminListName($newsletterQueue->_('email'));
    }
}
