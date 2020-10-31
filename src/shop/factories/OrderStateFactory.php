<?php

namespace VitesseCms\Shop\Factories;

use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Shop\Models\OrderState;

/**
 * Class OrderStateFactory
 */
class OrderStateFactory
{
    /**
     * @param string $name
     * @param string $calling_name
     * @param bool $published
     * @param string|null $parentId
     * @param string $stockAction
     * @param string $bodytext
     * @param string $messageText
     * @param string $messageType
     * @param array $analyticsTriggers
     * @param bool $clearCart
     * @param bool $printShippingLabel
     * @param int $ordering
     *
     * @return OrderState
     */
    public static function create(
        string $name,
        string $calling_name,
        bool $published = false,
        string $parentId = null,
        string $stockAction = '',
        string $bodytext = '',
        string $messageText = '',
        string $messageType = '',
        array $analyticsTriggers = [],
        bool $clearCart = false,
        bool $printShippingLabel = false,
        int $ordering = 0
    ): OrderState {
        if ($parentId !== null && MongoUtil::isObjectId($parentId)) :
            $parent = OrderState::findById($parentId);
            $parent->set('hasChildren', true)->save();
        endif;

        return (new OrderState())
            ->set('name', $name, true)
            ->set('calling_name', $calling_name)
            ->set('parentId', $parentId)
            ->set('published', $published)
            ->set('stockAction', $stockAction)
            ->set('bodytext', $bodytext, true)
            ->set('messageText', $messageText, true)
            ->set('messageType', $messageType)
            ->set('analyticsTriggers', $analyticsTriggers)
            ->set('clearCart', $clearCart)
            ->set('printShippingLabel', $printShippingLabel)
            ->set('ordering', $ordering);
    }
}
