<?php

namespace VitesseCms\Setting\Factory;

use VitesseCms\Setting\Models\Setting;

/**
 * Class SettingFactory
 */
class SettingFactory
{

    /**
     * @param string $calling_name
     * @param string $type
     * @param string $value
     * @param string $name
     * @param bool $published
     *
     * @return Setting
     */
    public static function create(
        string $calling_name,
        string $type,
        string $value = '',
        string $name = '',
        bool $published = false
    ): Setting {
        if(empty($name)) :
            $name = ucwords(str_replace('_',' - ',$calling_name));
        endif;

        $setting = new Setting();
        $setting->set('name', $name, true);
        $setting->set('calling_name', $calling_name);
        $setting->set('published', $published);
        $setting->set('type', $type);
        $setting->set('value', $value, true);

        return $setting;
    }
}
