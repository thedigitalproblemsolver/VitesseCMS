<?php  declare(strict_types=1);

namespace VitesseCms\Shop\Enum;

use VitesseCms\Core\AbstractEnum;

final class MyparcelShippingEnum extends AbstractEnum
{
    public const PACKAGETYPE_PACKAGE = 1;
    public const PACKAGETYPE_MAILBOX_PACKAGE = 2;
    public const PACKAGETYPE_LETTER = 3;
}
