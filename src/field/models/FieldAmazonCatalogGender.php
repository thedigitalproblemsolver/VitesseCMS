<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Core\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Field\AbstractField;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class FieldAmazonCatalogGender extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {
        $form->addDropdown(
            'Amazon gender',
            'AmazonCatalogGender',
            (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions([
                'Women' => 'Women',
                'Men'   => 'Men',
            ]))
        );
    }
}
