<?php

namespace VitesseCms\Shop\Helpers;

use VitesseCms\Block\Models\Block;
use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Shop\Enum\DiscountEnum;
use VitesseCms\Shop\Interfaces\ShippingTypeInterface;
use VitesseCms\Shop\Models\Cart;
use VitesseCms\Shop\Models\Discount;
use VitesseCms\Shop\Models\Shipping;
use VitesseCms\Shop\Utils\PriceUtil;
use MongoDB\BSON\ObjectID;
use Phalcon\Di;
use Phalcon\Http\Response\Cookies;
use Phalcon\Session\Adapter\Files as Session;

/**
 * Class CartHelper
 */
class CartHelper
{
    /**
     * @param AbstractCollection $item
     * @param string|null $chooseVariation
     *
     * @return string
     */
    public static function getMainImage(AbstractCollection $item, string $chooseVariation = null): string
    {
        if (
            $chooseVariation
            && \is_array($item->_('variations'))
            && \count($item->_('variations')) > 0

        ) :
            foreach ($item->_('variations') as $variation) :
                if ($variation['sku'] === $chooseVariation) :
                    if(\is_array($variation['image'])) :
                        return $variation['image'][0];
                    endif;
                    return $variation['image'];
                endif;
            endforeach;
        endif;

        return $item->_('image');
    }

    /**
     * @return Shipping
     */
    public static function getShipping()
    {
        $shippings = Shipping::findAll();
        if (\count($shippings) === 1) :
            /** @var Shipping $shipping */
            return $shippings[0];
        else :
            die('Shipping choice is not implemented');
        endif;
    }

    /**
     * @param AbstractCollection $item
     *
     * @return string
     */
    public static function getLogNameFromItem(AbstractCollection $item): string
    {
        $return = [$item->_('name')];
        if ($item->_('gender')) :
            $gender = Item::findById($item->_('gender'));
            $return[] = $gender->_('name');
        endif;
        if (Di::getDefault()->get('request')->getPost('variation', 'string')) :
            $return[] = Di::getDefault()->get('request')->getPost('variation', 'string');
        elseif ($item->_('variation')) :
            $return[] = $item->_('variation');
        endif;

        return implode(' ', $return);
    }

    /**
     * @param array $cartItems
     * @param ShippingTypeInterface $shippingType
     *
     * @return float
     */
    public static function calculateVat(array $cartItems, ShippingTypeInterface $shippingType): float
    {
        $total = self::calculateTotal($cartItems, $shippingType);
        $totalExVat = ( $total / 121 ) * 100;
        $vat = $total - $totalExVat;

        if($vat < 0 ) :
            return 0.00;
        endif;

        return $vat;
    }

    /**
     * @param array $cartItems
     * @param ShippingTypeInterface $shippingType
     *
     * @return float
     */
    public static function calculateTotal(array $cartItems, ShippingTypeInterface $shippingType): float
    {
        $total = $shippingType->calculateCartTotal($cartItems)+$cartItems['total'];
        $total = DiscountHelper::calculateTotal($total);

        return $total;
    }

    /**
     * @param Block $block
     * @param Cart $cart
     *
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function setBlockBasics(Block $block, Cart $cart): void
    {
        $cartItems = $cart->getItems(true);
        $shipping = self::getShipping();

        $block->set('cart', $cartItems);
        $block->set('shippingName', $shipping->_('name'));
        $block->set('shippingSubTotal', number_format($shipping->calculateCartAmount($cartItems), 2));
        $block->set('shippingTax', number_format($shipping->calculateCartVat($cartItems), 2));
        $block->set('shippingTotal', number_format($shipping->calculateCartTotal($cartItems), 2));
        $block->set('shippingTotalDisplay', PriceUtil::formatDisplay($block->_('shippingTotal')));
        $block->set('vatDisplay', PriceUtil::formatDisplay(self::calculateVat($cartItems, $shipping)));
        $block->set('totalDisplay', PriceUtil::formatDisplay(self::calculateTotal($cartItems, $shipping)));
        /** @var Discount $discount */
        $discount = DiscountHelper::getFromSession();
        if ($discount) :
            $block->set('discount', $discount);
            if (DiscountEnum::TARGET_ORDER === $discount->_('target')) :
                $block->set('totalDiscount', $discount->_('amount'));
                $block->set('totalDiscountDisplay', PriceUtil::formatDisplay($block->_('totalDiscount')));
            endif;
        endif;
        $block->set(
            'basketLegend',
            '<i class="fa fa-trash"></i> = %SHOP_REMOVE%&nbsp;&nbsp;&nbsp;<i class="fa fa-refresh"></i> = %SHOP_UPDATE_QUANTITY%'
        );
        $block->set('checkoutBar', false);
    }

    /**
     * @return Cart
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function getCartFromSession(): Cart
    {
        /** @var Session $session */
        $session = Di::getDefault()->get('session');
        if ($session->get('cartId') === null) :
            /** @var Cookies $cookies */
            $cookies = Di::getDefault()->get('cookies');
            if ($cookies->has('cartId') && MongoUtil::isObjectId($cookies->get('cartId')->getValue())) :
                $cartId = $cookies->get('cartId')->getValue();
            else :
                $cartId = new ObjectID();
                $cookies->set('cartId', $cartId, time() + 999 * 86400);
            endif;
            $session->set('cartId', $cartId);

            $cart = new Cart();
            $cart->set('published', true);
            $cart->setId($session->get('cartId'));
            $cart->save();
        else :
            $cart = Cart::findById($session->get('cartId'));

            if (!$cart) :
                /** @var Cookies $cookies */
                $cookies = Di::getDefault()->get('cookies');
                $cookies->get('cartId')->delete();
                $session->set('cartId', null);
                $cart = $this->getCartFromSession();
            endif;
        endif;
        $cart->products = (object)$cart->products;

        return $cart;
    }
}
