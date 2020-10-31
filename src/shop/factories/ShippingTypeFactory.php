<?php declare(strict_types=1);

namespace VitesseCms\Shop\Factories;

use VitesseCms\Shop\AbstractShippingType;
use VitesseCms\Shop\Helpers\ShippingHelper;

class ShippingTypeFactory
{
    public static function createFromArray(array $data): AbstractShippingType {
        $class = ShippingHelper::getClass($data['type']);
        /** @var AbstractShippingType $shippingType */
        $shippingType = new $class();
        foreach ($data as $key => $value) :
            $shippingType->set($key, $value);
        endforeach;

        return $shippingType;
    }
}
