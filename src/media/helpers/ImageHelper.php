<?php

namespace VitesseCms\Media\Helpers;

use VitesseCms\Media\Utils\MediaUtil;
use Phalcon\Di;
use Phalcon\Image\Adapter\Imagick;

/**
 * Class ImageHelper
 */
class ImageHelper
{
    /**
     * @param string $file
     * @param int $width
     * @param int $height
     *
     * @return string
     */
    public static function resize(string $file, int $width = 0, int $height = 0): string
    {
        if ($width === 0 && $height === 0) :
            return $file;
        endif;

        //From : http://phalcontip.com/discussion/19/crop-to-fit-image-with-gd
        $image = new Imagick($file);
        $source_height = $image->getHeight();
        $source_width = $image->getWidth();
        $source_aspect_ratio = $source_width / $source_height;
        $orgHeight = $height;
        $orgWidth = $width;

        if ($height === 0) :
            $height = $source_aspect_ratio * $source_height;
        endif;

        if ($width === 0) :
            $a = $height / $source_height;
            $width = $a * $source_width;
        endif;

        $desired_aspect_ratio = $width / $height;
        if ($source_aspect_ratio > $desired_aspect_ratio) :
            $newHeight = round($height);
            $newWidth = round((int)($height * $source_aspect_ratio));
        else :
            $newWidth = round($width);
            $newHeight = round((int)($width / $source_aspect_ratio));
        endif;

        if ($newHeight > 0 && $newWidth > 0) :
            $newFile = MediaUtil::getResizeFilename($file, $orgWidth, $orgHeight);
            $cacheDir = Di::getDefault()->get('config')->get('cacheDir') . 'resized/';

            if (
                is_file($cacheDir . $newFile)
                && Di::getDefault()->get('session')->get('cache') !== false
            ) :
                $file = $cacheDir . $newFile;
            else :
                if (!is_dir($cacheDir)) :
                    mkdir($cacheDir);
                endif;

                $image->resize($newWidth, $newHeight, 85);
                $image->save($cacheDir . $newFile);
                if (is_file($cacheDir . $newFile)) :
                    $file = $cacheDir . $newFile;
                endif;
            endif;
        endif;

        return $file;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public static function buildUrl(string $file): string
    {
        return Di::getDefault()->get('url')->getBaseUri() .
            'uploads/' .
            Di::getDefault()->get('config')->get('account') .
            '/' . $file;
    }
}
