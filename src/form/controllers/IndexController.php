<?php declare(strict_types=1);

namespace VitesseCms\Form\Controllers;

use VitesseCms\Block\Models\BlockFormBuilder;
use VitesseCms\Communication\Helpers\NewsletterHelper;
use VitesseCms\Core\AbstractController;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Form\Helpers\SubmissionHelper;
use VitesseCms\Form\Interfaces\RepositoriesInterface;
use VitesseCms\Form\Models\Submission;
use VitesseCms\Form\Utils\FormUtil;
use VitesseCms\Form\Factories\SubmissionFactory;
use VitesseCms\Form\Forms\BaseForm;

class IndexController extends AbstractController implements RepositoriesInterface
{
    public function submitAction(): void
    {
        $hasErrors = true;
        if ($this->request->getPost('block')) :
            $post = $this->request->getPost();
            $blockFormBuilder = $this->repositories->blockFormBuilder->getById(
                $this->request->getPost('block'),
                $this->view
            );
            if($blockFormBuilder !== null) :
                if($blockFormBuilder->isUseRecaptcha() && !FormUtil::hasValidRecaptcha($post)) :
                    $hasErrors = true;
                else :
                    unset($post['password'], $post['password2'], $post['csrf']);
                    $languages = $this->repositories->language->findAll(null,false);
                    while ($languages->valid()):
                        $language = $languages->current();
                        $post['name'][$language->getShortCode()] = date('Y-m-d H:i:s') . ' : ' . $blockFormBuilder->getNameField();
                        $languages->next();
                    endwhile;

                    $datagroup = $this->repositories->datagroup->getById($blockFormBuilder->getDatagroup());
                    if($datagroup !== null):
                        $form = new BaseForm();
                        $datagroup->buildItemForm($form);
                        if ($form->isValid($post)) :
                            $submission = SubmissionFactory::createSubmit();
                            SubmissionHelper::bindFormPost($submission, $post);
                            $submission->save();

                            $this->parseSubmittedFiles($submission);
                            $submission->save();
                            $hasErrors = false;

                            $this->parseNewsletters($blockFormBuilder);

                            if ($submission->hasEmail()) :
                                $this->view->set('systemEmailToAddress', $submission->getEmail());
                            endif;
                            $this->view->set('formData', SubmissionHelper::getHtmlTable($submission));
                            $this->view->set('formAdminData', SubmissionHelper::getHtmlAdminTable($submission, true));
                            $this->session->set('blockSubmittedId', $this->request->getPost('block'));

                            if ($blockFormBuilder->hasSystemThankyouByShortCode($this->configuration->getLanguageShort())) :
                                $this->flash->success($blockFormBuilder->getSystemThankyou());
                            else :
                                $this->flash->success('');
                            endif;
                        else :
                            foreach ($form->getMessages() as $message) :
                                $this->flash->error($message->getMessage());
                            endforeach;
                        endif;
                    endif;
                endif;
            endif;
        endif;

        if ($hasErrors) :
            $this->flash->_('FORM_NOT_SAVED', 'error');
        endif;

        $this->redirect();
    }

    protected function parseSubmittedFiles(Submission $submission): Submission
    {
        if ($this->request->hasFiles() === true) :
            DirectoryUtil::exists(
                $this->configuration->getUploadDir() . 'uploads/',
                true
            );

            foreach ($this->request->getUploadedFiles() as $file) :
                if (!empty($file->getName())) :
                    $name = $submission->getId() . '_' . FileUtil::sanatize($file->getName());
                    if ($file->moveTo($this->config->get('uploadDir') . 'uploads/' . $name)) :
                        $key = $file->getKey();
                        if (substr_count($key, '.') > 0) :
                            $tmp = explode('.', $key);
                            $valueName = $tmp[0];
                            if (!is_array($submission->$valueName)) :
                                $submission->$valueName = [];
                            endif;
                            $submission->$valueName[$tmp[1]] = $name;
                        else :
                            $submission->$key = $name;
                            $submission->addFieldname($key);
                        endif;
                    else :
                        $this->flash->_(
                            'CORE_FILE_UPLOAD_FAILED',
                            'error',
                            [$file->getName()]
                        );
                    endif;
                endif;
            endforeach;
        endif;

        return $submission;
    }

    protected function parseNewsletters(BlockFormBuilder $blockFormBuilder): void
    {
        $newsletters = $blockFormBuilder->getNewsletters();
        if($this->request->hasPost('email')) :
            foreach ($newsletters as $newsletterId) :
                $newsletter = $this->repositories->newsletter->getById($newsletterId);
                if($newsletter) :
                    NewsletterHelper::addMemberByEmail($newsletter,$this->request->get('email'));
                endif;
            endforeach;
        endif;
    }
}
