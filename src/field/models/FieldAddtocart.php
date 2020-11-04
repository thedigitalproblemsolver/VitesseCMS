<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Field\AbstractField;

class FieldAddtocart extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {
        $this->setOption('checked', 'checked');
        $this->setOption('readonly', 'readonly');
        $this->setOption('value', true);
        $form->_(
            'checkbox',
            $datafield->getNameField(),
            $datafield->getCallingName(),
            $this->getOptions()
        );
    }
}
