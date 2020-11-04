<?php

namespace VitesseCms\Joomla\Helpers;

use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Joomla\Models\JoomfishContent;

/**
 * Class JoomfishHelper
 */
class JoomfishHelper
{
    /**
     * @param BaseObjectInterface $joomlaObject
     * @param string $table
     * @param int $languageId
     * @param string $languageShort
     * @param string $JoomlaIdField
     *
     * @return BaseObjectInterface
     */
    public static function translateObject (
        BaseObjectInterface $joomlaObject,
        string $table,
        int $languageId,
        string $languageShort,
        string $JoomlaIdField = 'id'
    ) : BaseObjectInterface
    {
        $joomfishContents = JoomfishContent::find(
            " reference_id = ".$joomlaObject->_($JoomlaIdField)." 
            AND reference_table = '".$table."' 
            AND language_id = ".$languageId
        );
        foreach($joomfishContents as $joomfishContent ) :
            $joomlaObject->set(
                $joomfishContent->reference_field,
                $joomfishContent->value,
                true,
                $languageShort
            );
        endforeach;

        return $joomlaObject;
    }
}
