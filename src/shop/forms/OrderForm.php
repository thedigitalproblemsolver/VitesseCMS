<?php

namespace VitesseCms\Shop\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Shop\Models\Order;

/**
 * Class OrderForm
 */
class OrderForm extends AbstractForm
{

    /**
     * @param Order|null $item
     */
    public function initialize( Order $item = null)
    {
        $this->_(
            'text',
            'Orderid',
            'orderId'
        );

        $this->_(
            'text',
            'subTotal',
            'subTotal'
        );

        $this->_(
            'text',
            'tax',
            'tax'
        );

        $this->_(
            'text',
            'total',
            'total'
        );

        $this->_(
            'text',
            'shippingAmount',
            'shippingAmount'
        );

        $this->_(
            'text',
            'shippingTax',
            'shippingTax'
        );

        $this->_(
            'text',
            'orderState',
            'orderState'
        );
    }
}
