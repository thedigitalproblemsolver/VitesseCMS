<?php declare(strict_types=1);

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Block\Helpers\BlockHelper;
use VitesseCms\Core\AbstractController;
use VitesseCms\Block\Models\Block;
use VitesseCms\Content\Models\Item;
use VitesseCms\Language\Helpers\LanguageHelper;
use VitesseCms\Shop\Helpers\CartHelper;
use MongoDB\BSON\ObjectID;

class CartController extends AbstractController
{
    /**
     * indexAction
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function indexAction(): void
    {
        if ($this->request->get('embedded', 'int', 0)) :
            $block = Block::findById($this->setting->get('SHOP_BLOCK_CARTLARGE'));
            /** @var Block $block */
            $this->view->setVar('content', BlockHelper::render(
                $block,
                $this->view,
                $this->cache
            ));

            $this->prepareView();
        else :
            Item::setFindValue('datagroup', $this->setting->get('SHOP_DATAGROUP_CHECKOUT'));
            $cartPage = Item::findFirst();
            $this->redirect(str_replace('/', '', $cartPage->_('slug')));
        endif;
    }

    /**
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function addtocartAction(): void
    {
        if ($this->request->getPost('itemId')) :
            $cart = $this->shop->cart->getCartFromSession();
            $cart->addProduct(
                $this->request->getPost('itemId'),
                (int) $this->request->getPost('quantity', 'int'),
                $this->request->getPost('variation', 'string')
            );
            $cart->save();

            $logMessage = CartHelper::getLogNameFromItem(
                Item::findById($this->request->getPost('itemId'))
            );
            $logMessage .= ' x '.$this->request->getPost('quantity', 'int').' added to cart';
            $this->log->write(
                new ObjectID($this->request->getPost('itemId')),
                Item::class,
                $logMessage
            );

            $this->flash->_('SHOP_CART_PRODUCT_ADDED');

            $successFunction = "ui.fill('.shopcart-content','".$cart->getTotalText()."')";
            if ($this->setting->has('FACEBOOK_PIXEL_ID')) :
                $successFunction .= ";facebook.addToCart('".$this->request->getPost('itemId')."')";
            endif;
            $this->redirect(
                null,
                ['successFunction' => $this->language->parsePlaceholders($successFunction)]
            );
        else :
            $this->redirect();
        endif;
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function removeitemAction(): void
    {
        if ($this->request->getPost('cartItemId')) :
            $cart = $this->shop->cart->getCartFromSession();
            $item = $cart->getItem($this->request->getPost('cartItemId'));

            $cart->removeProduct($this->request->getPost('cartItemId'));
            $cart->save();

            $logMessage = CartHelper::getLogNameFromItem($item).' x '.$item->_('quantity').' removed from cart';
            $this->log->write($item->getId(), Item::class, $logMessage);

            $this->flash->_('SHOP_CART_PRODUCT_REMOVED');
            $this->redirect(null,
                ['successFunction' => "ui.remove('product-row-".$this->request->getPost('cartItemId')."');ui.fill('.shopcart-content','".$this->language->parsePlaceholders($cart->getTotalText())."')"]);
        else :
            $this->redirect();
        endif;
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function changequantityAction(): void
    {
        if ($this->request->getPost('cartItemId')) :
            $cart = $this->shop->cart->getCartFromSession();
            $item = $cart->getItem($this->request->getPost('cartItemId'));

            $cart->changeQuantity(
                $this->request->getPost('cartItemId'),
                (int) $this->request->getPost('quantity', 'int')
            );
            $cart->save();

            $logMessage = CartHelper::getLogNameFromItem($item);
            $logMessage .= ' cart-quantity changed from '.
                $item->_('quantity').
                ' to '.
                $this->request->getPost('quantity', 'int');
            $this->log->write($item->getId(), Item::class, $logMessage);

            $this->flash->_('SHOP_CART_QUANTITY_ADJUSTED');
            $this->redirect(null,
                ['successFunction' => "ui.fill('.shopcart-content','".$this->language->parsePlaceholders($cart->getTotalText())."');refresh(false)"]);
        else :
            $this->redirect();
        endif;
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function getcarttextAction(): void
    {
        $this->cache->setNoCacheHeaders();
        $this->prepareJson([
            'cartText' => $this->language->parsePlaceholders(
                $this->shop->cart->getCartFromSession()->getTotalText()
            ),
        ]);
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function setPackingForProductAction(): void
    {
        if ($this->request->getPost('cartItemId')) :
            $cartItemId = $this->request->getPost('cartItemId');
            $cart = $this->shop->cart->getCartFromSession();
            $item = $cart->getItem($cartItemId);
            $cart->changePacking($cartItemId, $this->request->getPost('packing', 'string'));
            $cart->save();

            $this->log->write(
                $item->getId(),
                Item::class,
                CartHelper::getLogNameFromItem($item).' cart-packing changed'
            );

            $this->flash->_('SHOP_PACKING_TYPE_ADJUSTED');
        endif;
        $this->redirect();
    }
}
