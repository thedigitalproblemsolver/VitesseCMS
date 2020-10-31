<?php declare(strict_types=1);

namespace VitesseCms\Shop\Factories;

use VitesseCms\Shop\Models\Product;

class ProductFactory
{
    public static function createFromOrderItem(array $orderItem): Product {
        return (new Product());
    }
}
