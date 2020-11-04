<?php

namespace VitesseCms\Communication\Controllers;

use VitesseCms\Communication\Forms\NewsletterTemplateForm;
use VitesseCms\Communication\Models\NewsletterTemplate;
use VitesseCms\Admin\AbstractAdminController;

/**
 * Class AdminnewslettertemplateController
 */
class AdminnewslettertemplateController extends AbstractAdminController
{

    /**
     * onConstruct
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = NewsletterTemplate::class;
        $this->classForm = NewsletterTemplateForm::class;
    }
}
