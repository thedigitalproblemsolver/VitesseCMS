<?php declare(strict_types=1);

namespace VitesseCms\Communication\Controllers;

use VitesseCms\Communication\Factories\NewsletterQueueFactory;
use VitesseCms\Communication\Forms\NewsletterForm;
use VitesseCms\Communication\Helpers\NewsletterHelper;
use VitesseCms\Communication\Helpers\NewsletterQueueHelper;
use VitesseCms\Communication\Interfaces\RepositoriesInterface;
use VitesseCms\Communication\Models\Newsletter;
use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Database\AbstractCollection;

class AdminnewsletterController extends AbstractAdminController implements RepositoriesInterface
{
    /**
     * @var array
     */
    protected $parseAsJob = ['sendPreview'];

    public function onConstruct(): void
    {
        parent::onConstruct();

        $this->class = Newsletter::class;
        $this->classForm = NewsletterForm::class;
        $this->listOrder = 'createdAt';
        $this->listOrderDirection = -1;
        $this->listNestable = true;
        $this->listSortable = true;
    }

    public function sendPreviewAction(string $id): void
    {
        if ($id && $this->request->get('previewEmail')) :
            Newsletter::setFindPublished(false);
            $newsletter = Newsletter::findById($id);
            if ($newsletter) :
                NewsletterQueueHelper::send(
                    NewsletterQueueFactory::create(
                        $this->request->get('previewEmail'),
                        '',
                        $id,
                        '',
                        $newsletter->_('subject'),
                        $newsletter->_('body')
                    ),
                    $this->setting,
                    $this->view
                );

                $this->flash->_('ADMIN_EMAIL_SEND_SUCCESS');
            else :
                $this->flash->_('ADMIN_EMAIL_SEND_FAILED', 'error');
            endif;
        else :
            $this->flash->_('ADMIN_EMAIL_SEND_FAILED', 'error');
        endif;

        parent::redirect();
    }

    public function queueNewsletterAction(string $id): void
    {
        $message = 'NEWSLETTER_NOT_FOUND';
        $newsletter = Newsletter::findById($id);
        if ($newsletter) :
            NewsletterHelper::queueMembers($newsletter);
            $message = 'NEWSLETTER_ADDED_TO_QUEUE';
        endif;
        $this->flash->_($message);

        parent::redirect();
    }

    public function beforeSave(AbstractCollection $item)
    {
        if (empty($this->request->get('emailHeaderImage'))) :
            unset($_POST['emailHeaderImage']);
        endif;
    }

    public function afterSave(AbstractCollection $newsletter): void
    {
        if ($newsletter->_('parentId')) :
            Newsletter::setFindPublished(false);
            $parentItem = Newsletter::findById($newsletter->_('parentId'));
            if ($parentItem) :
                $parentItem->set('hasChildren', true);
                $parentItem->save();
            endif;
        endif;
    }
}
