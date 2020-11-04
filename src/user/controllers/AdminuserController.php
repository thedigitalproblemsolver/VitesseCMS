<?php

namespace VitesseCms\User\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\User\Forms\UserForm;
use VitesseCms\User\Models\User;

/**
 * Class AdminuserController
 */
class AdminuserController extends AbstractAdminController
{

    /**
     * onConstruct
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = User::class;
        $this->classForm = UserForm::class;
        $this->listOrder = 'email';
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(AbstractCollection $item): void
    {
        if (
            $this->request->getPost('new_password')
            && !empty($this->request->getPost('new_password'))
            && $this->user->getPermissionRole() === 'superadmin'
        ) :
            $item->set('password',$this->security->hash($this->request->getPost('new_password')));
        endif;
    }

    /**
     * deleteAction
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function deleteAction(): void
    {
        if ($this->user->getId() !== $this->dispatcher->getParam(0)) :
            parent::deleteAction();
        else :
            $this->flash->_('USER_NOT_DELETE_YOURSELF', 'error');
            $this->redirect($this->link . '/adminList');
        endif;
    }

    /**
     * searchAction
     */
    public function searchEmailAction(): void
    {
        if ($this->request->isAjax()) :
            User::setFindValue('email', $this->request->get('search'), 'like');
            $users = User::findAll();

            $result = [
                'items' => []
            ];
            if($users) :
                foreach ($users as $user ) :
                    $tmp = [
                        'id' => (string)$user->getId(),
                        'name' => $user->_('email'),
                    ];
                    $result['items'][] = $tmp;
                endforeach;
            endif;

            $this->response->setContentType('application/json', 'UTF-8');
            echo json_encode($result);
        endif;

        $this->view->disable();
    }
}
