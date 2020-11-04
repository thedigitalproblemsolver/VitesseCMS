<?php declare(strict_types=1);

namespace VitesseCms\Language\Models;

use VitesseCms\Database\AbstractCollection;

class Language extends AbstractCollection
{
    /**
     * @var string
     */
    public $short;

    /**
     * @var string
     */
    public $locale;

    public function getShortCode(): ?string
    {
        return $this->short;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
