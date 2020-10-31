<?php

namespace VitesseCms\Install\Forms;

use VitesseCms\Form\AbstractForm;

/**
 * Class newProperty
 */
class newPropertyForm extends AbstractForm
{

    public function initialize()
    {
        $this->_(
            'email',
            '%CORE_EMAIL%',
            'email',
            ['required' => 'required']
        );
        $this->_(
            'password',
            '%USER_PASSWORD%',
            'password',
            ['required' => 'required']
        );

        $this->_(
            'submit',
            'create new property',
            'create'
        );
    }
}
