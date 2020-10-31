<?php

namespace VitesseCms\Communication\Forms;

use VitesseCms\Communication\Models\NewsletterList;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;

/**
 * Class NewsletterListForm
 */
class NewsletterListImportForm extends AbstractForm
{

    /**
     * initialize
     *
     * @param NewsletterList|null $item
     */
    public function initialize(NewsletterList $item = null)
    {
        $this->_(
            'select',
            'NewsletterList',
            'newsletterlist',
            [
                'required'  => 'required',
                'options' => ElementHelper::arrayToSelectOptions(NewsletterList::findAll()),
                'value' => (string)$item->getId()
            ]
        );

        $this->_(
            'file',
            'Importfile',
            'file',
            ['required'  => 'required']
        );

        $this->_(
            'submit',
            'Import file'
        );
    }
}
