<?php

namespace VitesseCms\Setting\Models;

use VitesseCms\Setting\AbstractSetting;
use VitesseCms\Setting\Forms\SettingForm;

/**
 * Class SettingTextEditor
 */
class SettingTextEditor extends AbstractSetting
{
    /**
     * {@inheritdoc}
     */
    public function buildAdminForm(SettingForm $form, Setting $item) {
        $this->setBaseOptions();
        $this->setOption('inputClass', 'editor');

        $form->_(
            'textarea',
            '%CORE_VALUE%',
            'value',
            $this->getOptions()
        );
    }
}
