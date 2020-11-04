<?php

namespace VitesseCms\Shop\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Shop\Models\TaxRate;


/**
 * Class TaxRateForm
 */
class TaxRateForm extends AbstractForm
{

    /**
     * @param TaxRate|null $item
     */
    public function initialize( TaxRate $item = null)
    {
        $this->_(
            'number',
            '%ADMIN_TAX_RATE%',
            'taxrate',
            [
                'required' => 'required',
                'min' => 0,
                'max' => 100
            ]
        );

        $this->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}
