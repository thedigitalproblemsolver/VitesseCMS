<?php declare(strict_types=1);

namespace VitesseCms\Core\Services;

use VitesseCms\Configuration\Utils\DomainConfigUtil;
use VitesseCms\Language\Models\Language;

class ConfigService
{
    /**
     * @var DomainConfigUtil
     */
    protected $config;

    /**
     * @var UrlService
     */
    protected $url;

    /**
     * @var Language
     */
    protected $language;

    public function __construct(DomainConfigUtil $config, UrlService $url)
    {
        $this->config = $config;
        $this->url = $url;
    }

    public function getUploadUri(): string
    {
        return str_replace('/'.$this->getLanguageShort().'/', '/', $this->url->getBaseUri())
            .'uploads/'.
            $this->config->get('account').'/';
    }

    public function getTranslationDir(): string {
        return $this->getModuleDir() . 'language/translations/' .$this->getLanguageLocale() .'/';
    }

    public function getAccountTranslationDir(): string {
        return $this->getAccountDir() . 'src/language/translations/' .$this->getLanguageLocale() .'/';
    }

    public function getAccount(): string
    {
        return $this->config->getAccount();
    }

    public function getTemplateDir(): string
    {
        return $this->config->getTemplateDir();
    }

    public function getCoreTemplateDir(): string
    {
        return $this->config->getCoreTemplateDir();
    }

    public function getRootDir(): string
    {
        return $this->config->getRootDir();
    }

    public function getWebDir(): string
    {
        return $this->config->getWebDir();
    }

    public function getAssetsDir(): string
    {
        return $this->getWebDir().'assets/'.$this->config->get('account').'/';
    }

    public function getUploadDir(): string
    {
        return $this->config->getUploadDir();
    }

    public function getUploadBaseDir(): string
    {
        return $this->getSystemDir().'../public_html/uploads/';
    }

    public function getDomainDir(): string
    {
        return $this->config->getDomainDir();
    }

    public function getAccountDir(): string
    {
        return $this->config->getAccountDir();
    }

    public function getCacheDir(): string
    {
        return $this->config->getCacheDir();
    }

    public function getSystemDir(): string
    {
        return $this->config->getSystemDir();
    }

    public function getModuleDir(): string
    {
        return $this->config->getModuleDir();
    }

    public function getLanguageShort(): string
    {
        if ($this->language !== null) :
            return $this->language->getShortCode();
        endif;

        return $this->config->getLanguageShort();
    }

    public function getLanguageLocale(): string
    {
        if ($this->language !== null) :
            return $this->language->getLocale();
        endif;

        return $this->config->getLanguageLocale();
    }

    public function getLanguageShortDefault(): ?string
    {
        return $this->config->getLanguageShortDefault();
    }


    public function hasLanguage(): bool
    {
        return $this->config->hasLanguage() || $this->language !== null;
    }

    public function getHost(): string
    {
        return $this->config->getHost();
    }

    public function setLanguage(Language $language): ConfigService
    {
        $this->language = $language;

        return $this;
    }

    public function hasMovedTo(): bool
    {
        return $this->config->hasMovedTo();
    }

    public function getMovedTo(): string
    {
        return $this->config->getMovedTo();
    }

    public function getMongoDatabase(): string
    {
        return $this->config->getMongoDatabase();
    }

    public function getMongoUri(): string
    {
        return $this->config->getMongoUri();
    }

    public function renderAdminListChildren(): bool
    {
        return $this->config->renderAdminListChildren();
    }

    public function getBeanstalkHost(): string
    {
        return $this->config->getBeanstalkHost();
    }

    public function getBeanstalkPort(): string
    {
        return $this->config->getBeanstalkPort();
    }

    public function getTemplatePositions(): array
    {
        return (array) $this->config->getTemplate()->get('positions');
    }

    public function isEcommerce(): bool
    {
        if($this->config->get('ecommerce') === null ):
            return false;
        endif;

        return (bool) $this->config->get('ecommerce');
    }
}
