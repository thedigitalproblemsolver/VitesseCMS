<?php

namespace VitesseCms\User\Forms;

use VitesseCms\Form\AbstractForm;

/**
 * Class ChangeForm
 */
class ChangeForm extends AbstractForm
{

    public function initialize()
    {
        $this->_(
            'password',
            '%USER_PASSWORD_OLD%',
            'password_old',
            ['required' => 'required']
        );
        $this->_(
            'password',
            '%USER_PASSWORD_NEW%',
            'password',
            ['required' => 'required']
        );
        $this->_(
            'password',
            '%USER_PASSWORD_REPEAT%',
            'password2',
            ['required' => 'required']
        );
        $this->_(
            'submit',
            '%USER_PASSWORD_CHANGE%'
        );
    }
}
