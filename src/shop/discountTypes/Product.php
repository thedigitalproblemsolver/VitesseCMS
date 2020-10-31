<?php declare(strict_types=1);

namespace VitesseCms\Shop\DiscountTypes;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Shop\AbstractDiscountType;

class Product extends AbstractDiscountType
{
    public function buildAdminForm(AbstractForm $form): void
    {
        $form->addNumber('Amount', 'amount', (new Attributes())->setRequired(true))
            ->addDropdown(
                'Type',
                'type',
                (new Attributes())
                    ->setRequired(true)
                    ->setOptions(
                        ElementHelper::arrayToSelectOptions([
                            'currency' => $this->setting->get('SHOP_CURRENCY_ISO'),
                            'percentage' => 'Percentage'
                        ])
                    )
            )
        ;
    }
}
