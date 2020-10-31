<?php declare(strict_types=1);

namespace VitesseCms\Communication\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Communication\Forms\EmailForm;
use VitesseCms\Communication\Models\Email;

class AdminemailController extends AbstractAdminController
{
    /**
     * @var array
     */
    protected $parseAsJob = ['sendPreview'];

    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Email::class;
        $this->classForm = EmailForm::class;
        $this->listOrder = 'subject';
        $this->listTemplate = 'listNewsletterQueue';
        $this->listTemplatePath = $this->configuration->getRootDir() . 'src/communication/resources/views/admin/';
    }

    public function sendPreviewAction(string $id): void
    {
        if($this->user->getId()) :
            Email::setFindPublished(false);
            $email = Email::findById($id);
            $this->mailer->sendMail(
                $this->user->_('email'),
                $email->_('subject'),
                $email->_('body')
            );
            $this->flash->_('Preview email is send to '. $this->user->_('email'));
        endif;

        $this->redirect();
    }
}
