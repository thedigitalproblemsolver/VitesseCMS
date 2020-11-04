<?php declare(strict_types=1);

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Shop\Forms\DiscountForm;
use VitesseCms\Shop\Models\Discount;

class AdmindiscountController extends AbstractAdminController
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Discount::class;
        $this->classForm = DiscountForm::class;
    }
}
