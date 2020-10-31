<?php

namespace VitesseCms\Sef\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Core\Helpers\Item;
use VitesseCms\Sef\Forms\RedirectForm;
use VitesseCms\Sef\Models\Redirect;

/**
 * Class AdmindatagroupController
 */
class AdminredirectController extends AbstractAdminController
{
    /**
     * onConstruct
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Redirect::class;
        $this->classForm = RedirectForm::class;
    }
}
