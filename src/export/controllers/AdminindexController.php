<?php declare(strict_types=1);

namespace VitesseCms\Export\Controllers;

use VitesseCms\Admin\AbstractAdminController;

class AdminindexController extends AbstractAdminController
{
    public function indexAction(): void
    {
        $this->view->setVar('content', $this->view->renderTemplate(
            'export_menu',
            $this->configuration->getRootDir().'src/export/resources/views/admin/'
        ));
        $this->prepareView();
    }
}
