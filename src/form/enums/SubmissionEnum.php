<?php declare(strict_types=1);

namespace VitesseCms\Form\Enums;

use VitesseCms\Core\AbstractEnum;

class SubmissionEnum extends AbstractEnum
{
    public const EXCLUDED_FIELDS_USER = [
        'findValue',
        'adminListName',
        'adminListRowClass',
        'adminListButtons',
        'fieldNames',
        '_id',
        'published',
        'block',
        'name',
        'createdAt',
        'updatedOn',
        'sourceUri',
        'ipAddress',
        'user',
        'language',
    ];

    public const EXCLUDED_FIELDS_ADMIN = [
        'findValue',
        'adminListName',
        'adminListRowClass',
        'adminListButtons',
        'fieldNames',
        '_id',
        'published',
        'block',
        'name',
        'createdAt',
        'updatedOn',
    ];
}
