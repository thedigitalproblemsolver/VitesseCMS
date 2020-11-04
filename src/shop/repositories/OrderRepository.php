<?php declare(strict_types=1);

namespace VitesseCms\Shop\Repositories;

use VitesseCms\Shop\Models\Order;
use MongoDB\BSON\ObjectId;

class OrderRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?Order
    {
        Order::setFindPublished($hideUnpublished);

        /** @var Order $order */
        $order = Order::findById($id);
        if (is_object($order)):
            return $order;
        endif;

        return null;
    }

    public function getViewOrder(string $id, ObjectId $userId): ?Order
    {
        Order::setFindPublished(false);
        Order::setFindValue('shopper.user._id', $userId);
        /** @var Order $order */
        $order = Order::findById($id);

        if($order instanceof Order):
            return $order;
        endif;

        return null;
    }
}
