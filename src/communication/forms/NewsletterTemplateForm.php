<?php

namespace VitesseCms\Communication\Forms;

use VitesseCms\Communication\Models\EmailTemplate;
use VitesseCms\Communication\Models\NewsletterTemplate;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Language\Models\Language;

/**
 * Class NewsletterTemplateForm
 */
class NewsletterTemplateForm extends AbstractForm
{

    /**
     * initialize
     *
     * @param NewsletterTemplate|null $item
     */
    public function initialize(NewsletterTemplate $item = null)
    {
        $this->_(
            'text',
            '%CORE_NAME%',
            'name',
            ['required'  => 'required']
        );

        $this->_(
            'select',
            '%ADMIN_LANGUAGE%',
            'language',
            [
                'required'  => 'required',
                'options' => ElementHelper::arrayToSelectOptions(Language::findAll())
            ]
        );

        $this->_(
            'textarea',
            'template',
            'template',
            [
                'required'  => 'required',
                'inputClass' => 'editor',
            ]
        );

        $this->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}
