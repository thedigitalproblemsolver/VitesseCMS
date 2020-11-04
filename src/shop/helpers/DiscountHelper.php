<?php

namespace VitesseCms\Shop\Helpers;

use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Core\Utils\SessionUtil;
use VitesseCms\Shop\Interfaces\DiscountInterface;
use VitesseCms\Shop\Models\Discount;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Models\TaxRate;
use VitesseCms\Shop\Utils\PriceUtil;

/**
 * Class DiscountHelper
 */
class DiscountHelper extends AbstractInjectable
{
    /**
     * @param AbstractCollection $item
     * @param array $cartProduct
     * @deprecated should use service
     */
    public static function parseCartItem(AbstractCollection $item, array $cartProduct): void
    {
        $item->set('subTotalDiscountSale', $item->_('price_discountSale') * $cartProduct['quantity']);
        $item->set('subTotalDiscountSaleDisplay', PriceUtil::formatDisplay($item->_('subTotalDiscountSale')));
        $item->set('subTotalDiscount', $item->_('price_discount') * $cartProduct['quantity']);
        $item->set('price_discountDisplay', PriceUtil::formatDisplay($item->_('price_discountSale')));
    }

    /**
     * @param DiscountInterface $discount
     * @param float $price
     *
     * @return float
     * @deprecated should use service
     */
    public static function calculateFinalPrice(
        DiscountInterface $discount,
        float $price
    ): float {
        $discountPrice = $price;
        switch ($discount->_('type')) {
            case 'currency':
                $discountPrice = $price - $discount->_('amount');
                break;
            case 'percentage':
                $discountPrice = ($price / 100) * (100 - $discount->_('amount'));
                break;
        }

        if ($discountPrice > $price) :
            return 0.00;
        endif;

        return $discountPrice;
    }

    /**
     * @param AbstractCollection $item
     * @param DiscountInterface $discount
     */
    public function setPriceDisplay(AbstractCollection $item, DiscountInterface $discount): void
    {
        $item->set(
            'price_discountDisplay',
            PriceUtil::formatDisplay(
                DiscountHelper::calculateFinalPrice($discount, $item->_('price_sale'))
            )
        );
    }

    /**
     * @param AbstractCollection $item
     * @param DiscountInterface $discount
     */
    public function setPriceSale(AbstractCollection $item, DiscountInterface $discount): void
    {
        $item->set(
            'price_discountSale',
            DiscountHelper::calculateFinalPrice($discount, $item->_('price_sale'))
        );
        $item->set(
            'price_discountSaleDisplay',
            PriceUtil::formatDisplay($item->_('price_discountSale'))
        );
    }

    /**
     * @param AbstractCollection $item
     */
    public function setPrice(AbstractCollection $item): void
    {
        $taxrate = TaxRate::findById($item->_('taxrate'));
        $item->set(
            'price_discount',
            TaxrateHelper::calculateExVatPrice(
                $taxrate,
                $item->_('price_discountSale')
            )
        );
    }

    /**
     * @param null|string $target
     *
     * @return $this|array|bool|\Phalcon\Mvc\CollectionInterface
     * @deprecated should use service
     */
    public static function getFromSession(?string $target = null)
    {
        if (SessionUtil::get('discountId')) :
            if ($target):
                Discount::setFindValue('target', $target);
            endif;

            return Discount::findById(SessionUtil::get('discountId'));
        endif;

        return false;
    }

    /**
     * is replacement for getFromSession
     *
     * @param null|string $target
     *
     * @return Discount|null
     */
    public function loadFromSession(?string $target = null): ?Discount
    {
        if ($this->session->get('discountId')) :
            if ($target):
                Discount::setFindValue('target', $target);
            endif;

            return Discount::findById($this->session->get('discountId'));
        endif;

        return null;
    }

    /**
     * @param float $total
     *
     * @return float
     * @deprecated should use service
     */
    public static function calculateTotal(float $total)
    {
        /** @var Discount $discount */
        $discount = DiscountHelper::getFromSession();
        if ($discount) :
            $total = self::calculateFinalPrice($discount, $total);
        endif;

        if (0 > $total) :
            return 0.00;
        endif;

        return $total;
    }

    /**
     * @param Discount $discount
     *
     * @return int
     */
    public function getAmountOfUsedOrders(Discount $discount): int
    {
        if ($this->user->isLoggedIn()) :
            Order::setFindValue('discount.code', $discount->_('code'));
            Order::setFindValue('shopper.userId', (string)$this->user->getId());

            return Order::count();
        endif;

        return 0;
    }

    /**
     * @param Discount $discount
     *
     * @return bool
     */
    public function isValid(Discount $discount): bool
    {
        $isValid = true;
        if(
            !empty($discount->_('fromDate'))
            && date_create_from_format('Y-m-d',$discount->_('fromDate')) > new \DateTime()
        ) :
            $isValid = false;
        endif;

        if(
            $isValid
            && !empty($discount->_('tillDate'))
            && date_create_from_format('Y-m-d',$discount->_('tillDate')) < new \DateTime()
        ) :
            $isValid = false;
        endif;

        return $isValid;
    }

    public static function getTypes(string $rootDir) : array
    {
        $types = [];
        $files = DirectoryUtil::getFilelist($rootDir . 'src/shop/discountTypes/');
        foreach ($files as $path => $file) :
            $name = FileUtil::getName($file);
            $types[$name] = $name;
        endforeach;

        return $types;
    }
}
