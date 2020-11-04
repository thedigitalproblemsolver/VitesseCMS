<?php declare(strict_types=1);

namespace VitesseCms\Communication\Listeners;

use VitesseCms\Communication\Controllers\AdminnewsletterController;
use VitesseCms\Communication\Models\Newsletter;
use Phalcon\Events\Event;

class AdminnewsletterControllerListener
{
    public function beforeEdit(Event $event, AdminnewsletterController $controller, Newsletter $newsletter): void
    {
        if (
            !empty($newsletter->getLanguage())
            && !empty($newsletter->getTemplate())
            && empty($newsletter->getBody())
        ) :
            $newsletterTemplate = $controller->repositories->newsletterTemplate->getById($newsletter->getTemplate());
            if ($newsletterTemplate !== null):
                $newsletter->setBody($newsletterTemplate->getTemplate());
            endif;
        endif;
    }

    public function adminListItem(Event $event, AdminnewsletterController $controller, Newsletter $newsletter): void
    {
        $language = $controller->repositories->language->getById(
            $newsletter->getLanguage()
        );
        $newsletter->setAdminListExtra($controller->view->renderModuleTemplate(
            'communication',
            'newsletterAdminListItem',
            'admin/',
            [
                'newsletter'    => $newsletter,
                'languageShort' => $language ? $language->getShortCode() : '',
                'listSortable'  => $controller->isListSortable(),
            ]
        ));
    }
}
