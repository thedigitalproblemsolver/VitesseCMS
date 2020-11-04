<?php declare(strict_types=1);

namespace VitesseCms\Sef\Utils;

use VitesseCms\Core\Utils\UrlUtil;
use Phalcon\Di;

class SefUtil {
    public static function fixSlashes(string $slug) : string
    {
        $file = str_replace('//','/',Di::getDefault()->get('config')->get('webDir').$slug);
        $urlExists = false;

        if(Di::getDefault()->get('config')->get('importUrl')) :
            $url = Di::getDefault()->get('config')->get('importUrl').$slug;
            $urlExists = UrlUtil::exists($url);
        endif;

        if( !is_file($file) && !$urlExists) :
            $slug = rtrim($slug, '/') . '/';
            $slug = '/'.ltrim($slug, '/');
        endif;


        return $slug;
    }

    public static function clientIsBot(string $userAgent): bool
    {
        $userAgent = strtolower($userAgent);

        return
            strpos($userAgent, 'googlebot') === true
            || strpos($userAgent, 'google-structured-data-testing-tool') === true
            || strpos($userAgent, 'bingbot') === true
            || strpos($userAgent, 'msnbot') === true
        ;
    }
}
