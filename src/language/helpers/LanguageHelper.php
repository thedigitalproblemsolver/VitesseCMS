<?php

namespace VitesseCms\Language\Helpers;

use VitesseCms\Core\Factories\ObjectFactory;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Admin\Utils\AdminUtil;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Di;

/**
 * Class LanguageHelper
 */
class LanguageHelper
{

    /**
     * @var BaseObjectInterface
     */
    protected static $translations;

    /**
     * @var string
     */
    protected static $translationDir;

    /**
     * @var array
     */
    protected static $parsedFiles;

    /**
     * @deprecated moved to service
     */
    protected static function init()
    {
        if (!self::$translations) :
            self::$translations = ObjectFactory::create();
            self::$translationDir = __DIR__ . '/../translations/' .
                Di::getDefault()->get('configuration')->getLanguageLocale() .
                '/';
            self::$translations->set('CORE', new Ini(self::$translationDir . 'core.ini'));
            self::$parsedFiles = ['CORE'];

            if (AdminUtil::isAdminPage()) :
                self::$translations->set('ADMIN', new Ini(self::$translationDir . strtolower('admin.ini')));
                self::$parsedFiles[] = 'ADMIN';
            endif;
        endif;
    }

    /**
     * @deprecated move to service
     */
    public static function _(string $key, array $replace = []): string
    {
        self::init();

        $parts = explode('_', $key);
        $iniFile = self::$translationDir . strtolower($parts[0]) . '.ini';
        $return = $key;
        if (
            !in_array($parts[0], self::$parsedFiles)
            && is_file($iniFile)
        ) :
            self::$translations->set(
                $parts[0],
                new Ini($iniFile)
            );
            self::$parsedFiles[] = $parts[0];
        endif;

        if(is_file($iniFile)) :
            $key = str_replace($parts[0] . '_', '', $key);
            $return = self::$translations->_($parts[0])->get($key, '');
        endif;

        if (count($replace) > 0) :
            $search = [];
            foreach ($replace as $part) :
                $search[] = '/%s/';
            endforeach;

            $return = preg_replace( $search, $replace, $return, 1);
        endif;

        return $return;
    }

    public static function parsePlaceholders(string $string): string
    {
        $parsed = [];

        preg_match_all("/%([A-Z_]*)%/", $string, $aMatches);
        foreach ($aMatches[1] as $key => $value) :
            if (!in_array($value, $parsed)) :
                $string = str_replace('%' . $value . '%', LanguageHelper::_($value), $string);
                $parsed[] = $value;
            endif;
        endforeach;

        return $string;
    }
}
