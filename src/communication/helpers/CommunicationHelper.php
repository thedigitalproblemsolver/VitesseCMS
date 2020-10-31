<?php declare(strict_types=1);

namespace VitesseCms\Communication\Helpers;

use VitesseCms\Communication\Models\Email;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Factories\LogFactory;
use Phalcon\Di;
use Phalcon\Mvc\Router;

class CommunicationHelper
{
    public static function sendRedirectEmail(
        Router $router,
        ViewService $view
    ): void {
        $systemMailState = null;

        if (Di::getDefault()->get('flash')->has('error')) :
            $systemMailState = 'danger';
        elseif (Di::getDefault()->get('flash')->has('success')) :
            $systemMailState = 'success';
        elseif (Di::getDefault()->get('flash')->has('notice')) :
            $systemMailState = 'notice';
        elseif (Di::getDefault()->get('flash')->has('warning')) :
            $systemMailState = 'warning';
        endif;

        if ($systemMailState !== null) :
            $toAddress = Di::getDefault()->get('user')->_('email');
            if ($view->getVar('systemEmailToAddress')) :
                $toAddress = $view->getVar('systemEmailToAddress');
            endif;
            self::sendSystemEmail($toAddress, $systemMailState, strtolower(
                $router->getModuleName() .
                $router->getControllerName() .
                $router->getActionName()
            ));
        endif;
    }
    
    public static function sendSystemEmail(
        string $toAddress,
        string $systemMailState,
        string $systemAction,
        string $replyTo = ''
    ): void
    {
        if (!filter_var($toAddress, FILTER_VALIDATE_EMAIL) === false) :
            Email::setFindValue('state', $systemMailState);
            Email::setFindValue('systemAction', $systemAction);
            $emails = Email::findAll();
            if (\count($emails) > 0 ) :
                /** @var AbstractCollection $email */
                foreach ($emails as $email) :
                    $realToAddress = $toAddress;
                    if(
                        \is_string($email->_('recipient'))
                        && !empty($email->_('recipient'))
                    ) :
                        $realToAddress = $email->_('recipient');
                    endif;
                    $sendResult = Di::getDefault()->get('mailer')->sendMail(
                        $realToAddress,
                        $email->_('subject'),
                        $email->_('body'),
                        '',
                        $replyTo
                    );
                    if (
                        $sendResult
                        && \is_string($email->_('messageSuccess'))
                        && $email->_('messageSuccess')
                    ) :
                        Di::getDefault()->get('flash')->success($email->_('messageSuccess'));
                        LogFactory::create(
                            $email->getId(),
                            Email::class,
                            'Email send successfull : '.$email->_('subject').' to '.$realToAddress
                        )->save();
                    elseif (
                        \is_string($email->_('messageError'))
                        && $email->_('messageError')
                    ) :
                        Di::getDefault()->get('flash')->error($email->_('messageError'));
                        LogFactory::create(
                            $email->getId(),
                            Email::class,
                            'Email send failed : '.$email->_('subject').' to '.$realToAddress
                        )->save();
                    endif;
                endforeach;
            endif;
        endif;
    }
}
