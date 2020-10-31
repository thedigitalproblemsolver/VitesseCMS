<?php

namespace VitesseCms\Media\Utils;

use VitesseCms\Core\Utils\FileUtil;


/**
 * Class MediaUtil
 */
class MediaUtil
{
    /**
     * @param string $file
     * @param int $width
     * @param int $height
     *
     * @return string
     */
    public static function getResizeFilename(
        string $file,
        int $width = 0,
        int $height = 0
    ): string
    {
        if($width === 0 && $height === 0 ) :
            return $file;
        endif;
        FileUtil::setFile($file);

        return FileUtil::getName().'_'.$width.'x'.$height.'.'.FileUtil::getExtension();
    }
}
