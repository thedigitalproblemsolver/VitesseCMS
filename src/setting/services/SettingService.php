<?php declare(strict_types=1);

namespace VitesseCms\Setting\Services;

use VitesseCms\Setting\Models\Setting;
use VitesseCms\Core\Services\CacheService;
use VitesseCms\Configuration\Services\ConfigService;
use VitesseCms\Language\Models\Language;
use VitesseCms\Setting\Factory\SettingFactory;

class SettingService
{
    /**
     * @var CacheService
     */
    protected $cache;

    /**
     * @var ConfigService
     */
    protected $configuration;

    public function __construct(
        CacheService $cache,
        ConfigService $configuration
    ) {
        $this->cache = $cache;
        $this->configuration = $configuration;
    }

    public function get(string $settingKey)
    {
        if (Language::count() === 0) :
            return '';
        endif;

        $settingKey = $this->buildCallingName($settingKey);
        $cacheKey = $this->cache->getCacheKey($settingKey.$this->configuration->getLanguageShort());
        $content = $this->cache->get($cacheKey);
        if (!$content) :
            Setting::setFindValue('calling_name', $settingKey);
            /** @var Setting $setting */
            $setting = Setting::findFirst();
            if (!$setting) :
                Setting::setFindValue('calling_name', $settingKey);
                Setting::setFindPublished(false);
                $setting = Setting::findFirst();
                if (!$setting) :
                    $setting = SettingFactory::create($settingKey, '');
                    $setting->save();

                    return '';
                else :
                    $content = $setting->_('value');
                    $this->cache->save($cacheKey, $content);
                endif;
            else :
                $content = $setting->_('value');
                $this->cache->save($cacheKey, $content);
            endif;
        endif;

        return $content;
    }

    public function has(string $setting): bool
    {
        $setting = $this->buildCallingName($setting);

        $content = $this->cache->get(
            $this->cache->getCacheKey($setting)
        );
        if (!$content) :
            Setting::setFindValue('calling_name', $setting);
            $content = Setting::count();
        endif;

        return (bool)$content;
    }

    /**
     * @param string $setting
     *
     * @return mixed|null|string
     *
     * @deprecated please use ->get
     */
    public function _(string $setting)
    {
        return $this->get($setting);
    }

    public function parsePlaceholders(string $content): string
    {
        preg_match_all('/{{([A-Z_-]*)}}/', $content, $aMatches);
        foreach ((array)$aMatches[1] as $key => $value) :
            if (substr_count($value, '_') === 2) :
                $content = str_replace(
                    ['{{{'.$value.'}}}', '{{'.$value.'}}'],
                    $this->get($value),
                    $content
                );
            endif;
        endforeach;

        return $content;
    }

    protected function buildCallingName(string $setting): string
    {
        return strtoupper(str_replace([' ', '-'], '_', $setting));
    }
}
