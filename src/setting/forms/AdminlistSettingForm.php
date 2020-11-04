<?php declare(strict_types=1);

namespace VitesseCms\Setting\Forms;

use VitesseCms\Admin\AbstractAdminlistFilterForm;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Setting\Models\Setting;

class AdminlistSettingForm extends AbstractAdminlistFilterForm
{
    public static function getAdminlistForm(
        AbstractFormInterface $form,
        BaseObjectInterface $item
    ): void {
        self::addNameField($form);
        $form->addText(
            '%ADMIN_VALUE%',
            'filter[value.'.$form->configuration->getLanguageShort().']'
        )->addDropdown(
            '%ADMIN_TYPE%',
            'filter[type]',
            (new Attributes())->setOptions((new Setting())->getTypes())
        );
        self::addPublishedField($form);
    }
}
