<?php declare(strict_types=1);

namespace VitesseCms\Media\Controllers;

use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Core\Utils\FileUtil;

class AssetsController extends AbstractInjectable
{
    public function indexAction(): void
    {
        $this->url->setUrl($_SERVER['REQUEST_URI']);
        $urlPath = $this->url->getParsed('path');
        $file = $this->config->get('webDir').ltrim($urlPath, '/');
        if (is_file($file)) :
            FileUtil::display($file);
        endif;
    }
}
