<?php declare(strict_types=1);

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Shop\Forms\OrderStateForm;
use VitesseCms\Shop\Models\OrderState;

class AdminorderstateController extends AbstractAdminController
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = OrderState::class;
        $this->classForm = OrderStateForm::class;
        $this->listOrder = 'ordering';
        $this->listSortable = true;
        $this->listNestable = true;
    }
}
