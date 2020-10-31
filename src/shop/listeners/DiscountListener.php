<?php

namespace VitesseCms\Shop\Listeners;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Shop\Enum\DiscountEnum;
use VitesseCms\Shop\Helpers\DiscountHelper;
use VitesseCms\Shop\Models\Country;
use VitesseCms\Shop\Models\Discount;
use Phalcon\Events\Event;

/**
 * Class DiscountListener
 */
class DiscountListener extends AbstractInjectable
{
    /**
     * @param Event $event
     */
    public function prepareItem(Event $event, Item $item): void
    {
        $item->set('price_discount', false);
        $item->set('price_discountDisplay', false);
        $item->set('price_discountSale', false);
        $item->set('price_discountSaleDisplay', false);

        if (\is_array($item->_('discount'))) :
            $codes = [null, ''];
            /** @var Discount $discount */
            $discount = DiscountHelper::getFromSession(DiscountEnum::TARGET_PRODUCT);
            if ($discount) :
                $codes[] = $discount->_('code');
            endif;

            foreach ($item->_('discount') as $discountId) :
                Discount::setFindValue(
                    'target',
                    ['$in' => [DiscountEnum::TARGET_PRODUCT, DiscountEnum::TARGET_FREE_SHIPPING]]
                );
                Discount::setFindValue('code', ['$in' => $codes]);
                $discount = Discount::findById($discountId);
                if ($discount && $this->shop->discount->isValid($discount)) :
                    switch ($discount->_('target')) :
                        case DiscountEnum::TARGET_PRODUCT:
                            $this->shop->discount->setPriceSale($item, $discount);
                            $this->shop->discount->setPrice($item);
                            $this->shop->discount->setPriceDisplay($item, $discount);
                            break;
                        case DiscountEnum::TARGET_FREE_SHIPPING:
                            $discountCountries = [];
                            if(\is_array($discount->_('countries'))) :
                                foreach ($discount->_('countries') as $countryId) :
                                    $country = Country::findById($countryId);
                                    if ($country) :
                                        $discountCountries[] = $country;
                                    endif;
                                endforeach;
                            endif;
                            $item->set('price_discount_freeShipping_countries', $discountCountries);
                            if(\count($discountCountries)) :
                                $item->set('price_discount_freeShipping', true);
                            endif;
                            break;
                    endswitch;
                endif;
            endforeach;
        endif;
    }

    /**
     * @param Event $event
     */
    public function onLoginSuccess(Event $event): void
    {
        $discount = $this->shop->discount->loadFromSession();
        if ($discount) :
            if ($this->shop->discount->getAmountOfUsedOrders($discount)) :
                $this->session->remove('discountId');
                $this->flash->_('SHOP_DISCOUNT_CODE_ALREADY_USED', 'error');
            endif;
            if ($this->shop->discount->isValid($discount)) :
                $this->session->remove('discountId');
                $this->flash->_('SHOP_DISCOUNT_CODE_UNKNOWN_OR_USED', 'error');
            endif;
        endif;
    }
}
