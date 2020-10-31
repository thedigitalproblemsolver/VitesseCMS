<?php

namespace VitesseCms\Communication\Factories;

use VitesseCms\Communication\Models\NewsletterQueue;
use DateTime;

/**
 * Class NewsletterQueue
 */
class NewsletterQueueFactory
{

    /**
     * @param string $email
     * @param string $userId
     * @param string $newsletterId
     * @param string $newsletterListId
     * @param string $subject
     * @param string $body
     * @param DateTime|null $dateSent
     * @param DateTime|null $dateOpened
     *
     * @return NewsletterQueue
     */
    public static function create(
        string $email,
        string $userId,
        string $newsletterId,
        string $newsletterListId,
        string $subject,
        string $body,
        DateTime $dateSent = null,
        DateTime $dateOpened = null
    ): NewsletterQueue {
        $NewsletterQueue = (new NewsletterQueue())
            ->set('email', $email)
            ->set('userId', $userId)
            ->set('newsletterId', $newsletterId)
            ->set('newsletterListId', $newsletterListId)
            ->set('subject', $subject)
            ->set('body', $body)
        ;

        if($dateSent) :
            $NewsletterQueue->set('dateSent', $dateSent);
        endif;
        if($dateOpened) :
            $NewsletterQueue->set('dateOpened', $dateOpened);
        endif;

        return $NewsletterQueue;
    }
}
