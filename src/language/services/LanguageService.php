<?php declare(strict_types=1);

namespace VitesseCms\Language\Services;

use VitesseCms\Admin\Utils\AdminUtil;
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

        if (!isset($this->translations[$module])) :
            $iniFile = $this->configuration->getTranslationDir() . strtolower($module) . '.ini';
            $accountIniFile = $this->configuration->getAccountTranslationDir() . strtolower($module) . '.ini';
            $vendorNameAdminIniFile = $this->configuration->getVendorNameDir() . strtolower($module) . '/src/translations/' . $this->configuration->getLanguageLocale() . '/admin.ini';
            $this->translations[$module] = null;

            $this->addFileToTranslation($module, $iniFile);
            $this->addFileToTranslation($module, $accountIniFile);
            if (AdminUtil::isAdminPage()) :
                $this->addFileToTranslation($module, $vendorNameAdminIniFile);
            endif;
        endif;

        if ($this->translations[$module] === null) :
            return $key;
        endif;

        $return = $this->translations[$module]->get(str_replace($module . '_', '', $key), $key);

        if (count($replace) > 0) :
            $search = [];
            foreach ($replace as $part) :
                $search[] = '/%s/';
            endforeach;

            $return = preg_replace($search, $replace, $return, 1);
        endif;

        return $return;
    }

    private function addFileToTranslation(string $module, string $file): void
    {
        if (is_file($file)) :
            if ($this->translations[$module] === null) :
                $this->translations[$module] = new Ini($file);
            else :
                $this->translations[$module]->merge(new Ini($file));
            endif;
        endif;
    }

    public function parsePlaceholders(string $string): string
    {
        $parsed = [];

        preg_match_all("/%([A-Z_]*)%/", $string, $aMatches);
        foreach ($aMatches[1] as $key => $value) :
            if (!in_array($value, $parsed, true)) :
                $string = str_replace('%' . $value . '%', $this->get($value), $string);
                $parsed[] = $value;
            endif;
        endforeach;

        return $string;
    }
}
