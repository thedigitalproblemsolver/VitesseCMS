<?php declare(strict_types=1);

namespace VitesseCms\Admin\Listeners;

use VitesseCms\Admin\Models\AdminMenu;
use VitesseCms\Admin\Models\AdminMenuNavBarChildren;
use VitesseCms\Core\Models\Datagroup;
use Phalcon\Events\Event;

class AdminMenuListener
{
    public function AddChildren(Event $event, AdminMenu $adminMenu): void
    {
        if ($adminMenu->getUser()->getPermissionRole() === 'superadmin') :
            $children = new AdminMenuNavBarChildren();
            $children->addChild('Data groups','admin/core/admindatagroup/adminList')
                ->addChild('Data fields','admin/field/admindatafield/adminList')
                ->addChild('Blocks','admin/block/adminblock/adminList')
                ->addChild('BlockPositions','admin/block/adminblockposition/adminList')
            ;
            $formOptionsGroups = $adminMenu->getGroups()->getByKey('formOptions');
            if ($formOptionsGroups !== null) :
                $children->addLine();
                /** @var Datagroup $formOptionGroup */
                foreach ($formOptionsGroups->getDatagroups() as $formOptionGroup) :
                    $children->addChild($formOptionGroup->_('name'),'admin/content/adminitem/adminList/?filter[datagroup]='.$formOptionGroup->getId());
                endforeach;
            endif;

            $adminMenu->addDropbown('DataDesign',$children);
        endif;
    }
}
