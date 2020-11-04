<?php

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Core\AbstractController;
use VitesseCms\Shop\Helpers\OrderHelper;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Models\OrderState;
use VitesseCms\Shop\Models\Payment;

/**
 * Class PaymentController
 */
class PaymentController extends AbstractController
{
    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function processAction(): void
    {
        $doRedirect = true;

        if($this->dispatcher->getParam(0) !== null ) :
            Order::setFindPublished(false);
            /** @var Order $order */
            $order = Order::findById($this->dispatcher->getParam(0));
            if($order) :
                $paymentType = $this->processPayment($order);
                $doRedirect = $paymentType->isProcessRedirect();
            else :
                $this->flash->_('SHOP_ORDER_NOT_FOUND', 'error');
            endif;
        endif;

        if($doRedirect) :
            $this->redirect($this->shop->checkout->getStep(5)->_('slug'));
        else :
            $this->disableView();
        endif;
    }

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function redirectAction(): void
    {
        if($this->dispatcher->getParam(0) !== null ) :
            Order::setFindPublished(false);
            /** @var Order $order */
            $order = Order::findById($this->dispatcher->getParam(0));
            if($order) :
                $this->processPayment($order);
                $this->redirect($this->shop->checkout->getStep(5)->_('slug').'?v='.time());
            else :
                $this->flash->_('SHOP_ORDER_NOT_FOUND', 'error');
                $this->redirect('/');
            endif;
        endif;
    }

    /**
     * process payment
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function cancelAction(): void
    {
        $hasErrors = true;

        if($this->dispatcher->getParam(0) !== null ) :
            Order::setFindPublished(false);
            $order = Order::findById($this->dispatcher->getParam(0));
            if($order) :
                OrderState::setFindValue('short', 'X');
                $order->set('orderState', OrderState::findFirst());
                $order->set('published', false);
                $order->save();
                $hasErrors = false;

                $this->flash->_('SHOP_ORDER_CANCELLED');
            endif;
        endif;

        if($hasErrors) :
            $this->flash->_('SHOP_ORDER_NOT_FOUND','error');
        endif;

        $this->redirect($this->shop->checkout->getStep()->_('slug'));
    }

    /**
     * @param Order $order
     *
     * @return Payment
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    protected function processPayment(Order $order): Payment
    {
        $this->session->set('currentOrderId', $order->getId());
        Payment::setFindPublished(false);
        /** @var Payment $paymentType */
        $paymentType = Payment::findById($order->_('paymentType')['_id']);
        $paymentType->prepareOrder($order);
        $orderState = $paymentType->getTransactionState(
            $order->_('paymentType')['transactionId'],
            (string) $order->_('orderState')['_id']
        );
        OrderHelper::setOrderState($order, $orderState);
        $order->set('published', true);
        $order->save();

        $shopOrder = $this->view->renderTemplate(
            'order',
            'partials/shop',
            [
                'orderItem' => $order,
                'shopOrderId' => $order->_('orderId')
            ]
        );
        $this->view->set('shopOrder', $shopOrder);

        if($orderState->_('messageType')) :
            $messageType = $orderState->_('messageType');
            $this->flash->$messageType($orderState->_('messageText'));
        endif;

        $this->log->write(
            $order->getId(),
            Order::class,
            'Order '.$order->_('orderId').' processed with orderstate '.$orderState->_('calling_name')
        );

        return $paymentType;
    }
}
