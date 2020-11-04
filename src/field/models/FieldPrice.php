<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Field\AbstractField;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Shop\Enum\DiscountEnum;
use VitesseCms\Shop\Helpers\ProductHelper;
use VitesseCms\Shop\Models\Discount;
use VitesseCms\Shop\Utils\PriceUtil;
use Phalcon\Di;

class FieldPrice extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {

        $this->setOption('step', 'any');
        $this->setOption('min', '0');
        $options = $this->getOptions();
        $options['readonly'] = true;
        $form->_(
            'text',
            $datafield->getNameField().' - ex. VAT',
            $datafield->getCallingName(),
            $options
        )->_(
            'number',
            $datafield->getNameField().' - purchase',
            $datafield->getCallingName().'_purchase',
            $this->getOptions()
        )->_(
            'number',
            $datafield->getNameField().' - inc. VAT',
            $datafield->getCallingName().'_sale',
            $this->getOptions()
        );

        Discount::setFindValue('target', ['$in' => [DiscountEnum::TARGET_PRODUCT, DiscountEnum::TARGET_FREE_SHIPPING]]);
        $form->_(
            'select',
            'Discount',
            'discount',
            [
                'options'    => Discount::class,
                'multiple'   => true,
                'inputClass' => 'select2',
            ]
        );
    }

    public static function beforeMaincontent(Item $item, Datafield $datafield): void
    {
        $item->set(
            $datafield->getCallingName().'_saleDisplay',
            PriceUtil::formatDisplay(
                (float)$item->_($datafield->getCallingName().'_sale')
            )
        );

        $item->set(
            $datafield->getCallingName().'Display',
            PriceUtil::formatDisplay(
                (float)$item->_($datafield->getCallingName())
            )
        );

        Di::getDefault()->get('eventsManager')->fire('discount:prepareItem', $item);
    }
}
