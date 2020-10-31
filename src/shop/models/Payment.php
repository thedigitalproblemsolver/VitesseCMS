<?php declare(strict_types=1);

namespace VitesseCms\Shop\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;

class Payment extends AbstractCollection
{
    /**
     * @var string
     */
    public $type;

    public function getTypes() : array
    {
        $types = [];
        $files = DirectoryUtil::getFilelist($this->di->config->get('rootDir') . 'src/shop/paymentTypes/');
        foreach ($files as $path => $file) :
            $name = FileUtil::getName($file);
            $types[$name] = $name;
        endforeach;

        return $types;
    }

    public function getTypeClass(): string
    {
        return 'VitesseCms\Shop\PaymentTypes\\'.$this->type;
    }

    public function doPayment(Order $order): void
    {
        $object = $this->getTypeClass();
        (new $object())->doPayment($order, $this);
    }

    public function getTransactionState(
        int $transactionId,
        string $orderStateParent = null
    ): OrderState
    {
        $object = $this->getTypeClass();

        $callingName = (new $object())->getTransactionState($transactionId, $this);

        OrderState::setFindValue('calling_name', $callingName);
        OrderState::setFindValue('parentId', $orderStateParent);
        /** @var OrderState $orderState */
        $orderState = OrderState::findFirst();

        if(!$orderState && $orderStateParent !== null) :
            $orderState = OrderState::findById($orderStateParent);
        endif;

        return $orderState;
    }

    public function prepareOrder(Order $order)
    {
        $object = $this->getTypeClass();
        (new $object())->prepareOrder($order);
    }

    public function isProcessRedirect()
    {
        $object = $this->getTypeClass();
        return (new $object())->isProcessRedirect();
    }
}
