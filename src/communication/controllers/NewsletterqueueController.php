<?php declare(strict_types=1);

namespace VitesseCms\Communication\Controllers;

use VitesseCms\Communication\Helpers\NewsletterQueueHelper;
use VitesseCms\Communication\Interfaces\RepositoriesInterface;
use VitesseCms\Core\AbstractController;

class NewsletterqueueController extends AbstractController implements RepositoriesInterface
{
    public function unsubscribeAction(string $newsletterQueueId): void
    {
        $newsletterQueue = $this->repositories->newsletterQueue->getById($newsletterQueueId);
        if($newsletterQueue) :
            $newsletterList = $this->repositories->newsletterList->getById(
                $newsletterQueue->_('newsletterListId')
            );
            if($newsletterList !== null) :
                $newsletterList->unsubscribeMember($newsletterQueue->_('email'))->save();
                $this->flash->_('NEWSLETTER_LIST_UNSUBSCRIBE_SUCCESS');
            endif;
        endif;

        $this->redirect($this->url->getBaseUri());
    }

    public function openedAction(string $newsletterQueueId): void
    {
        NewsletterQueueHelper::recordOpened($newsletterQueueId);
        $this->response->setHeader('Content-Type', 'image/png');
        echo file_get_contents($this->configuration->getRootDir().'src/communication/resources/assets/images/1_1_transparent.png');

        $this->disableView();
    }
}
