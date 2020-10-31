<?php declare(strict_types=1);

namespace VitesseCms\Core\Services;

use VitesseCms\Language\Helpers\LanguageHelper;
use Phalcon\Flash\Session;

class FlashService extends Session
{
    /**
     * @deprecated use seperate type function
     */
    public function _(
        string $translation,
        string $type = 'success',
        array $replace = []
    ): void {
        $this->$type(LanguageHelper::_($translation, $replace));
    }

    public function setWarning(string $translation,array $replace = []): void
    {
        $this->warning(LanguageHelper::_($translation, $replace));
    }

    public function setSucces(string $translation,array $replace = []): void
    {
        $this->success(LanguageHelper::_($translation, $replace));
    }

    public function setNotice(string $translation,array $replace = []): void
    {
        $this->notice(LanguageHelper::_($translation, $replace));
    }

    public function setError(string $translation,array $replace = []): void
    {
        $this->error(LanguageHelper::_($translation, $replace));
    }
}
