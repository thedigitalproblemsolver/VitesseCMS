<?php declare(strict_types=1);

namespace VitesseCms\Language\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Models\Attributes;

class LanguageForm extends AbstractForm
{

    public function initialize()
    {
        $this->addText('%CORE_NAME%', 'name', (new Attributes())->setRequired(true))
            ->addText('%ADMIN_LANGUAGE_LOCAL_NAME%', 'nativeName', (new Attributes())->setRequired(true))
            ->addText('%ADMIN_LANGUAGE_ISO_FOUR_CODE%', 'locale', (new Attributes())->setRequired(true))
            ->addText('%ADMIN_LANGUAGE_ISO_TWO_CODE%', 'short', (new Attributes())->setRequired(true))
            ->addText('%ADMIN_LANGUAGE_DOMAIN%', 'domain', (new Attributes())->setRequired(true))
            ->addText('%ADMIN_LANGUAGE_FLAGICON_CSSCLASS%', 'flagClass', (new Attributes())->setRequired(true))
            ->addSubmitButton('%CORE_SAVE%');
    }
}
