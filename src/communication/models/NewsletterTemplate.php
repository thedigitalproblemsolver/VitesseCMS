<?php declare(strict_types=1);

namespace VitesseCms\Communication\Models;

use VitesseCms\Database\AbstractCollection;

class NewsletterTemplate extends AbstractCollection
{
    /**
     * @var ?string
     */
    public $template;

    public function getTemplate(): ?string
    {
        return $this->template;
    }
}
