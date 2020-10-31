<?php declare(strict_types=1);

namespace VitesseCms\Communication\Listeners;

use VitesseCms\Communication\Controllers\AdminemailController;
use VitesseCms\Communication\Models\Email;
use VitesseCms\User\Utils\PermissionUtils;
use Phalcon\Events\Event;
use Phalcon\Tag;

class AdminemailControllerListener
{
    public function adminListItem(Event $event, AdminemailController $controller, Email $email): void
    {
        $email->setAdminListExtra($controller->view->renderTemplate(
            'emailAdminListItem',
            $controller->configuration->getRootDir().'src/communication/resources/views/admin/',
            ['email' => $email]
        ));

        if (PermissionUtils::check(
            $controller->user,
            $controller->view->getVar('aclModulePrefix').'communication',
            'adminemail',
            'sendPreview'
        )) :
            $email->setExtraAdminListButtons(Tag::linkTo([
                'action' => '/admin/communication/adminemail/sendPreview/'.$email->getId(),
                'class'  => 'fa fa-envelope',
            ]));
        endif;
    }
}
