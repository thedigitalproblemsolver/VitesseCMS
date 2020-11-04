<?php declare(strict_types=1);

namespace VitesseCms\Shop\Listeners;

use Phalcon\Events\Event;
use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Shop\Controllers\AdminorderstateController;
use VitesseCms\Shop\Enum\OrderStateEnum;
use VitesseCms\Shop\Models\OrderState;

class AdminorderstateControllerListener
{
    public function adminListItem(
        Event $event,
        AdminorderstateController $controller,
        OrderState $orderState
    ): void
    {
        switch ($orderState->getStockAction()) :
            case OrderStateEnum::STOCK_ACTION_INCREASE:
                $orderState->setAdminListName($orderState->getNameField() . ' +');
                break;
            case OrderStateEnum::STOCK_ACTION_DECREASE:
                $orderState->setAdminListName($orderState->getNameField() . ' -');
                break;
        endswitch;
    }
}
