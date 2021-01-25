<?php declare(strict_types=1);

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Core\AbstractController;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Shop\Enum\DiscountEnum;
use VitesseCms\Shop\Enum\OrderStateEnum;
use VitesseCms\Shop\Enum\ShipToAddressEnum;
use VitesseCms\Shop\Factories\OrderFactory;
use VitesseCms\Shop\Factories\ShiptoAddressFactory;
use VitesseCms\Shop\Helpers\DiscountHelper;
use VitesseCms\Shop\Helpers\OrderHelper;
use VitesseCms\Shop\Interfaces\RepositoriesInterface;
use VitesseCms\Shop\Models\Cart;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Utils\PriceUtil;

class OrderController extends AbstractController implements RepositoriesInterface
{
    public function saveAndPayAction(): void
    {
        if ($this->user->isLoggedIn()):
            $cart = Cart::getCart();
            $shopper = $this->repositories->shopper->getByUserid((string)$this->user->getId());
            if($shopper === null):
                die('Shopper is unknown');
            endif;

            if (
                $this->session->get('shiptoAddress') !== ShipToAddressEnum::TYPE_INVOICE
                && MongoUtil::isObjectId($this->session->get('shiptoAddress'))
            ) :
                $shiptoAddresses = ShiptoAddressFactory::createFromItem(
                    $this->repositories->item->getById(
                        $this->session->get('shiptoAddress')
                    ),
                    $this->repositories->datagroup->getById(
                        $this->setting->get('SHOP_DATAGROUP_SHOPPERSHIPTO')
                    ),
                    $this->repositories->datafield
                );
            else :
                $shiptoAddresses = ShiptoAddressFactory::createFromUser(
                    $this->user,
                    $this->repositories->datagroup->getById(
                        $this->setting->get('SHOP_DATAGROUP_SHOPPERSHIPTO')
                    ),
                    $this->repositories->datafield
                );
            endif;

            $shippingTypes = $this->repositories->shippingType->findAll();
            if ($shippingTypes->count() === 1) :
                $shippingType = $shippingTypes->current();
            else :
                die('Shipping choice is not implemented');
            endif;

            $paymentTypes = $this->repositories->payment->findAll();
            if ($paymentTypes->count() === 1) :
                $paymentType = $paymentTypes->current();
            else :
                die('Payment choice is not implemented');
            endif;

            $order = OrderFactory::create();
            $order->setOrderId(OrderHelper::getNewOrdernumber());
            $order->setShopper($shopper);
            $order->setShiptoAddress($shiptoAddresses);
            $order->setItems($cart->getItems(true));
            $order->setIpAddress( $this->request->getClientAddress());
            $order->setShippingType($shippingType);
            $order->setShippingAmount($shippingType->calculateOrderAmount($order));
            $order->setShippingAmountDisplay( PriceUtil::formatDisplay(
                (float) $order->_('shippingAmount'))
            );
            $order->setShippingTax($shippingType->calculateOrderVat($order));
            $order->setShippingTaxDisplay(PriceUtil::formatDisplay(
                (float) $order->_('shippingTax'))
            );
            $order->setPaymentType($paymentType);
            $order->setAffiliateId($this->cookies->get('affiliate-source'));
            $order->setCurrency($this->setting->get('SHOP_CURRENCY_BASE'));
            $order->setOrderMessage($this->session->get('orderMessage'));
            OrderHelper::calculateTotals($order);
            if($this->session->has('discountId')) :
                $discount = $this->repositories->discount->getById($this->session->get('discountId'));
                if($discount !== null) :
                    $order->setDiscount($discount);
                    if (DiscountEnum::TARGET_ORDER === $discount->getTarget()) :
                        $order->setTotalDiscount($discount->getAmount());
                        $order->setTotalDiscountDisplay( PriceUtil::formatDisplay(
                            (float) $order->_('totalDiscount')
                        ));
                        $order->setTotal(DiscountHelper::calculateTotal(
                            (float) $order->_('total'))
                        );
                        $order->setTotalDisplay(PriceUtil::formatDisplay(
                            (float) $order->_('total'))
                        );
                    endif;
                endif;
            endif;
            $order->setPublished(true);
            OrderHelper::setOrderState(
                $order,
                $this->repositories->orderState->getByState(OrderStateEnum::CONFIRMED)
            );
            $order->save();

            $this->cookies->delete('affiliate-source');

            $this->log->write(
                $order->getId(),
                Order::class,
                'Order '.$order->_('orderId').' created'
            );

            $paymentType->doPayment($order, $paymentType);
            die('order saved');
        endif;

        $this->redirect($this->url->getBaseUri());
    }

    public function viewOrderAction(): void
    {
        $displayError = true;
        if(
            $this->user->isLoggedIn()
            && $this->dispatcher->getParam(0)
        ) :
            $order = $this->repositories->order->getViewOrder(
                $this->dispatcher->getParam(0),
                $this->user->getId()
            );
            if($order !== null) :
                $this->view->setVar('content',$this->view->renderTemplate(
                    'order',
                    'partials/shop',
                    [ 'orderItem' => $order]
                ));
                $displayError = false;
                $this->prepareView();
            endif;
        endif;

        if($displayError) :
            $this->flash->setError('SHOP_ORDER_NOT_DISPLAYED');
            $this->redirect($this->url->getBaseUri());
        endif;
    }

    public function storeOrderMessageAction(): void {
        if($this->request->isAjax()) {
            $this->session->set('orderMessage', $this->request->get('orderMessage'));
        }
        $this->disableView();
    }
}
