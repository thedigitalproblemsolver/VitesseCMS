<?php

namespace VitesseCms\Communication\Controllers;

use VitesseCms\Communication\Forms\NewsletterQueueForm;
use VitesseCms\Communication\Helpers\NewsletterQueueHelper;
use VitesseCms\Communication\Models\NewsletterQueue;
use VitesseCms\Admin\AbstractAdminController;

/**
 * Class AdminnewsletterqueueController
 */
class AdminnewsletterqueueController extends AbstractAdminController
{
    /**
     * @var array
     */
    protected $parseAsJob = ['sendQueuedNewsletter'];

    /**
     * onConstruct
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = NewsletterQueue::class;
        $this->classForm = NewsletterQueueForm::class;

        $this->listTemplate = 'listNewsletterQueue';
        $this->listTemplatePath = $this->config->get('rootDir') . 'src/communication/resources/views/admin/';
    }

    /**
     * @param string $id
     */
    public function viewBodyAction(string $id): void
    {
        if($id) :
            NewsletterQueue::setFindPublished(false);
            $this->view->setVar('content',NewsletterQueue::findById($id)->_('body'));
            $_REQUEST['embedded'] = 1;
        endif;

        parent::prepareView();
    }

    /**
     * @param string $id
     *
     * @throws \Phalcon\Exception
     */
    public function sendQueuedNewsletterAction(string $id): void
    {
        NewsletterQueue::setFindPublished(false);
        /** @var NewsletterQueue $newsletterQueue */
        $newsletterQueue = NewsletterQueue::findById($id);
        if($newsletterQueue) :
            $now = new \DateTime();
            $newsletterQueue->set('dateSending', $now->format('Y-m-d H:i:s'))->save();

            /** @var NewsletterQueue $newsletterQueue */
            if( NewsletterQueueHelper::send($newsletterQueue, $this->setting, $this->view)) :
                $newsletterQueue->set('dateSent', $now->format('Y-m-d H:i:s'))
                    ->set('published', true)
                    ->save()
                ;

                $this->flash->_('NEWSLETTER_SEND');
            endif;
        endif;
    }
}
