<?php declare(strict_types=1);

namespace VitesseCms\Media\Controllers;

use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Media\Helpers\ImageHelper;
use VitesseCms\Media\Utils\MediaUtil;

class ImageController extends AbstractInjectable
{
    public function indexAction(): void
    {
        $this->url->setUrl($_SERVER['REQUEST_URI']);
        parse_str($this->url->getParsed('query'), $urlQuery);
        $file = $this->configuration->getWebDir().$this->url->getParsed('path');
        $width = isset($urlQuery['w']) ? (int)$urlQuery['w'] : 0;
        $height = isset($urlQuery['h']) ? (int)$urlQuery['h'] : 0;

        $resizedFile = MediaUtil::getResizeFilename($file, $width, $height);
        $cacheDir = $this->configuration->getCacheDir().'resized/';

        if (is_array($urlQuery) && count($urlQuery) > 0) :
            if (
                !is_file($cacheDir.$resizedFile)
                || $this->session->get('cache') === false
                || (
                    is_file($cacheDir.$resizedFile)
                    && filemtime($file) > filemtime($cacheDir.$resizedFile)
                )
            ) :
                ImageHelper::resize($file, $width, $height);
            endif;
            $file = $cacheDir.$resizedFile;
        endif;

        FileUtil::display($file);
    }
}
