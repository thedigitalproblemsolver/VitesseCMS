<?php declare(strict_types=1);

namespace VitesseCms\Shop\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Shop\AbstractDiscountType;
use VitesseCms\Shop\Helpers\DiscountHelper;
use VitesseCms\Shop\Models\Discount;

class DiscountForm extends AbstractForm
{
    public function initialize(Discount $item = null): void
    {
        $this->addText(
            '%CORE_NAME%',
            'name',
            (new Attributes())->setRequired(true)->setMultilang(true)
        )->addText('Code', 'code');

        if ($item->getTarget() === null) :
            $this->addDropdown(
                'Discount type',
                'target',
                (new Attributes())->setRequired(true)->setOptions(ElementHelper::arrayToSelectOptions(
                    DiscountHelper::getTypes($this->configuration->getRootDir())
                ))
            );
        else :
            $object = $item->getTargetClass();
            /** @var AbstractDiscountType $discountType */
            $discountType = new $object();
            $discountType->buildAdminForm($this);
        endif;

        $this->addDate('From', 'fromDate')
            ->addDate('Till', 'tillDate')
            ->addSubmitButton('%CORE_SAVE%');
    }
}
