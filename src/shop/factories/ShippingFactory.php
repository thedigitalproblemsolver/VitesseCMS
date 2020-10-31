<?php declare(strict_types=1);

namespace VitesseCms\Shop\Factories;

use VitesseCms\Shop\Models\Shipping;

class ShippingFactory
{
    public static function create(
        string $name,
        string $shippingType,
        bool $published = false
    ): Shipping {
        return (new Shipping())
            ->set('name', $name, true)
            ->set('type', $shippingType)
            ->set('published', $published);
    }
}
