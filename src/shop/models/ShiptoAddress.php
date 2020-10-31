<?php declare(strict_types=1);

namespace VitesseCms\Shop\Models;

use VitesseCms\Database\AbstractCollection;

class ShiptoAddress extends AbstractCollection
{
    public $country;

    public function setCountryId(string $countryId): self
    {
        $this->country = $countryId;

        return $this;
    }

    public function getCountryId(): string
    {
        return $this->country;
    }
}
