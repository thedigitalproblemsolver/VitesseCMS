<?php declare(strict_types=1);

namespace VitesseCms\Shop\Helpers;

use VitesseCms\Communication\Helpers\CommunicationHelper;
use VitesseCms\Communication\Helpers\NewsletterHelper;
use VitesseCms\Communication\Models\Newsletter;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Factories\LogFactory;
use VitesseCms\Core\Services\ViewService;
use VitesseCms\Sef\Utils\UtmUtil;
use VitesseCms\Shop\Enum\OrderStateEnum;
use VitesseCms\Shop\Models\Cart;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Models\OrderState;
use Phalcon\Mvc\User\Component;

class OrderHelper
{
    public static function setOrderStateByIds(
        string $orderObjectId,
        string $orderStateId
    ): Order
    {
        $orderState = OrderState::findById($orderStateId);

        Order::setFindPublished(false);
        /** @var Order $order */
        $order = Order::findById($orderObjectId);
        $order->set('orderState', $orderState);
        $order->save();

        OrderHelper::parseStockByOrderState($order);

        return $order;
    }

    public static function setOrderState(Order $order, OrderState $orderState): void
    {
        $order->set('orderState', $orderState);

        if (\is_array($order->_('shopper'))) :
            $email = $order->_('shopper')['user']['email'];
        else :
            $email = $order->_('shopper')->_('email');
        endif;

        if (!empty($orderState->_('addToNewsletters'))) :
            $newsletters = $orderState->_('addToNewsletters');
            (new Component())->content->addEventInput('orderId', (string)$order->getId());
            foreach ($newsletters as $newsletterId) :
                $newsletter = Newsletter::findById($newsletterId);
                if ($newsletter) :
                    NewsletterHelper::addMemberByEmail($newsletter, $email);
                endif;
            endforeach;
        endif;

        if (!empty($orderState->_('unsubscribeFromNewsletters'))) :
            $newsletters = $orderState->_('unsubscribeFromNewsletters');
            foreach ($newsletters as $newsletterId) :
                $newsletter = Newsletter::findById($newsletterId);
                if ($newsletter) :
                    NewsletterHelper::unsubscribeMemberByEmail($newsletter, $email);
                endif;
            endforeach;
        endif;

        if (!empty($orderState->_('removeFromNewsletters'))) :
            $newsletters = $orderState->_('removeFromNewsletters');
            foreach ($newsletters as $newsletterId) :
                $newsletter = Newsletter::findById($newsletterId);
                if ($newsletter) :
                    NewsletterHelper::removeMemberByEmail($newsletter, $email);
                endif;
            endforeach;
        endif;

        if ($orderState->_('clearCart')) :
            $cart = new Cart();
            $cart->clear();
            $cart->save();
        endif;

        OrderHelper::parseStockByOrderState($order);
    }

    public static function parseStockByOrderState(Order $order): void
    {
        if ($order->_('orderState')->_('stockAction')) :
            foreach ($order->_('items')['products'] as $orderItem) :
                $orderItem = (array)$orderItem;
                $item = Item::findById($orderItem['_id']);
                if ($item) :
                    $itemName = [$item->_('name')];
                    if ($item->_('gender')) :
                        $gender = Item::findById($item->_('gender'));
                        array_push($itemName, $gender->_('name'));
                    endif;
                    $logMessage = '';
                    //change normal stock
                    if ($item->_('stock')) :
                        switch ($order->_('orderState')->_('stockAction')) :
                            case OrderStateEnum::STOCK_ACTION_INCREASE:
                                $item->set('stock', (int)$item->_('stock') + $orderItem['quantity']);
                                $logMessage = implode(' ', $itemName) . ' stock increased by ' . $orderItem['quantity'];
                                break;
                            case OrderStateEnum::STOCK_ACTION_DECREASE:
                                $item->set('stock', (int)$item->_('stock') - $orderItem['quantity']);
                                $logMessage = implode(' ', $itemName) . ' stock decreased by ' . $orderItem['quantity'];
                                break;
                        endswitch;
                    elseif (isset($orderItem['variation']) && $item->_('variations')) :
                        //change variation stock
                        $itemVariations = $item->_('variations');
                        foreach ($itemVariations as $key => $variation) :
                            if ($orderItem['variation'] == $variation['sku']) :
                                array_push($itemName, $variation['sku']);
                                switch ($order->_('orderState')->_('stockAction')) :
                                    case OrderStateEnum::STOCK_ACTION_INCREASE:
                                        $variation['stock'] = (int)$variation['stock'] + $orderItem['quantity'];
                                        $logMessage = implode(' ', $itemName) . ' stock increased by ' . $orderItem['quantity'];
                                        break;
                                    case OrderStateEnum::STOCK_ACTION_DECREASE:
                                        $variation['stock'] = (int)$variation['stock'] - $orderItem['quantity'];
                                        $logMessage = implode(' ', $itemName) . ' stock decreased by ' . $orderItem['quantity'];
                                        break;
                                endswitch;
                                $itemVariations[$key] = $variation;
                            endif;
                        endforeach;
                        $item->set('variations', $itemVariations);
                    endif;
                    $item->save();

                    LogFactory::create(
                        $item->getId(),
                        Item::class,
                        $logMessage
                    )->save();
                endif;
            endforeach;
        endif;
    }

    public static function calculateTotals(Order $order): void
    {
        $subTotal = $taxTotal = 0;

        $subTotal += $order->_('shippingAmount');
        $taxTotal += $order->_('shippingTax');

        $subTotal += $order->_('items')['subTotal'];
        $taxTotal += $order->_('items')['vat'];

        $order->set('total', $subTotal + $taxTotal);

        $order->set('subTotal', $subTotal);
        $order->set('tax', $taxTotal);
    }

    public static function setDisplayFormats(Order $order): void
    {
        $fields = [
            'total',
            'discount',
            'shippingAmount',
            'shippingTax',
        ];

        foreach ($fields as $field) :
            $order->set(
                $field . 'Display',
                number_format(
                    (float)$order->_($field),
                    2, ',',
                    '.'
                )
            );
        endforeach;

        $items = $order->_('items');
        $items['vatDisplay'] = number_format(
            (float)$items['vat'],
            2, ',',
            '.'
        );
        $items['subTotalDisplay'] = number_format(
            (float)$items['subTotal'],
            2, ',',
            '.'
        );
        $order->set('items', $items);
    }

    public static function sendEmail(
        Order $order,
        ViewService $viewService
    ): void
    {
        if (!$viewService->getVar('shopOrder')) :
            $shopOrder = $viewService->renderTemplate(
                'order',
                'partials/shop',
                [
                    'orderItem' => $order,
                    'shopOrderId' => $order->_('orderId')
                ]
            );
            $viewService->set('shopOrder', $shopOrder);
        endif;

        UtmUtil::setMedium('email');
        UtmUtil::setSource('order');

        CommunicationHelper::sendSystemEmail(
            $order->_('shopper')['user']['email'],
            'success',
            'shoppaymentredirect',
            $order->_('shopper')['user']['email']
        );
    }

    public static function getLatestOrdernumber(): int
    {
        Order::setFindPublished(false);
        Order::addFindOrder('orderId', -1);
        Order::setFindLimit(1);
        $latestOrders = Order::findAll();
        if (count($latestOrders) > 0) :
            return $latestOrders[0]->_('orderId');
        endif;

        return random_int(1000, 1500);
    }

    public static function getNewOrdernumber(): int
    {
        return self::getLatestOrdernumber() + 1;
    }
}
