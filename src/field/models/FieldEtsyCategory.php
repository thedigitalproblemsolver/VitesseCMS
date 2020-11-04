<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Core\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Field\AbstractField;
use VitesseCms\Field\Enums\FieldSizeAndColorEnum;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Models\Attributes;

class FieldEtsyCategory extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {
        $form->addText('Etsy CategoryId', 'etsyCategoryId')
            ->addText('Etsy TaxanomyId', 'etsyTaxonomyId')
            ->addText('Etsy Tags', 'etsyTags', (new Attributes())->setMultilang(true))
            ->addText('Etsy SizeId', 'etsySizeId');

        foreach (FieldSizeAndColorEnum::sizes as $size => $sizeName) :
            $form->addText($size.' to Etsy Size', 'etsySizeMapper['.$size.']');
        endforeach;
    }
}
