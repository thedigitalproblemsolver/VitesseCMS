<?php

namespace VitesseCms\Setting\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Setting\Enum\TypeEnum;
use VitesseCms\Setting\Models\Setting;
use VitesseCms\Setting\AbstractSetting;

/**
 * Class SettingForm
 */
class SettingForm extends AbstractForm
{

    /**
     * @param Setting|null $item
     */
    public function initialize( Setting $item = null)
    {
        if( $item === null) :
            $item = new Setting();
            $item ->set('type', null);
        endif;

        $this->_(
            'text',
            '%CORE_NAME%',
            'name',
            [
                'required' => 'required',
                'multilang' => true,
            ]
        );

        $readonly = null;
        if( $item->_('calling_name') ) :
            $readonly = 'readonly';
        endif;
        $this->_(
            'text',
            '%ADMIN_CALLING_NAME%',
            'calling_name',
            [
                'required' => 'required',
                'readonly' => $readonly
            ]
        );

        if( !$item->_('type') ) :
            $this->addDropdown(
                '%ADMIN_TYPE%',
                'type',
                (new Attributes())->setRequired(true)->setOptions(ElementHelper::arrayToSelectOptions(TypeEnum::ALL_TYPES))
            );
        else :
            $object = '\\Modules\\Setting\Models\\Setting'.str_replace('Setting','', ucfirst($item->_('type')));
            /** @var AbstractSetting $item */
            $object = new $object();
            $object->buildAdminForm($this, $item);
        endif;

        $this->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}
