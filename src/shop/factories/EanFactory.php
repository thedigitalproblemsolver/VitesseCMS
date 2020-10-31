<?php declare(strict_types=1);

namespace VitesseCms\Shop\Factories;

use VitesseCms\Shop\Models\Ean;

class EanFactory
{
    public static function create(
        string $ean,
        string $parentItem,
        string $sku,
        bool $published = false
    ): Ean {
        return (new Ean())
            ->set('name', $ean)
            ->set('parentItem', $parentItem)
            ->set('sku', $sku)
            ->set('published', $published);
    }
}
