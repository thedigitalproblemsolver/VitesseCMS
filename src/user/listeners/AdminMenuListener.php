<?php declare(strict_types=1);

namespace VitesseCms\User\Listeners;

use VitesseCms\Admin\Models\AdminMenu;
use VitesseCms\Admin\Models\AdminMenuNavBarChildren;
use VitesseCms\Core\Models\Datagroup;
use Phalcon\Events\Event;

class AdminMenuListener
{
    public function AddChildren(Event $event, AdminMenu $adminMenu): void
    {
        if ('superadmin' === $adminMenu->getUser()->getPermissionRole()) :
            $children = new AdminMenuNavBarChildren();
            $children->addChild('Permissions','admin/user/adminpermissions/adminList')
                ->addChild('Permissions Roles','admin/user/adminpermissionrole/adminList')
            ;
            $adminMenu->addDropbown('Permissions',$children);
        endif;

        $group = $adminMenu->getGroups()->getByKey('user');
        if ($group !== null) :
            $children = new AdminMenuNavBarChildren();
            $children->addChild('User details', 'admin/user/adminuser/adminList');

            /** @var Datagroup $contentGroup */
            foreach ($group->getDatagroups() as $contentGroup) :
                $children->addChild($contentGroup->_('name'), 'admin/content/adminitem/adminList/?filter[datagroup]='.$contentGroup->getId());
            endforeach;

            $adminMenu->addDropbown('Users', $children);
        endif;
    }
}
