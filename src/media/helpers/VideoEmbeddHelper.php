<?php declare(strict_types=1);

namespace VitesseCms\Media\Helpers;

use VitesseCms\Core\Factories\ObjectFactory;
use VitesseCms\Core\Services\ViewService;
use Phalcon\Di;

class VideoEmbeddHelper
{
    /**
     * @var array
     */
    protected static $url;

    public static function setUrl(string $url): void
    {
        self::$url = parse_url($url);
        if(isset(self::$url['query'])) :
            parse_str(self::$url['query'], self::$url['query']);
        endif;
    }

    public static function getEmbeddCode(ViewService $view, string $url = null): string
    {
        if(is_string($url)) :
            self::setUrl($url);
        endif;

        $video = ObjectFactory::create();
        switch(VideoEmbeddHelper::getProvider()) :
            case 'youtube':
                if(isset(self::$url['query']['v'])) :
                    $video->set('videoId',self::$url['query']['v']);
                else :
                    $video->set('videoId',str_replace('/','',self::$url['path']));
                endif;
                break;
        endswitch;

        if($video->_('videoId')) :
            return $view->renderTemplate(
                self::getProvider(),
                Di::getDefault()->get('config')->get('rootDir') . 'template/core/views/partials/video',
                ['video' => $video]

            );
        endif;

        return '';
    }

    public static function getProvider(string $url = null): string
    {
        if(is_string($url)) :
            self::setUrl($url);
        endif;
        if(
            substr_count(self::$url['host'],'youtube')
            || substr_count(self::$url['host'],'youtu.be')
        ) :
            return 'youtube';
        endif;

        return '';
    }
}
