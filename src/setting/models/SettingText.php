<?php

namespace VitesseCms\Setting\Models;

use VitesseCms\Setting\AbstractSetting;
use VitesseCms\Setting\Forms\SettingForm;

/**
 * Class SettingText
 */
class SettingText extends AbstractSetting
{

    /**
     * {@inheritdoc}
     */
    public function buildAdminForm(SettingForm $form, Setting $item) {
        $this->setBaseOptions();

        $form->_(
            'text',
            '%ADMIN_VALUE%',
            'value',
            $this->getOptions()
        );
    }
}
