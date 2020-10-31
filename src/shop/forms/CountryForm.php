<?php declare(strict_types=1);

namespace VitesseCms\Shop\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Models\Attributes;

class CountryForm extends AbstractForm
{
    public function initialize(): void
    {
        $this->addText(
                '%CORE_NAME%',
                'name',
                (new Attributes())->setRequired(true)->setMultilang(true)
            )
            ->addText('%ADMIN_COUNTRY_TWO_CODE%', 'short', (new Attributes())->setRequired(true))
            ->addText('%ADMIN_COUNTRY_THREE_CODE%', 'shortThree', (new Attributes())->setRequired(true))
            ->addSubmitButton('%CORE_SAVE%')
        ;
    }
}
