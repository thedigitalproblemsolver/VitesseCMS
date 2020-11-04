<?php

namespace VitesseCms\Communication\Interfaces;

use VitesseCms\Communication\Models\NewsletterQueue;

/**
 * Interface CommunicationTagInterface
 */
interface CommunicationTagInterface
{
    /**
     * @param string $content
     * @param array $tagOptions
     * @param NewsletterQueue|null $newsletterQueue
     *
     * @return string
     */
    public static function parse(
        string $content,
        array $tagOptions,
        ?NewsletterQueue $newsletterQueue = null
    ): string;
}
