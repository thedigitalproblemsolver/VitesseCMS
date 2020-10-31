<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Field\AbstractField;
use VitesseCms\Form\Interfaces\AbstractFormInterface;

class FieldCheckbox extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {
        if (
            is_object($data)
            && $data->_($datafield->getCallingName())
        ) :
            $this->setOption('checked', true);
        endif;

        $form->_(
            'checkbox',
            $datafield->getNameField(),
            $datafield->getCallingName(),
            array_merge($this->getOptions(), ['value' => true])
        );
    }

    public function renderFilter(AbstractFormInterface $filter, Datafield $datafield): void
    {
        $fieldName = $this->getFieldname($datafield);
        $this->setOption('value', true);
        $this->setOption('template', 'checkbox_toggle');

        $filter->_(
            'checkbox',
            $datafield->getNameField(),
            $fieldName,
            $this->getOptions()
        );
    }
}
