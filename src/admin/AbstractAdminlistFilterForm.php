<?php declare(strict_types=1);

namespace VitesseCms\Admin;

use VitesseCms\Core\Interfaces\AdminlistFormInterface;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use VitesseCms\Form\Models\Attributes;

abstract class AbstractAdminlistFilterForm implements AdminlistFormInterface
{
    abstract public static function getAdminlistForm(
        AbstractFormInterface $form,
        BaseObjectInterface $item
    ): void;

    public static function addNameField(AbstractFormInterface $form): void
    {
        $form->addText(
            '%CORE_NAME%',
            'filter[name.'.$form->configuration->getLanguageShort().']'
        );
    }

    public static function addPublishedField(AbstractFormInterface $form): void
    {
        $form->addDropdown(
            '%ADMIN_PUBLISHED_STATE%',
            'filter[published]',
            (new Attributes())->setOptions([
                ['value' => '', 'label' => '%ADMIN_NO_SELECTION%'],
                ['value' => 'true', 'label' => '%ADMIN_PUBLISHED%'],
                ['value' => 'false', 'label' => '%ADMIN_UNPUBLISHED%'],
            ])
        );
    }
}
