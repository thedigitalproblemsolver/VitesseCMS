<?php

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Shop\Forms\PaymentForm;
use VitesseCms\Shop\Models\Payment;

/**
 * Class AdminpaymentController
 */
class AdminpaymentController extends AbstractAdminController
{

    /**
     * onConstruct
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Payment::class;
        $this->classForm = PaymentForm::class;
    }
}
