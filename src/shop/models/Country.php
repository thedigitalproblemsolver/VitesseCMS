<?php declare(strict_types=1);

namespace VitesseCms\Shop\Models;

use VitesseCms\Database\AbstractCollection;

class Country extends AbstractCollection
{
    /**
     * @var string
     */
    public $short;

    /**
     * @var string
     */
    public $shortThree;

    public function getShort(): ?string
    {
        return $this->short;
    }

    public function getShortThree(): ?string
    {
        return $this->shortThree;
    }
}
