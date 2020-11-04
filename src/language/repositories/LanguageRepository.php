<?php declare(strict_types=1);

namespace VitesseCms\Language\Repositories;

use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Language\Models\Language;
use VitesseCms\Language\Models\LanguageIterator;

class LanguageRepository
{
    public function findAll(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true
    ): LanguageIterator {
        Language::setFindPublished($hideUnpublished);

        if ($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                Language::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;

        return new LanguageIterator(Language::findAll());
    }

    public function getById(string $id, bool $hideUnpublished = true): ?Language
    {
        Language::setFindPublished($hideUnpublished);

        /** @var Language $language */
        $language = Language::findById($id);
        if(is_object($language)):
            return $language;
        endif;

        return null;
    }
}
