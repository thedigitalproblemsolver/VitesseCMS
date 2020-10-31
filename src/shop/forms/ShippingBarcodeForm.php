<?php

namespace VitesseCms\Shop\Forms;

use VitesseCms\Form\AbstractForm;

/**
 * Class ShippingBarcodeForm
 */
class ShippingBarcodeForm extends AbstractForm
{

    /**
     * initialize
     */
    public function initialize(): void
    {
        $this->_(
            'text',
            'Barcode',
            'barcode',
            [
                'required' => true
            ]
        )->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}
