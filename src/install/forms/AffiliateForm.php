<?php declare(strict_types=1);

namespace VitesseCms\Install\Forms;

use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Form\AbstractForm;

class AffiliateForm extends AbstractForm
{

    public function initialize()
    {
        $this->_(
            'select',
            'Affiliate property datagroup',
            'SHOP_DATAGROUP_AFFILIATE',
            [
                'required' => 'required',
                'value' => $this->setting->get('SHOP_DATAGROUP_AFFILIATE'),
                'options' => Datagroup::class
            ]
        );

        $this->_(
            'submit',
            'create',
            'create'
        );
    }
}
