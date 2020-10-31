<?php declare(strict_types=1);

namespace VitesseCms\Shop\ShippingTypes;

use VitesseCms\Shop\AbstractShippingType;
use VitesseCms\Shop\Models\Order;

class NoShipping extends AbstractShippingType
{
    public function calculateOrderAmount(Order $order): float
    {
        return 0;
    }

    public function calculateOrderVat(Order $order): float
    {
        return 0;
    }

    public function calculateCartAmount(array $items): float
    {
        return 0;
    }

    public function calculateCartVat(array $items): float
    {
        return 0;
    }

    public function calculateCartTotal(array $items): float
    {
        // TODO: Implement calculateCartTotal() method.
    }
}
