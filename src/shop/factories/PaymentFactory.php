<?php

namespace VitesseCms\Shop\Factories;

use VitesseCms\Shop\Models\Payment;

/**
 * Class PaymentFactory
 */
class PaymentFactory
{
    /**
     * @param string $name
     * @param string $paymentType
     * @param bool $published
     *
     * @return Payment
     */
    public static function create(
        string $name,
        string $paymentType,
        bool $published = false
    ): Payment {
        return (new Payment())
            ->set('name', $name, true)
            ->set('type', $paymentType)
            ->set('published', $published);
    }
}
