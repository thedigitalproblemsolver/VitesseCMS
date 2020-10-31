<?php declare(strict_types=1);

namespace VitesseCms\Communication\Controllers;

use VitesseCms\Communication\Interfaces\RepositoriesInterface;
use VitesseCms\Core\AbstractController;
use VitesseCms\Form\Controllers\IndexController;
use VitesseCms\Form\Utils\FormUtil;

class NewsletterlistController extends AbstractController implements RepositoriesInterface
{
    public function addmemberAction(): void
    {
        if(
            is_string($this->request->get('newsletterList'))
            && $this->request->get('email')
            && ( $this->request->isAjax() || $this->request->isPost() )
        ) :
            $hasErrors = false;
            if($this->request->isPost() && !empty($this->request->getPost('block'))) :
                $post = $this->request->getPost();
                $blockFormBuilder = $this->repositories->blockFormBuilder->getById(
                    $this->request->getPost('block'),
                    $this->view
                );
                if(
                    $blockFormBuilder !== null
                    && $blockFormBuilder->isUseRecaptcha()
                    && !FormUtil::hasValidRecaptcha($post)
                ) :
                    $hasErrors = true;
                endif;
            endif;

            if(!$hasErrors) {
                $newsletterLists = explode(',', $this->request->get('newsletterList'));
                foreach ($newsletterLists as $newsletterListId) :
                    $newsletterList = $this->repositories->newsletterList->getById($newsletterListId);
                    if($newsletterList !== null ):
                        $newsletterList->addMember($this->request->get('email'))->save();
                    endif;
                endforeach;
            }
        endif;

        $formController = new IndexController();
        $formController->submitAction();
    }

    public function unsubscribeAction(string $newsletterListId): void
    {
        if($this->user->isLoggedIn()) :
            $newsletterList = $this->repositories->newsletterList->getById($newsletterListId);
            if($newsletterList !== null ):
                $newsletterList->unsubscribeMember($this->user->_('email'))->save();
                $this->flash->_('NEWSLETTER_UNSUBSCRIBE_SUCCESS','success',[$newsletterList->_('name')]);
            endif;
        endif;

        $this->redirect();
    }

    public function subscribeAction(string $newsletterListId): void
    {
        if($this->user->isLoggedIn()) :
            $newsletterList = $this->repositories->newsletterList->getById($newsletterListId);
            if($newsletterList !== null) :
                $newsletterList->subscribeMember($this->user->_('email'))->save();
                $this->flash->_('NEWSLETTER_SUBSCRIBE_SUCCESS','success',[$newsletterList->_('name')]);
            endif;
        endif;

        $this->redirect();
    }
}
