<?php declare(strict_types=1);

namespace VitesseCms\Communication\Repositories;

use VitesseCms\Communication\Models\Newsletter;
use VitesseCms\Communication\Models\NewsletterIterator;
use VitesseCms\Database\Models\FindValueIterator;

class NewsletterRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?Newsletter
    {
        Newsletter::setFindPublished($hideUnpublished);

        /** @var Newsletter $newsletter */
        $newsletter = Newsletter::findById($id);
        if (is_object($newsletter)):
            return $newsletter;
        endif;

        return null;
    }

    public function findAll(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true
    ): NewsletterIterator {
        Newsletter::setFindPublished($hideUnpublished);
        Newsletter::addFindOrder('name');
        $this->parsefindValues($findValues);

        return new NewsletterIterator(Newsletter::findAll());
    }

    protected function parsefindValues(?FindValueIterator $findValues = null): void
    {
        if ($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                Newsletter::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;
    }
}
