<?php declare(strict_types=1);

namespace VitesseCms\Setting\Models;

use VitesseCms\Block\Models\Block;
use VitesseCms\Setting\AbstractSetting;
use VitesseCms\Setting\Forms\SettingForm;

/**
 * Class SettingBlock
 */
class SettingBlock extends AbstractSetting
{

    /**
     * {@inheritdoc}
     */
    public function buildAdminForm(SettingForm $form, Setting $item)
    {
        $this->setBaseOptions();
        $this->setOption('options', Block::class);
        $this->setOption('multilang', false);

        $form->_(
            'select',
            '%ADMIN_VALUE%',
            'value',
            $this->getOptions()
        );
    }
}
