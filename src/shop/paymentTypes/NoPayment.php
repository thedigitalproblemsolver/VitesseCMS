<?php declare(strict_types=1);

namespace VitesseCms\Shop\PaymentTypes;

use VitesseCms\Shop\AbstractPaymentType;
use VitesseCms\Shop\Enum\PaymentEnum;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Models\Payment;

class NoPayment extends AbstractPaymentType
{
    public function doPayment(Order $order, Payment $payment): void
    {
        $this->log->write(
            $order->getId(),
            Order::class,
            'Order '.$order->_('orderId').' user with no payment redirected to payment process'
        );

        header('Location: ' . $this->url->getBaseUri() . 'shop/payment/process/' . $order->getId());
        die();
    }

    public function getTransactionState(int $transactionId, Payment $payment): string
    {
        return PaymentEnum::PAID;
    }

    public function isProcessRedirect(): bool
    {
        return true;
    }
}
