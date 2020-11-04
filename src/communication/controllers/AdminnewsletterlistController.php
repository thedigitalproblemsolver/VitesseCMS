<?php declare(strict_types=1);

namespace VitesseCms\Communication\Controllers;

use VitesseCms\Communication\Forms\NewsletterListForm;
use VitesseCms\Communication\Forms\NewsletterListImportForm;
use VitesseCms\Communication\Helpers\ExportHelper;
use VitesseCms\Communication\Models\NewsletterList;
use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Utils\FileUtil;

class AdminnewsletterlistController extends AbstractAdminController
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = NewsletterList::class;
        $this->classForm = NewsletterListForm::class;
    }

    public function importFormAction(string $newsletterListId): void
    {
        $form = new NewsletterListImportForm(NewsletterList::findById($newsletterListId));
        $this->view->setVar(
            'content',
            $form->renderForm(
            'admin/communication/adminnewsletterlist/parseimportform/'
            )
        );

        $this->prepareView();
    }

    public function exportAction(string $newsletterListId): void
    {
        $exportHelper = new ExportHelper($this->setting);
        $exportHelper->setItems([NewsletterList::findById($newsletterListId)]);
        $exportHelper->setHeaders();
        $exportHelper->createOutput();
        $this->view->disable();
    }

    public function parseImportFormAction(): void
    {
        if($this->request->get('newsletterlist') && $this->request->hasFiles()) :
            foreach ($this->request->getUploadedFiles() as $file) :
                $name = FileUtil::sanatize($file->getName());
                if ($file->moveTo($this->config->get('uploadDir') . $name)) :
                    if (($handle = fopen($this->config->get('uploadDir') . $name, 'rb')) !== false) :
                        /** @var NewsletterList $newsletterList */
                        $newsletterList = NewsletterList::findById($this->request->get('newsletterlist'));
                        while (($data = fgetcsv($handle, 1000, ',')) !== false) :
                            $newsletterList->addMember($data[0]);
                        endwhile;
                        $newsletterList->save();
                    endif;
                    $this->flash->_('ADMIN_FILE_UPLOAD_SUCCESS', 'success', [$file->getName()]);
                else :
                    $this->flash->_('ADMIN_FILE_UPLOAD_FAILED', 'error', [$file->getName()]);
                endif;
            endforeach;

            $this->redirect(
                $this->url->getBaseUri().
                'admin/communication/adminnewsletterlist/edit/'.
                $this->request->get('newsletterlist')
            );
        endif;

        $this->redirect();
    }

    public function deleteMemberAction(string $id, int $key): void
    {
        if($id && is_numeric($key)) :
            NewsletterList::setFindPublished(false);
            $newsletterList = NewsletterList::findById($id);
            $members = $newsletterList->_('members');
            if(isset($members[$key])) :
                $newsletterList->removeMember($members[$key]['email'])->save();
            endif;
        endif;

        $this->redirect();
    }

    public function unsubscribeMemberAction(string $id, int $key): void
    {
        if($id && is_numeric($key)) :
            NewsletterList::setFindPublished(false);
            /** @var NewsletterList $newsletterList */
            $newsletterList = NewsletterList::findById($id);
            $members = $newsletterList->_('members');
            if(isset($members[$key])) :
                $newsletterList = $newsletterList->unsubscribeMember($members[$key]['email']);
            endif;
            $newsletterList->save();
        endif;

        $this->redirect();
    }

    public function subscribeMemberAction(string $id, int $key): void
    {
        if($id && is_numeric($key)) :
            NewsletterList::setFindPublished(false);
            /** @var NewsletterList $newsletterList */
            $newsletterList = NewsletterList::findById($id);
            $members = $newsletterList->_('members');
            if(isset($members[$key])) :
                $newsletterList = $newsletterList->subscribeMember($members[$key]['email']);
            endif;
            $newsletterList->save();
        endif;

        $this->redirect();
    }

    public function beforeSave(AbstractCollection $item)
    {
        if ($this->request->get('addEmail')) :
            /** @var NewsletterList $item */
            $item->addMember($this->request->get('addEmail'));
            $this->log->write(
                $item->getId(),
                NewsletterList::class,
                'Added '.$this->request->get('addEmail').' to '.$item->_('name').' by admin'
            );
            $_POST['addEmail'] = null;
        endif;
    }
}
