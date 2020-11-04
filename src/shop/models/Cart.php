<?php

namespace VitesseCms\Shop\Models;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Factories\ObjectFactory;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Core\Utils\CookieUtil;
use VitesseCms\Core\Utils\SessionUtil;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Shop\Helpers\CartHelper;
use VitesseCms\Shop\Helpers\DiscountHelper;
use VitesseCms\Shop\Helpers\ProductHelper;
use VitesseCms\Shop\Utils\PriceUtil;
use MongoDB\BSON\ObjectID;
use Phalcon\Di;
use Phalcon\Http\Response\Cookies;
use Phalcon\Session\Adapter\Files as Session;
use stdClass;

class Cart extends AbstractCollection
{

    /**
     * @var BaseObjectInterface
     */
    public $products;

    /**
     * @var Int
     */
    public $productsTotal;

    public function onConstruct()
    {
        if (!\is_object($this->products)) :
            $this->products = ObjectFactory::create();
        endif;
    }

    /**
     * @return Cart
     * @throws \Phalcon\Mvc\Collection\Exception
     * @deprecated should be used as a service
     */
    public static function getCart(): Cart
    {
        /** @var Session $session */
        $session = Di::getDefault()->get('session');
        if ($session->get('cartId') === null) :
            /** @var Cookies $cookies */
            $cookies = Di::getDefault()->get('cookies');
            if ($cookies->get('cartId')->getValue()) :
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
                $cart = Cart::getCart();
            endif;
        endif;
        $cart->products = (object)$cart->products;

        return $cart;
    }

    public function addProduct(string $itemId, int $quantity, string $variation = null): void
    {
        $cartItemId = $itemId.$variation;

        $quantityToCheck = $quantity;
        if (isset($this->products->$cartItemId['quantity'])) :
            $quantityToCheck += $this->products->$cartItemId['quantity'];
        endif;

        $this->setProduct($cartItemId, $itemId, $variation);
        $this->products->$cartItemId['quantity'] = $this->checkStock($cartItemId, $quantityToCheck);
        $this->calculateTotalProducts();
    }

    public function removeProduct(string $cartItemId): void
    {
        if (isset($this->products->$cartItemId)) :
            unset($this->products->$cartItemId);
            $this->calculateTotalProducts();
        endif;
    }

    public function changeQuantity(string $cartItemId, Int $quantity): void
    {
        if (isset($this->products->$cartItemId)) :
            if ($quantity < 1) :
                $quantity = 1;
            endif;
            $this->products->$cartItemId['quantity'] = $this->checkStock($cartItemId, $quantity);
            $this->calculateTotalProducts();
        endif;
    }

    public function checkStock(string $cartItemId, int $quantity): int
    {
        $item = Item::findById($this->products->$cartItemId['itemId']);

        if ($item->_('outOfStock')) :
            $this->di->flash->_('SHOP_CART_OUT_OF_STOCK', 'error');

            return 0;
        endif;

        if (
            $item->_('stock')
            && $quantity > $item->_('stock')
        ) :
            $quantity = $item->_('stock');
            if (isset($this->products->$cartItemId['quantity'])) :
                $this->products->$cartItemId['quantity'] = 0;
            endif;
            $this->di->flash->_('SHOP_CART_NOT_ENOUGH_IN_STOCK', 'warning');
        endif;

        $variation = $this->products->$cartItemId['variation'];
        if (
            $variation
            && $item->_('variations')
        ) :
            foreach ($item->_('variations') as $itemVariation) :
                if ($itemVariation['sku'] === $variation) :
                    if ($quantity > $itemVariation['stock']) :
                        $quantity = $itemVariation['stock'];
                        if (isset($this->products->$cartItemId['quantity'])) :
                            $this->products->$cartItemId['quantity'] = 0;
                        endif;
                        $this->di->flash->_('SHOP_CART_NOT_ENOUGH_IN_STOCK', 'warning');
                    endif;
                endif;
            endforeach;
        endif;

        return $quantity;
    }

    public function changePacking(string $cartItemId, ?string $packingId = null): void
    {
        if (isset($this->products->$cartItemId)) :
            $this->products->$cartItemId['packing'] = $packingId;
        endif;
    }

    public function setProduct(string $cartItemId, string $itemId, string $variation = null): void
    {
        if (!isset($this->products->$cartItemId)) :
            $this->products->$cartItemId = [];
            $this->products->$cartItemId['quantity'] = 0;
            $this->products->$cartItemId['itemId'] = $itemId;
            $this->products->$cartItemId['variation'] = $variation;
            $this->products->$cartItemId['packing'] = null;
        endif;
    }

    /**
     * {@inheritdoc}
     */
    public function _(string $key, string $languageShort = null)
    {
        $return = parent::_($key);
        if (!$return) :
            if (isset($this->products->$key)) :
                $return = $this->products->$key;
            endif;
        endif;

        return $return;
    }

    public function getTotalText(): string
    {
        if ($this->_('productsTotal') === 1) :
            return '1 %SHOP_PRODUCT%';
        elseif ($this->_('productsTotal') > 1) :
            return $this->_('productsTotal').' %SHOP_PRODUCTS%';
        endif;

        return '%SHOP_CART_EMPTY%';
    }

    public function getItems(bool $parseBeforeMainContent = false): array
    {
        $return = ['products' => []];
        $totalSale = $subTotal = $subTotalSale = 0;

        foreach ($this->products as $cartItemId => $product) :
            if ($product !== null) :
                /** @var Item $item */
                $item = Item::findById($product['itemId']);

                $packing = null;
                if (MongoUtil::isObjectId($product['packing'])) :
                    $packing = Item::findById($product['packing']);
                    if ($packing) :
                        $price_sale = $item->_('price_sale') + (float)$packing->_('price_sale');
                        $item->set('price_sale', $price_sale);
                        $item->set('packingName', $packing->_('name'));
                    endif;
                endif;

                if ($parseBeforeMainContent) :
                    ItemHelper::parseBeforeMainContent($item);
                endif;

                $item->set('mainImage', CartHelper::getMainImage($item, $product['variation']));
                $item->set('quantity', $product['quantity']);
                $item->set('variation', $product['variation']);
                $item->set('packing', $product['packing']);
                $item->set('cartItemId', $cartItemId);
                $item->set('subTotalSale', $item->_('price_sale') * $product['quantity']);
                $item->set('subTotalSaleDisplay', PriceUtil::formatDisplay($item->_('subTotalSale')));
                $item->set('subTotal', $item->_('price') * $product['quantity']);

                if ($item->_('price_discount') > 0) :
                    DiscountHelper::parseCartItem($item, $product);
                    $totalSale += $item->_('subTotalDiscountSale');
                    $subTotalSale += $item->_('subTotalDiscountSale');
                    $subTotal += $item->_('subTotalDiscount');
                else :
                    $totalSale += $item->_('subTotalSale');
                    $subTotalSale += $item->_('subTotalSale');
                    $subTotal += $item->_('subTotal');
                endif;

                $return['products'][] = $item;
            endif;
        endforeach;

        $return['total'] = $totalSale;
        $return['totalDisplay'] = PriceUtil::formatDisplay($totalSale);
        $return['subTotalSale'] = $subTotalSale;
        $return['subTotalSaleDisplay'] = PriceUtil::formatDisplay($subTotalSale);
        $return['subTotal'] = $subTotal;

        $return['vat'] = $subTotalSale - $subTotal;
        $return['vatDisplay'] = PriceUtil::formatDisplay($return['vat']);

        return $return;
    }

    public function getItem(string $cartItemId): AbstractCollection
    {
        $products = $this->products;
        $product = $products->$cartItemId;

        $item = Item::findById($product['itemId']);
        $item->set('quantity', $product['quantity']);
        $item->set('variation', $product['variation']);
        $item->set('cartItemId', $cartItemId);
        $item->set('slug', $item->_('slug'));
        $item->set('subTotal', $item->_('price') * $product['quantity']);

        return $item;
    }

    public function clear(): void
    {
        $this->products = new stdClass();
        $this->productsTotal = 0;
        CookieUtil::delete('cartId');
        SessionUtil::remove('cartId');
        SessionUtil::remove('discountId');
    }

    public function calculateTotalProducts(): void
    {
        $total = 0;
        foreach ($this->products as $cartItemId => $product) :
            $total += $product['quantity'];
        endforeach;
        $this->productsTotal = $total;
    }

    public function hasProducts(): bool
    {
        return $this->productsTotal > 0;
    }
}
