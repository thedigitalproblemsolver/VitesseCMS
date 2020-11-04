<?php declare(strict_types=1);

namespace VitesseCms\Sef\Utils;

use Phalcon\Di;
use Phalcon\Exception;
use Phalcon\Utils\Slug;

class UtmUtil
{
    /**
     * @var array
     */
    protected static $utm = [];

    public static function setSource(string $source): void
    {
        try {
            self::$utm['utm_source'] = Slug::generate($source);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            die();
        }
    }

    public static function setMedium(string $medium): void
    {
        try {
            self::$utm['utm_medium'] = Slug::generate($medium);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            die();
        }
    }

    public static function setCampaign(string $campaign): void
    {
        try {
            self::$utm['utm_campaign'] = Slug::generate($campaign);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            die();
        }
    }

    public static function setTerm(string $term): void
    {
        try {
            self::$utm['utm_term'] = Slug::generate($term);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            die();
        }
    }

    public static function setContent(string $content): void
    {
        try {
            self::$utm['utm_content'] = Slug::generate($content);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            die();
        }
    }

    public static function reset(): void
    {
        self::$utm = [];
    }

    public static function append(string $string, bool $reset = true): string
    {
        $regex = '/<a href="' . self::getRegexBase() . '([^"]*)"([^>]*)>/i';
        $return = preg_replace_callback($regex, 'self::appendToTag', $string);

        if($reset) :
            self::reset();
        endif;

        return $return;
    }

    public static function appendToUrl(string $url, bool $reset = true): string
    {
        if (\count(self::$utm) > 0) :
            if (strpos($url, '?') === false) {
                $url .= '?';
            }
            $url .= http_build_query(self::$utm);
        endif;

        if($reset) :
            self::reset();
        endif;

        return $url;
    }

    protected static function appendToTag(array $match): string
    {
        $url = $match[1];
        if (\count(self::$utm) > 0) :
            if (strpos($url, '?') === false) {
                $url .= '?';
            }
            $url .= http_build_query(self::$utm);
        endif;

        return '<a href="' . self::getRegexBase(false) . $url.'"'.$match[2].'>';
    }

    protected static function getRegexBase(bool $str_replace = true): string
    {
        $url = Di::getDefault()->get('url')->getBaseUri();

        $url = substr($url, 0,  - 1);

        if($str_replace) :
            return str_replace('/', '\/', $url);
        endif;

        return $url;
    }
}
