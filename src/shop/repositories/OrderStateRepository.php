<?php declare(strict_types=1);

namespace VitesseCms\Shop\Repositories;

use VitesseCms\Shop\Enum\OrderStateEnum;
use VitesseCms\Shop\Models\OrderState;

class OrderStateRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?OrderState
    {
        OrderState::setFindPublished($hideUnpublished);

        /** @var OrderState $orderState */
        $orderState = OrderState::findById($id);
        if (is_object($orderState)):
            return $orderState;
        endif;

        return null;
    }

    public function getByState(string $state): ?OrderState
    {
        if (!isset(OrderStateEnum::ORDER_STATES[$state])):
            die('unknow orderstate');
        endif;

        OrderState::setFindValue('calling_name', $state);
        /** @var OrderState $orderState */
        $orderState = OrderState::findFirst();
        if ($orderState instanceof OrderState):
            return $orderState;
        endif;

        return null;
    }
}
