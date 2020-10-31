<?php declare(strict_types=1);

namespace VitesseCms\Shop\DiscountTypes;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Media\Enums\AssetsEnum;
use VitesseCms\Shop\AbstractDiscountType;
use VitesseCms\Shop\Models\Country;

class FreeShipping extends AbstractDiscountType
{
    public function buildAdminForm(AbstractForm $form): void
    {
        Country::setFindLimit(999);
        $form->addDropdown(
            'Countries',
            'countries',
            (new Attributes())
                ->setMultilang(true)
                ->setMultiple(true)
                ->setOptions(ElementHelper::arrayToSelectOptions(['all' => 'All']) + ElementHelper::arrayToSelectOptions(Country::findAll()))
                ->setInputClass(AssetsEnum::SELECT2)
        );
    }
}