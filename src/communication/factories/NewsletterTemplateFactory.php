<?php

namespace VitesseCms\Communication\Factories;

use VitesseCms\Communication\Models\NewsletterTemplate;
use VitesseCms\Language\Models\Language;

/**
 * Class NewsletterListFactory
 */
class NewsletterTemplateFactory
{
    /**
     * @param string $name
     * @param Language $language
     * @param string $template
     * @param bool $published
     *
     * @return NewsletterTemplate
     */
    public static function create(
        string $name,
        Language $language,
        string $template,
        bool $published = false
    ): NewsletterTemplate {
        return (new NewsletterTemplate())
            ->set('name', $name)
            ->set('language',(string)$language->getId())
            ->set('template', $template)
            ->set('published', $published)
        ;
    }
}
