<?php declare(strict_types=1);

namespace VitesseCms\Install\Forms;

use VitesseCms\Form\AbstractForm;

class ContactForm extends AbstractForm
{

    public function initialize()
    {
        $this->_(
            'text',
            'Website name',
            'WEBSITE_DEFAULT_NAME',
            [
                'required' => 'required',
                'value'    => $this->setting->get('WEBSITE_DEFAULT_NAME'),
                'readonly' => $this->setting->has('WEBSITE_DEFAULT_NAME'),
            ]
        );

        $this->_(
            'email',
            'Website email',
            'WEBSITE_CONTACT_EMAIL',
            [
                'required' => 'required',
                'value'    => $this->setting->get('WEBSITE_CONTACT_EMAIL'),
                'readonly' => $this->setting->has('WEBSITE_CONTACT_EMAIL'),
            ]
        );


        $this->_(
            'submit',
            'create',
            'create'
        );
    }
}
