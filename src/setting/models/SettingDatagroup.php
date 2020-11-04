<?php

namespace VitesseCms\Setting\Models;

use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Setting\AbstractSetting;
use VitesseCms\Setting\Forms\SettingForm;

/**
 * Class SettingDatagroup
 */
class SettingDatagroup extends AbstractSetting
{

    /**
     * @inheritdoc
     */
    public function buildAdminForm(SettingForm $form, Setting $item)
    {
        $this->setBaseOptions();
        $this->setOption('options', Datagroup::class);
        $this->setOption('multilang', false);

        $form->_(
            'select',
            '%ADMIN_VALUE%',
            'value',
            $this->getOptions()
        );
    }
}
