<?php

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Core\AbstractController;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Form\Forms\BaseForm;
use VitesseCms\Shop\Models\Shopper;
use VitesseCms\User\Models\User;

/**
 * Class CartController
 */
class CheckoutController extends AbstractController
{
    /**
     * indexAction
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function indexAction()
    {
        $this->redirect();
    }

    /**
     * register
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function registerAction(): void
    {
        $form = new BaseForm();
        /** @var Datagroup $datagroup */
        $datagroup = Datagroup::findById($this->setting->get('SHOP_DATAGROUP_REGISTRATIONFORM'));
        $datagroup->buildItemForm($form);
        if ($form->validate($this)) :
            $post = $this->request->getPost();
            $post['email'] = strtolower($post['email']);
            User::setFindValue('email', $post['email'] );
            User::setFindPublished(false);
            $user = User::findFirst();
            if( \is_object($user) ) :
                $this->flash->_('USER_EXISTS','error');
            else :
                $shopper = Shopper::createNew($post);
                $this->session->set('auth', ['id' => $shopper->_('userId')]);
            endif;
        endif;

        $this->redirect();
    }

    /**
     * setShiptoAddressAction
     */
    public function setShiptoAddressAction() :void
    {
        $this->session->set('shiptoAddress',$this->request->get('id'));
        $this->view->disable();
    }
}
