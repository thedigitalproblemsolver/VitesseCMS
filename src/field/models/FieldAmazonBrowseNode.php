<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Field\Enums\FieldAmazonEnum;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Field\AbstractField;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class FieldAmazonBrowseNode extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {
        $attributes = new Attributes();
        if($data !== null) {
            $attributes->setOptions(ElementHelper::arrayToSelectOptions(
                FieldAmazonEnum::nodes,
                [$data->_('AmazonBrowseNode')])
            );
        }

        $form->addDropdown(
            'Amazon Browse Node',
            'AmazonBrowseNode',
            $attributes
        );
    }
}
