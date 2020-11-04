<?php declare(strict_types=1);

namespace VitesseCms\Communication\Repositories;

use VitesseCms\Communication\Models\NewsletterTemplate;

class NewsletterTemplateRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?NewsletterTemplate
    {
        NewsletterTemplate::setFindPublished($hideUnpublished);

        /** @var NewsletterTemplate $newsletterTemplate */
        $newsletterTemplate = NewsletterTemplate::findById($id);
        if (is_object($newsletterTemplate)):
            return $newsletterTemplate;
        endif;

        return null;
    }
}
