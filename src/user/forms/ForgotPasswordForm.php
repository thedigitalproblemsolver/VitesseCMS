<?php

namespace VitesseCms\User\Forms;

use VitesseCms\Form\AbstractForm;

/**
 * Class ForgotPasswordForm
 */
class ForgotPasswordForm extends AbstractForm
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
            'submit',
            '%USER_PASSWORD_FORGOT_REQUEST_SEND_EMAIL%'
        );
    }
}
