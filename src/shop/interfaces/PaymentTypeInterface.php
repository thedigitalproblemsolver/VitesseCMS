<?php

namespace VitesseCms\Shop\Interfaces;

use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Models\Payment;

/**
 * Class PaymentTypeInterface
 */
interface PaymentTypeInterface
{
    /**
     * @param Order $order
     * @param Payment $payment
     */
    public function doPayment(Order $order, Payment $payment): void;

    /**
     * @param int $transactionId
     * @param Payment $payment
     *
     * @return string
     */
    public function getTransactionState(int $transactionId, Payment $payment): string;

    /**
     * @param Order $order
     */
    public function prepareOrder(Order $order): void;

    /**
     * @return bool
     */
    public function isProcessRedirect(): bool;
}
