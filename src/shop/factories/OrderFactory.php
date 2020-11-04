<?php declare(strict_types=1);

namespace VitesseCms\Shop\Factories;

use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Models\OrderState;

class OrderFactory
{
    public static function create(BaseObjectInterface $bindData = null) : Order
    {
        $order = new Order();
        $order->setIpAddress($_SERVER['REMOTE_ADDR']);
        $order->setProperty($_SERVER['HTTP_HOST']);
        $order->setOrderState(OrderState::findFirst());

        if($bindData) :
            if(!empty($bindData->_('ipAddress'))) :
                $order->setIpAddress($bindData->_('ipAddress'));
            endif;
            $cartItems = $bindData->_('cart')->getItems();

            $order->setOrderId($bindData->_('orderId'));
            $order->setShopper($bindData->_('shopper'));
            $order->setShiptoAddress($bindData->_('shiptoAddress'));
            $order->setItems($cartItems);
            $order->setShippingType($bindData->_('shippingType'));
            $order->setShippingAmount($bindData->_('shippingAmount'));
            $order->setShippingTax($bindData->_('shippingTax'));
        endif;

        return $order;
    }
}
