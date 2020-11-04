<?php

namespace VitesseCms\Field\Forms;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Field\Interfaces\AdminformInterface;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;

/**
 * Class AdminFieldImage
 */
class AdminFieldImage implements AdminformInterface
{
    /**
     * (@inheritdoc)
     */
    public static function buildForm(AbstractForm $form, AbstractCollection $item): void
    {
        $form->_(
            'text',
            'Width',
            'width'
        );
        $form->_(
            'text',
            'Height',
            'height'
        );

        $form->_(
            'select',
            'Toegestaande bestandstype',
            'allowedFiletypeGroups',
            [
                'multiple' => true,
                'options' => ElementHelper::arrayToSelectOptions(FileUtil::getFiletypeGroups())
            ]
        );
    }
}
