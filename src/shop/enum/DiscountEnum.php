<?php  declare(strict_types=1);

namespace VitesseCms\Shop\Enum;

use VitesseCms\Core\AbstractEnum;

final class DiscountEnum extends AbstractEnum
{
    public const TARGET_PRODUCT = 'Product';
    public const TARGET_ORDER = 'Order';
    public const TARGET_FREE_SHIPPING = 'FreeShipping';

    public const TYPE_CURRENCY = 'currency';
    public const TYPE_PRECENTAGE = 'percentage';
}
