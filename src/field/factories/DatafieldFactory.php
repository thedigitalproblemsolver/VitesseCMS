<?php

namespace VitesseCms\Field\Factories;

use VitesseCms\Core\Models\Datafield;

/**
 * Class DatafieldFactory
 */
class DatafieldFactory
{
    /**
     * @param string $title
     * @param string $calling_name
     * @param string $type
     * @param array $datafieldSettings
     * @param bool $published
     * @param int $ordering
     *
     * @return Datafield
     */
    public static function create(
        string $title,
        string $calling_name,
        string $type,
        array $datafieldSettings = [],
        bool $published = false,
        int $ordering = 0
    ) : Datafield
    {
        $datafield = new Datafield();
        $datafield->set('name', $title, true);
        $datafield->set('calling_name', $calling_name);
        $datafield->set('type', $type);
        $datafield->set('published', $published);
        $datafield->set('ordering', $ordering);
        if (isset($datafieldSettings)) :
            foreach ($datafieldSettings as $settingKey => $settingValue) :
                $datafield->set($settingKey, $settingValue);
            endforeach;
        endif;

        return $datafield;
    }
}
