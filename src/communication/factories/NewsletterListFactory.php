<?php

namespace VitesseCms\Communication\Factories;

use VitesseCms\Communication\Models\NewsletterList;
use VitesseCms\Language\Models\Language;

/**
 * Class NewsletterListFactory
 */
class NewsletterListFactory
{
    /**
     * @param string $name
     * @param Language $language
     * @param bool $published
     *
     * @return NewsletterList
     */
    public static function create(
        string $name,
        Language $language,
        bool $published = false
    ): NewsletterList {
        return (new NewsletterList())
            ->set('name', $name)
            ->set('language',(string)$language->getId())
            ->set('published', $published)
        ;
    }
}
