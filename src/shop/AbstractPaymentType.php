<?php

namespace VitesseCms\Shop;

use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Core\Interfaces\ExtendAdminFormInterface;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Shop\Interfaces\PaymentTypeInterface;
use VitesseCms\Shop\Models\Order;

/**
 * Class AbstractSetting
 */
abstract class AbstractPaymentType
    extends AbstractInjectable
    implements PaymentTypeInterface, ExtendAdminFormInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildAdminForm(AbstractForm $form): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function prepareOrder(Order $order): void
    {
        $paymentType = $order->_('paymentType');
        $paymentType['transactionId'] = (string)$order->_('orderId');
        $order->set('paymentType', $paymentType);
    }

    /**
     * {@inheritdoc}
     */
    public function isProcessRedirect(): bool
    {
        return false;
    }
}
