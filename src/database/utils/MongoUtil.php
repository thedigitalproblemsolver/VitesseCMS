<?php declare(strict_types=1);

namespace VitesseCms\Database\Utils;

use MongoDB\BSON\ObjectID;

class MongoUtil
{
    public static function isObjectId($idToTest): bool
    {
        if (empty($idToTest) || !is_string($idToTest)) :
            return false;
        endif;

        try {
            new ObjectID($idToTest);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
