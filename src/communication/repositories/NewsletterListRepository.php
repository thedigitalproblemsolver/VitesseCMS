<?php declare(strict_types=1);

namespace VitesseCms\Communication\Repositories;

use VitesseCms\Communication\Models\NewsletterList;

class NewsletterListRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?NewsletterList
    {
        NewsletterList::setFindPublished($hideUnpublished);

        /** @var NewsletterList $newsletterList */
        $newsletterList = NewsletterList::findById($id);
        if (is_object($newsletterList)):
            return $newsletterList;
        endif;

        return null;
    }
}
