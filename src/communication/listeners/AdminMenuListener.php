<?php declare(strict_types=1);

namespace VitesseCms\Communication\Listeners;

use VitesseCms\Admin\Models\AdminMenu;
use VitesseCms\Admin\Models\AdminMenuNavBarChildren;
use Phalcon\Events\Event;

class AdminMenuListener
{
    public function AddChildren(Event $event, AdminMenu $adminMenu): void
    {
        if ($adminMenu->getUser()->getPermissionRole() === 'superadmin') :
            $children = new AdminMenuNavBarChildren();
            $children->addChild('Form submissions', 'admin/form/adminsubmission/adminList')
                ->addChild('System E-mails', 'admin/communication/adminemail/adminList')
                ->addChild('Newsletter lists', 'admin/communication/adminnewsletterlist/adminList')
                ->addChild('Newsletter template', 'admin/communication/adminnewslettertemplate/adminList')
                ->addChild('Newsletter', 'admin/communication/adminnewsletter/adminList')
                ->addChild('Newsletter Queue', 'admin/communication/adminnewsletterqueue/adminList')
            ;

            $adminMenu->addDropbown('Communication', $children);
        endif;
    }
}
