<?php

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Shop\Forms\CountryForm;
use VitesseCms\Shop\Models\Country;

/**
 * Class AdmincountryController
 */
class AdmincountryController extends AbstractAdminController
{

    /**
     * onConstruct
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Country::class;
        $this->classForm = CountryForm::class;
    }
}
