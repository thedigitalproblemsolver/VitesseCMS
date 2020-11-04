<?php declare(strict_types=1);

namespace VitesseCms\Language\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Language\Forms\LanguageForm;
use VitesseCms\Language\Models\Language;

class AdminlanguageController extends AbstractAdminController
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Language::class;
        $this->classForm = LanguageForm::class;
    }
}
