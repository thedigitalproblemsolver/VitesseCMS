<?php

namespace VitesseCms\Field\Forms;

use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Core\Utils\SystemUtil;
use VitesseCms\Field\Interfaces\AdminformInterface;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;

/**
 * Class AdminFieldModel
 */
class AdminFieldModel implements AdminformInterface
{
    /**
     * (@inheritdoc)
     */
    public static function buildForm(AbstractForm $form, AbstractCollection $item): void
    {
        $form->_(
            'select',
            'Model',
            'model',
            [
                'options'    => ElementHelper::arrayToSelectOptions(
                    SystemUtil::getModels(true)
                ),
                'required'   => 'required',
                'inputClass' => 'select2',
            ]
        )->_(
            'number',
            'Display limit',
            'displayLimit'
        )->_(
            'checkbox',
            'use Select2',
            'useSelect2'
        )->_(
            'checkbox',
            'Select multiple',
            'multiple'
        );

        switch ($item->_('model')) :
            case Item::class:
                $form->_(
                    'select',
                    'Items from datagroup',
                    'datagroup',
                    [
                        'options' => Datagroup::class,
                        'multiple' => true,
                        'inputClass' => 'select2',
                    ]
                );
                break;

        endswitch;
    }
}
