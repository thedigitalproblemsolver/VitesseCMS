<?php declare(strict_types=1);

namespace VitesseCms\Form\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Form\Models\Submission;

class AdminsubmissionController extends AbstractAdminController
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Submission::class;
        $this->listOrderDirection = -1;
    }
}
