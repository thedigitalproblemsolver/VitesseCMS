<?php declare(strict_types=1);

namespace VitesseCms\Communication\Listeners;

use VitesseCms\Communication\Controllers\AdminnewsletterlistController;
use VitesseCms\Communication\Models\NewsletterList;
use Phalcon\Events\Event;

class AdminnewsletterlistControllerListener
{
    public function beforeEdit(
        Event $event,
        AdminnewsletterlistController $controller,
        NewsletterList $newsletterList
    ): void {
        $rows = [];
        foreach ($newsletterList->getMembers() as $key => $member) :
            $member['key'] = $key;
            $member['rowNumber'] = $key+1;
            $member['rowState'] = 'list-group-item-success';
            if(!empty($member['unSubscribeDate'])) :
                $member['rowState'] = 'list-group-item-danger';
            endif;
            $rows[] = $member;
        endforeach;

        $link = $controller->url->getBaseUri() .
            'admin/' .
            $controller->router->getModuleName() .
            '/' . $controller->router->getControllerName()
        ;

        $dataHtml = $controller->view->renderTemplate(
            'adminNewsletterListMembers',
            $controller->configuration->getRootDir().'src/communication/resources/views/admin/',
            [
                'baseLink' => $link,
                'baseId' => (string)$newsletterList->getId(),
                'rows' => $rows
            ]
        );

        $newsletterList->set('dataHtml', $dataHtml);
    }
}
