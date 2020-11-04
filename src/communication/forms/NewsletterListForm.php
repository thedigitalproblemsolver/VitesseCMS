<?php

namespace VitesseCms\Communication\Forms;

use VitesseCms\Communication\Models\NewsletterList;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Language\Models\Language;
use VitesseCms\User\Models\User;

/**
 * Class NewsletterListForm
 */
class NewsletterListForm extends AbstractForm
{

    /**
     * initialize
     *
     * @param NewsletterList|null $item
     */
    public function initialize(NewsletterList $item = null)
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
            'email',
            '%CORE_EMAIL%',
            'addEmail'
        );

        $this->_(
            'html',
            'dataHtml',
            null,
            [
                'html' => $item->_('dataHtml')
            ]
        );

        $this->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}
