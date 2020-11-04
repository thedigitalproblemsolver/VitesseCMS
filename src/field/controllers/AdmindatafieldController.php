<?php declare(strict_types=1);

namespace VitesseCms\Field\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Field\Forms\DataFieldForm;
use VitesseCms\Core\Models\Datafield;

class AdmindatafieldController extends AbstractAdminController
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Datafield::class;
        $this->classForm = DataFieldForm::class;
    }
}
