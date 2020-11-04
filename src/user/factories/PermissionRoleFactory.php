<?php

namespace VitesseCms\User\Factories;

use VitesseCms\User\Models\PermissionRole;

/**
 * Class PermissionRoleFactory
 */
class PermissionRoleFactory
{
    /**
     * @param string $name
     * @param string $calling_name
     * @param bool $published
     * @param bool $adminAccess
     * @param string|null $parentId
     *
     * @return PermissionRole
     */
    public static function create(
        string $name,
        string $calling_name,
        bool $published = false,
        bool $adminAccess = false,
        string $parentId = null
    ) : PermissionRole
    {
        $permission = new PermissionRole();
        $permission->set('name', $name,true);
        $permission->set('calling_name', $calling_name);
        $permission->set('published', $published);
        $permission->set('adminAccess', $adminAccess);
        $permission->set('parentId', $parentId);

        return $permission;
    }
}
