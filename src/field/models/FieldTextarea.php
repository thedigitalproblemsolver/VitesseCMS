<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Core\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Field\AbstractField;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Interfaces\AbstractFormInterface;

class FieldTextarea extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {
        $form->_(
            'textarea',
            $datafield->getNameField(),
            $datafield->getCallingName(),
            $this->getOptions()
        );
    }

    public function getSearchValue(AbstractCollection $item, string $languageShort, Datafield $datafield)
    {
        return trim(strip_tags($item->_($datafield->getCallingName(), $languageShort)));
    }

    public function renderFilter(
        AbstractFormInterface $filter,
        Datafield $datafield,
        AbstractCollection $data = null
    ): void {
        $filter->_(
            'hidden',
            null,
            'filter[textFields]['.uniqid('', true).']',
            [
                'value' => $datafield->_('calling_name'),
            ]
        );
    }
}
