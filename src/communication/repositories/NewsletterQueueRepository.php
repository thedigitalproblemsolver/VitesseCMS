<?php declare(strict_types=1);

namespace VitesseCms\Communication\Repositories;

use VitesseCms\Communication\Models\NewsletterQueue;

class NewsletterQueueRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?NewsletterQueue
    {
        NewsletterQueue::setFindPublished($hideUnpublished);

        /** @var NewsletterQueue $newsletterQueue */
        $newsletterQueue = NewsletterQueue::findById($id);
        if (is_object($newsletterQueue)):
            return $newsletterQueue;
        endif;

        return null;
    }
}
