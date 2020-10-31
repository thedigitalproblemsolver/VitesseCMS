<?php

namespace VitesseCms\Sef\Forms;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Language\Models\Language;
use VitesseCms\Form\AbstractForm;

/**
 * Class RedirectForm
 */
class RedirectForm extends AbstractForm
{

    /**
     * initialize
     *
     * @param AbstractCollection|null $item
     */
    public function initialize(AbstractCollection $item = null)
    {
        $this->_(
            'text',
            '%ADMIN_FROM%',
            'from',
            [
                'required' => true,
            ]
        );
        $this->_(
            'text',
            '%ADMIN_TO%',
            'to',
            [
                'required' => true,
            ]
        );

        $languages = [
            [
                'value'    => '',
                'label'    => '%FORM_CHOOSE_AN_OPTION%',
                'selected' => false,
            ],
        ];
        Language::setFindPublished(false);
        foreach (Language::findAll() as $language) :
            $selected = false;
            if ($item->_('languageShort') === $language->_('short')) :
                $selected = true;
            endif;

            $languages[] = [
                'value'    => $language->_('short'),
                'label'    => $language->_('name'),
                'selected' => $selected,
            ];
        endforeach;
        $this->_(
            'select',
            '%ADMIN_LANGUAGE%',
            'language',
            [ 'options'  => $languages]
        );
        $this->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}
