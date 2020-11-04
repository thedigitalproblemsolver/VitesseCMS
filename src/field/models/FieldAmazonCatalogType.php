<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Core\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Field\AbstractField;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;

class FieldAmazonCatalogType extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {
        $form->addDropdown(
            'Amazon gender',
            'AmazonCatalogType',
            (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions([
                'sweater' => 'sweater',
                'shirt'   => 'shirt',
            ]))
        );
    }
}
