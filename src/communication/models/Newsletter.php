<?php declare(strict_types=1);

namespace VitesseCms\Communication\Models;

use VitesseCms\Database\AbstractCollection;

class Newsletter extends AbstractCollection
{
    /**
     * @var ?string
     */
    public $language;

    /**
     * @var ?string
     */
    public $template;

    /**
     * @var ?string
     */
    public $body;

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): Newsletter
    {
        $this->body = $body;

        return $this;
    }
}
