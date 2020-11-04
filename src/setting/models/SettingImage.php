<?php declare(strict_types=1);

namespace VitesseCms\Setting\Models;

use VitesseCms\Setting\AbstractSetting;
use VitesseCms\Setting\Forms\SettingForm;

class SettingImage extends AbstractSetting
{
    public function buildAdminForm(SettingForm $form, Setting $item) {
        $this->setBaseOptions();
        $this->setOption('template', 'filemanager');

        $form->_(
            'file',
            '%CORE_VALUE%',
            'value',
            $this->getOptions()
        );
    }
}
