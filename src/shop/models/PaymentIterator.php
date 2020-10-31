<?php declare(strict_types=1);

namespace VitesseCms\Shop\Models;

class PaymentIterator extends \ArrayIterator
{
    public function __construct(array $payments)
    {
        parent::__construct($payments);
    }

    public function current(): Payment
    {
        return parent::current();
    }
}
