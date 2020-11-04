<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Field\AbstractField;
use VitesseCms\Form\Interfaces\AbstractFormInterface;

class FieldTexteditor extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {
        $this->setOption('inputClass', 'editor');
        $form->_(
            'textarea',
            $datafield->getNameField(),
            $datafield->getCallingName(),
            $this->getOptions()
        );
    }

    public function renderFilter(
        AbstractFormInterface $filter,
        Datafield $datafield,
        AbstractCollection $data = null
    ): void {
        $filter->_(
            'hidden',
            null,
            'filter[textFields]['.uniqid('',false).']',
            [
                'value' => $datafield->getCallingName()
            ]
        );
    }

    public function getSearchValue(
        AbstractCollection $item,
        string $languageShort, Datafield $datafield
    ) {
        $result = $item->_(
            $datafield->getCallingName(),
            $languageShort
        );
        if(is_string($result)) :
            return trim(strip_tags($result));
        endif;

        return '';
    }
}
