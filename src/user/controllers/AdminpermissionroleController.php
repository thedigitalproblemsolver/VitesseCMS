<?php

namespace VitesseCms\User\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\User\Forms\PermissionRoleForm;
use VitesseCms\User\Models\PermissionRole;

/**
 * Class AdminpermissionroleController
 */
class AdminpermissionroleController extends AbstractAdminController
{
    /**
     * onConstruct
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = PermissionRole::class;
        $this->classForm = PermissionRoleForm::class;
        $this->listSortable = true;
        $this->listNestable = true;
        $this->listOrder = 'ordering';
    }
}
