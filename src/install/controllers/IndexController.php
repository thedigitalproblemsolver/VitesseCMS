<?php declare(strict_types=1);

namespace VitesseCms\Install\Controllers;

use VitesseCms\Core\Factories\ObjectFactory;
use VitesseCms\Install\AbstractCreatorController;
use VitesseCms\Install\Forms\newPropertyForm;
use VitesseCms\Language\Factories\LanguageFactory;
use VitesseCms\User\Factories\UserFactory;

class IndexController extends AbstractCreatorController
{
    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function indexAction(): void
    {
        $_REQUEST['embedded'] = 1;

        $form = new newPropertyForm();
        $this->view->setVar('content',$form->renderForm(
            $this->url->getBaseUri().'install/index/createproperty/'
        ));
        $this->prepareView();
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function createPropertyAction(): void
    {
        $form = new newPropertyForm();
        $form->bind($this->request->getPost(), new ObjectFactory());
        if ($form->validate($this)) :
            $post = $this->request->getPost();
            $language = LanguageFactory::create(
                'Default language',
                'Default language',
                $this->configuration->getLanguageLocale(),
                $this->configuration->getLanguageShort(),
                $this->url->getBaseUri(),
                'flag-icon flag-icon-'.$this->configuration->getLanguageShort(),
                true
            );
            $language->save();

            $this->createBasicPermissionRoles();

            $user = UserFactory::create(
                $post['email'],
                $post['password'],
                'superadmin',
                true
            );
            $user->save();

            $this->response->redirect($this->url->getBaseUri().'user/loginform');
        else:
            $this->response->redirect($this->url->getBaseUri());
        endif;

        $this->disableView();
    }
}
