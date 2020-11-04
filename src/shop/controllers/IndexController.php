<?php

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Core\AbstractController;

/**
 * Class IndexController
 */
class IndexController extends AbstractController
{
    /**
     * indexAction
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function indexAction(): void
    {
        $this->redirect();
    }
}
