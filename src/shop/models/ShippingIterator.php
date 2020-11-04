<?php declare(strict_types=1);

namespace VitesseCms\Shop\Models;

use VitesseCms\Shop\AbstractShippingType;

class ShippingIterator extends \ArrayIterator
{
    public function __construct(array $shippings)
    {
        parent::__construct($shippings);
    }

    public function current(): AbstractShippingType
    {
        return parent::current();
    }
}
