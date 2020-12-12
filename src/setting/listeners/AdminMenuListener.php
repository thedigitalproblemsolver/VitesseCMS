<?php declare(strict_types=1);

namespace VitesseCms\Setting\Listeners;

use VitesseCms\Admin\Models\AdminMenu;
use VitesseCms\Admin\Models\AdminMenuNavBarChildren;
use Phalcon\Events\Event;

class AdminMenuListener
{
    public function AddChildren(Event $event, AdminMenu $adminMenu): void
    {
        if ($adminMenu->getUser()->getPermissionRole() === 'superadmin') :
            $children = new AdminMenuNavBarChildren();

            $children->addChild('Settings', 'admin/setting/adminsetting/adminList')
                ->addChild('Languages', 'admin/language/adminlanguage/adminList')
                ->addChild('Site Creator', 'admin/install/sitecreator/index')
                ->addChild('Search', 'admin/search/adminindex/index')
                ->addChild('Activity log', 'admin/core/adminlog/adminList')
                ->addChild('Job-queue', 'admin/core/adminjobqueue/adminList')
                ->addChild('Execute job', 'core/JobQueue/execute','_blank')
                ->addChild('Export', 'admin/export/adminindex/index')
                ->addChild('SEF-redirects', 'admin/sef/adminredirect/adminList')
            ;

            $adminMenu->addDropbown('System',$children);
        endif;
    }
}
