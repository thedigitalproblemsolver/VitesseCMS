<?php declare(strict_types=1);

namespace VitesseCms\Configuration\Utils;

use Phalcon\Config\Adapter\Ini;
use VitesseCms\Core\Utils\DebugUtil;

class DomainConfigUtil extends Ini
{

    /**
     * @var Ini
     */
    protected $template;

    /**
     * @var string
     */
    protected $templateDir;

    /**
     * @var string
     */
    protected $coreTemplateDir;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $webDir;

    /**
     * @var string
     */
    protected $uploadDir;

    /**
     * @var string
     */
    protected $domainDir;

    /**
     * @var string
     */
    protected $accountDir;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var \stdClass
     */
    protected $language;

    /**
     * @var string
     */
    protected $systemDir;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $movedTo;

    /**
     * @var string
     */
    protected $languageShortDefault;

    public function __construct(string $filePath = '', ?mixed $mode = null)
    {
        $this->systemDir = __DIR__ . '/../../';
        $this->host = $_SERVER['HTTP_HOST'];
        $this->movedTo = '';

        $domainConfigFile = $this->systemDir.'../config/domain/'.$this->host.'/config.ini';
        if (!is_file($domainConfigFile)) :
            $this->host = str_replace('www.', '', $this->host);
            $domainConfigFile = $this->systemDir.'../config/domain/'.$this->host.'/config.ini';
            if (is_file($domainConfigFile)) :
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: http://'.$this->host);
            endif;
            if (DebugUtil::isDocker($_SERVER['SERVER_ADDR'] ?? '')) :
                $this->host = str_replace('new.', '', $this->host);
                $domainConfigFile = $this->systemDir.'../config/domain/'.$this->host.'/config.ini';
                if (!is_file($domainConfigFile)) :
                    throw new \RuntimeException('Geen domein aanwezig');
                endif;
            else :
                throw new \RuntimeException('Geen domein aanwezig');
            endif;
        endif;

        parent::__construct($domainConfigFile, $mode);
    }

    public function setTemplate(): DomainConfigUtil
    {
        $this->templateDir = $this->systemDir.'mustache/template/';
        $this->coreTemplateDir = $this->systemDir.'mustache/template/core/';

        $settingFile = $this->systemDir.'../config/template/'.$this->get('template').'/settings.ini';
        if( is_file($settingFile)):
            $this->templateDir = $this->systemDir.'../config/template/'.$this->get('template').'/';
            $this->template = new Ini( $settingFile);
        endif;

        $settingFile = $this->systemDir.'mustache/template/core/settings.ini';;
        if( is_string($this->template) && is_file($settingFile)):
            $this->template = new Ini( $settingFile);
            $this->templateDir =  $this->coreTemplateDir;
        endif;

        return $this;
    }

    public function setDirectories(): DomainConfigUtil
    {
        $this->rootDir = $this->systemDir.'../';
        $this->webDir = $this->systemDir.'../public_html/';
        $this->uploadDir = $this->systemDir.'../public_html/uploads/'.$this->get('account').'/';
        $this->domainDir = $this->systemDir.'../config/domain/';
        $this->accountDir = $this->systemDir.'../config/account/'.$this->get('account').'/';
        $this->cacheDir = $this->systemDir.'../cache/'.$this->get('account').'/';

        if (
            !is_dir($this->get('uploadDir'))
            && !mkdir($this->get('uploadDir'))
            && !is_dir($this->get('uploadDir'))
        ) :
            throw new \RuntimeException(sprintf('Directory "%s" was not created',
                $this->get('uploadDir')));
        endif;

        if (!is_dir($this->get('cacheDir'))
            && !mkdir($this->get('cacheDir'))
            && !is_dir($this->get('cacheDir'))
        ) :
            throw new \RuntimeException(sprintf('Directory "%s" was not created',
                $this->get('cacheDir')));
        endif;

        return $this;
    }

    public function getTemplate(): Ini
    {
        return $this->template;
    }

    public function getTemplateDir(): string
    {
        return $this->templateDir;
    }

    public function getCoreTemplateDir(): string
    {
        return $this->coreTemplateDir;
    }

    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    public function getWebDir(): string
    {
        return $this->webDir;
    }

    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }

    public function getDomainDir(): string
    {
        return $this->domainDir;
    }

    public function getAccountDir(): string
    {
        return $this->accountDir;
    }

    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    public function getSystemDir(): string
    {
        return $this->systemDir;
    }

    public function getAccount(): string
    {
        return $this->get('account');
    }

    public function getLanguageShort(): string
    {
        return $this->language->short;
    }

    public function getLanguageLocale(): string
    {
        return $this->language->locale;
    }

    public function getLanguageShortDefault(): ?string
    {
        return $this->languageShortDefault;
    }

    public function hasLanguage(): bool
    {
        return $this->language !== null;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function hasMovedTo(): bool
    {
        return !empty($this->movedTo);
    }

    public function getMovedTo(): ?string
    {
        return $this->movedTo;
    }

    public function getMongoDatabase(): string
    {
        return $this->mongo->database;
    }

    public function getMongoUri(): string
    {
        return !empty($this->mongo->ip) ? 'mongodb://'.$this->mongo->ip.'/' : 'mongodb://127.0.0.1/';
    }

    public function renderAdminListChildren(): bool
    {
        return $this->admin->renderAdminListChildren ?? true;
    }

    public function getBeanstalkHost(): string
    {
        return $this->beanstalk->host??'';
    }

    public function getBeanstalkPort(): string
    {
        return $this->beanstalk->database??'';
    }
}
