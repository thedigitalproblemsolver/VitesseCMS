<?php declare(strict_types=1);

namespace VitesseCms\Shop\Enum;

use VitesseCms\Core\AbstractEnum;

final class OrderStateEnum extends AbstractEnum
{
    public const PREORDER = 'PREORDER';
    public const PENDING = 'PENDING';
    public const CONFIRMED = 'CONFIRMED';
    public const BANKTRANSFER = 'BANKTRANSFER';
    public const PAID = 'PAID';
    public const SHIPPED = 'SHIPPED';
    public const CANCELLED = 'CANCELLED';
    public const ERROR = 'ERROR';
    public const BACKORDER = 'BACKORDER';

    public const PREORDER_LABEL = '%SHOP_ORDERSTATE_PREORDER%';
    public const PENDING_LABEL = '%SHOP_ORDERSTATE_PENDING%';
    public const CONFIRMED_LABEL = '%SHOP_ORDERSTATE_CONFIRMED%';
    public const BANKTRANSFER_LABEL = '%SHOP_ORDERSTATE_BANKTRANSFER%';
    public const PAID_LABEL = '%SHOP_ORDERSTATE_PAID%';
    public const SHIPPED_LABEL = '%SHOP_ORDERSTATE_SHIPPED%';
    public const CANCELLED_LABEL = '%SHOP_ORDERSTATE_CANCELLED%';
    public const ERROR_LABEL = '%SHOP_ORDERSTATE_ERROR%';

    public const ANALYTICS_TRIGGER_MAILCHIMP = 'mailchimp';
    public const ANALYTICS_TRIGGERS = [
        'adwords' => 'Adwords',
        'analytics' => 'Analytics',
        'facebook' => 'Facebook Pixel',
        self::ANALYTICS_TRIGGER_MAILCHIMP => 'Mailchimp',
        'tradetracker' => 'TradeTracker'
    ];

    public const ORDER_STATES = [
        self::BANKTRANSFER => self::BANKTRANSFER_LABEL,
        self::CANCELLED => self::CANCELLED_LABEL,
        self::CONFIRMED => self::CONFIRMED_LABEL,
        self::ERROR => self::ERROR_LABEL,
        self::PAID => self::PAID_LABEL,
        self::PENDING => self::PENDING_LABEL,
        self::PREORDER => self::PREORDER_LABEL,
        self::SHIPPED => self::SHIPPED_LABEL
    ];

    public const STOCK_ACTION_INCREASE = 'increase';
    public const STOCK_ACTION_DECREASE = 'decrease';
}
