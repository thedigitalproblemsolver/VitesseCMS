<?php

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Shop\Forms\TaxRateForm;
use VitesseCms\Shop\Models\TaxRate;

/**
 * Class AdmintaxrateController
 */
class AdmintaxrateController extends AbstractAdminController
{
    /**
     * onConstruct
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = TaxRate::class;
        $this->classForm  = TaxRateForm::class;
    }
}
