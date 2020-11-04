<?php

namespace VitesseCms\Joomla\Models;

use VitesseCms\Core\AbstractModel;

/**
 * Class K2ExtraFields
 */
class K2ExtraFields extends AbstractModel
{

    static $groups = [];

    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'jos_k2_extra_fields';
    }

    /**
     * @param int $groupId
     *
     * @return array
     */
    public static function getFieldsOfGroup(int $groupId): array
    {
        if (isset(self::$groups[$groupId])) :
            return self::$groups[$groupId];
        else :
            $extrafields = self::find();
            $matchedExtrafields = [];
            foreach ($extrafields as $extrafield) :
                if ($extrafield->group == $groupId) :
                    $matchedExtrafields[$extrafield->name] = $extrafield;
                endif;
            endforeach;
            self::$groups[$groupId] = $matchedExtrafields;

            return self::$groups[$groupId];
        endif;
    }
}
