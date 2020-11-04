<?php

namespace VitesseCms\Setting;

use VitesseCms\Setting\Models\Setting;
use VitesseCms\Setting\Forms\SettingForm;

/**
 * Class AbstractSetting
 */
abstract class AbstractSetting
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setOption(string $key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * @return array
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * set base options
     */
    public function setBaseOptions()
    {
        $this->setOption('multilang',true);
    }

    /**
     * @param SettingForm $form
     * @param Setting $item
     */
    public function buildAdminForm(SettingForm $form, Setting $item) {}
}
