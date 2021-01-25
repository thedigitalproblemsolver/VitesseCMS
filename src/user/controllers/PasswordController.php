<?php declare(strict_types=1);

namespace VitesseCms\User\Controllers;

use VitesseCms\Core\AbstractController;
use VitesseCms\Core\Factories\ObjectFactory;
use VitesseCms\Core\Helpers\Sef;
use VitesseCms\User\Factories\PasswordFactory;
use VitesseCms\User\Forms\ChangeForm;
use VitesseCms\User\Forms\LoginForm;
use VitesseCms\User\Forms\ForgotPasswordForm;
use VitesseCms\User\Forms\ResetForm;
use VitesseCms\User\Interfaces\RepositoriesInterface;
use VitesseCms\User\Models\User;

class PasswordController extends AbstractController implements RepositoriesInterface
{
    public function indexAction(): void
    {
        $this->redirect('/');
    }

    public function changeFormAction(): void
    {
        if ($this->user->isLoggedIn()) :
            $this->view->setVar('content', (new ChangeForm())->renderForm('user/password/parseChangeForm'));
        else :
            $this->view->setVar('content', (new LoginForm())->renderForm('user/login'));
        endif;

        $this->prepareView();
    }

    public function forgotFormAction(): void
    {
        $this->view->setVar('content', (new ForgotPasswordForm())->renderForm('user/password/parseForgotForm'));
        $this->prepareView();
    }

    public function resetFormAction(): void
    {
        $hasErrors = true;
        if($this->dispatcher->getParam(0)) :
            User::setFindValue('passwordReset.passwordResetToken',$this->dispatcher->getParam(0));
            $user = User::findFirst();
            if($user) :
                $item = ObjectFactory::create();
                $item->set('passwordResetToken',$this->dispatcher->getParam(0));
                $this->view->setVar(
                    'content',
                    (new ResetForm($item))->renderForm('user/password/parseResetForm')
                );
                $hasErrors = false;
            endif;
        endif;

        if($hasErrors) :
            $this->flash->setError('CORE_SOMETHING_IS_WRONG');
        endif;

        $this->prepareView();
    }

    public function parseForgotFormAction(): void
    {
        $hasErrors = true;
        $return = null;

        $form = new ForgotPasswordForm();
        $form->bind($this->request->getPost(), new stdClass());
        if ($form->validate($this)) :
            $user = $this->repositories->user->getByEmail($this->request->get('email'));
            if ($user !== null) :
                $user->set('passwordReset', PasswordFactory::createReset());
                $user->save();
                $this->view->set('systemEmailToAddress', $user->_('email'));
                $this->view->set(
                    'resetLink',
                    $this->url->getBaseUri().
                    'user/password/resetForm/'.
                    $user->_('passwordReset')->_('passwordResetToken')
                );

                $hasErrors = false;
                $this->flash->setSucces('USER_PASSWORD_FORGOT_REQUEST_SAVED_SUCCESS');

                $item = $this->repositories->item->getById($this->setting->get('USER_PAGE_PASSWORDFORGOTEMAIL'));
                if($item !== null):
                    $return = $this->url->getBaseUri().$item->_('slug');
                endif;
            endif;
        endif;

        if($hasErrors) :
            $this->flash->setError('CORE_SOMETHING_IS_WRONG');
        endif;

        $this->redirect($return);
    }

    public function parseChangeFormAction(): void
    {
        $hasErrors = true;
        $redirect = '/';
        if ($this->user->isLoggedIn()) :
            $form = new ChangeForm();
            $form->bind($this->request->getPost(), new stdClass());
            if (
                $form->validate($this)
                && $this->request->get('password') === $this->request->get('password2')
            ) :
                $this->user->set('forcePasswordReset', false);
                $this->user->set('password', $this->security->hash($this->request->get('password')));
                $this->user->save();

                $hasErrors = false;
                $this->flash->setSucces('USER_PASSWORD_CHANGE_SUCCESS');
            endif;

            $redirect = null;
        endif;

        if($hasErrors) :
            $this->flash->setError('CORE_SOMETHING_IS_WRONG');
        endif;

        $this->redirect($redirect);
    }

    public function parseResetFormAction(): void
    {
        $hasErrors = true;

        $form = new ResetForm();
        $form->bind($this->request->getPost(), new stdClass());
        if (
            $form->validate($this)
            && $this->request->get('passwordResetToken') !== null
            && $this->request->get('password') === $this->request->get('password2')
        ) :
            $user = $this->repositories->user->getByPasswordResetToken($this->request->get('passwordResetToken'));
            if($user !== null) :
                $user->setPassword($this->security->hash($this->request->get('password')))
                    ->setPasswordReset(false)
                    ->setForcePasswordReset(false)
                    ->save()
                ;

                $hasErrors = false;
                $this->flash->setSucces('USER_PASSWORD_CHANGE_SUCCESS');
            endif;
        endif;

        if($hasErrors) :
            $this->flash->setError('CORE_SOMETHING_IS_WRONG');
        endif;

        $this->redirect('user/loginform');
    }
}
