<?php declare(strict_types=1);

namespace VitesseCms\Shop\ShippingTypes;

use VitesseCms\Shop\AbstractShippingType;
use VitesseCms\Shop\Forms\ShippingForm;
use VitesseCms\Shop\Models\Order;

class Vinologix extends AbstractShippingType
{
    public function buildAdminForm(ShippingForm $form)
    {
        $form->addHtml('Needs to be developed further.');
        /*$form->_(
            'text',
            'Account ID',
            'accountId',
            [
                'required' => true
            ]
        );
        $form->_(
            'text',
            'API-key',
            'apiKey',
            [
                'required' => true
            ]
        );*/
    }

    public function calculateOrderAmount(Order $order): float
    {
        // TODO: Implement calculateOrderAmounnt() method.
    }

    public function calculateOrderVat(Order $order): float
    {
        // TODO: Implement calculateOrderTax() method.
    }

    public function calculateCartAmount(array $items): float
    {
        // TODO: Implement calculateCartAmount() method.
    }

    public function calculateCartVat(array $items): float
    {
        // TODO: Implement calculateCartTax() method.
    }

    public function calculateCartTotal(array $items): float
    {
        // TODO: Implement calculateCartTotal() method.
    }
}
