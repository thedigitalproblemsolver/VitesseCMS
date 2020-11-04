<?php declare(strict_types=1);

namespace VitesseCms\Field\Enums;

use VitesseCms\Core\AbstractEnum;
use VitesseCms\Field\Models\FieldDatagroup;

class FieldEnum extends AbstractEnum
{
    public const TYPE_TEXT = 'FieldText';
    public const TYPE_DATAGROUP = FieldDatagroup::class;

    public const ALL_TYPES = [
        self::TYPE_TEXT => 'Text',
        self::TYPE_DATAGROUP => 'Datagroup'
    ];
}
