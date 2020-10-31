<?php

namespace VitesseCms\Communication\Factories;

use VitesseCms\Communication\Models\Newsletter;
use VitesseCms\Communication\Models\NewsletterList;
use VitesseCms\Communication\Models\NewsletterTemplate;
use VitesseCms\Language\Models\Language;

/**
 * Class NewsletterFactory
 */
class NewsletterFactory
{
    /**
     * @param string $name
     * @param Language $language
     * @param NewsletterList $newsletterList
     * @param NewsletterTemplate $newsletterTemplate
     * @param string $subject
     * @param string $body
     * @param bool $published
     *
     * @return Newsletter
     */
    public static function create(
        string $name,
        Language $language,
        NewsletterList $newsletterList,
        NewsletterTemplate $newsletterTemplate,
        string $subject,
        string $body = '',
        bool $published = false
    ): Newsletter {
        return (new Newsletter())
            ->set('name', $name)
            ->set('language', (string)$language->getId())
            ->set('list', (string)$newsletterList->getId())
            ->set('template', (string)$newsletterTemplate->getId())
            ->set('subject', $subject)
            ->set('body', $body)
            ->set('published', $published)
        ;
    }
}
