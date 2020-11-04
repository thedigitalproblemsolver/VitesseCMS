<?php declare(strict_types=1);

namespace VitesseCms\Form\Listeners;

use VitesseCms\Form\Controllers\AdminsubmissionController;
use VitesseCms\Form\Helpers\SubmissionHelper;
use VitesseCms\Form\Models\Submission;
use Phalcon\Events\Event;

class AdminsubmissionControllerListener
{
    public function beforeEdit(
        Event $event,
        AdminsubmissionController $controller,
        Submission $submission
    ): void {
        $controller->addRenderParam(
            'adminEditForm',
            SubmissionHelper::getHtmlAdminTable($submission, true)
        );
    }
}
