<?php declare(strict_types=1);

namespace VitesseCms\User\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Models\Attributes;
use Phalcon\Tag;

class LoginForm extends AbstractForm
{
    public function initialize(): void
    {
        $this->addEmail(
            '%CORE_EMAIL%',
            'email',
            (new Attributes())->setRequired(true)
        )->addPassword(
            '%USER_PASSWORD%',
            'password',
            (new Attributes())->setRequired(true)
        )->addSubmitButton('%USER_LOGIN%')
            ->addHtml(
                Tag::linkTo([
                        'action' => 'user/password/forgotForm',
                        'text'   => '%USER_FORGOT_PASSWORD%',
                        'class'  => 'openmodal',
                    ]
                )
            )
        ;
    }
}
