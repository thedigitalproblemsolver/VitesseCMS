<?php

namespace VitesseCms\Shop\Factories;

use VitesseCms\Shop\Models\TaxRate;

/**
 * Class TaxrateFactory
 */
class TaxrateFactory
{
    /**
     * @param string $name
     * @param float $taxrate
     * @param bool $published
     *
     * @return TaxRate
     */
    public static function create(
        string $name,
        float $taxrate,
        bool $published = false
    ): TaxRate {
        return (new TaxRate())
            ->set('name', $name, true)
            ->set('taxrate', $taxrate)
            ->set('published', $published);
    }
}
