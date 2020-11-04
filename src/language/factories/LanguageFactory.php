<?php

namespace VitesseCms\Language\Factories;

use VitesseCms\Language\Models\Language;

/**
 * Class LanguageFactory
 */
class LanguageFactory
{
    /**
     * @param string $name
     * @param string $nativeName
     * @param string $locale
     * @param string $short
     * @param string $domain
     * @param string $flagClass
     * @param bool $published
     *
     * @return Language
     */
    public static function create(
        string $name,
        string $nativeName,
        string $locale,
        string $short,
        string $domain,
        string $flagClass = '',
        bool $published = false
    ): Language {
        $language = new Language();
        $language->set('name', $name);
        $language->set('nativeName', $nativeName);
        $language->set('locale', $locale);
        $language->set('short', $short);
        $language->set('domain', $domain);
        $language->set('flagClass', $flagClass);
        $language->set('published', $published);

        return $language;
    }
}
