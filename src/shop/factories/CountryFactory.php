<?php

namespace VitesseCms\Shop\Factories;

use VitesseCms\Shop\Models\Country;

/**
 * Class CountryFactory
 */
class CountryFactory
{
    /**
     * @param string $name
     * @param string $short
     * @param string $shortThree
     * @param bool $published
     *
     * @return Country
     */
    public static function create(
        string $name,
        string $short,
        string $shortThree,
        bool $published = false
    ): Country {
        return (new Country())
            ->set('name', $name, true)
            ->set('short', $short)
            ->set('shortThree', $shortThree)
            ->set('published', $published);
    }
}
