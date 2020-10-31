<?php declare(strict_types=1);

namespace VitesseCms\Language\Services;

use VitesseCms\Core\Services\AbstractInjectableService;
use Phalcon\Config\Adapter\Ini;

class LanguageService extends AbstractInjectableService
{
    /**
     * @var Ini[]
     */
    protected $translations;

    public function __construct()
    {
        $this->translations = [];
    }

    public function get(string $key, array $replace = []): string
    {
        $parts = explode('_', $key);
        $module = $parts[0];
        $iniFile = $this->configuration->getTranslationDir().strtolower($module).'.ini';
        $accountIniFile = $this->configuration->getAccountTranslationDir().strtolower($module).'.ini';

        if (
            empty($this->translations[$module])
            && is_file($iniFile)
        ) :
            $this->translations[$module] = new Ini($iniFile);
            if(is_file($accountIniFile)) :
                $this->translations[$module]->merge(new Ini($accountIniFile));
            endif;
        endif;

        $return = $this->translations[$module]->get(str_replace($module.'_', '', $key), $key);

        if (count($replace) > 0) :
            $search = [];
            foreach ($replace as $part) :
                $search[] = '/%s/';
            endforeach;

            $return = preg_replace($search, $replace, $return, 1);
        endif;

        return $return;
    }

    public function parsePlaceholders(string $string): string
    {
        $parsed = [];

        preg_match_all("/%([A-Z_]*)%/", $string, $aMatches);
        foreach ($aMatches[1] as $key => $value) :
            if (!in_array($value, $parsed, true)) :
                $string = str_replace('%'.$value.'%', $this->get($value), $string);
                $parsed[] = $value;
            endif;
        endforeach;

        return $string;
    }
}
