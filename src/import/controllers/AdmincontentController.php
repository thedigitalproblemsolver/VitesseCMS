<?php

namespace VitesseCms\Import\Controllers;

use VitesseCms\Import\Forms\ImportTypeForm;
use VitesseCms\Import\Models\ImportType;

/**
 * Class DatagroupController
 */
class AdmincontentController extends AbstractImportController
{
    /**
     * onConstruct
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = ImportType::class;
        $this->classForm = ImportTypeForm::class;
    }
}
