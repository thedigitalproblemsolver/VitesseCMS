<?php declare(strict_types=1);

namespace VitesseCms\Setting\Enum;

use VitesseCms\Core\AbstractEnum;

class CallingNameEnum extends AbstractEnum
{
    public const FAVICON = 'SITE_LOGO_FAVICON';
    public const LOGO_DEFAULT = 'SITE_LOGO_DEFAULT';
    public const LOGO_MOBILE = 'SITE_LOGO_MOBILE';
    public const LOGO_EMAIL = 'SITE_LOGO_EMAIL';
    public const GOOGLE_ANALYTICS_TRACKINGID = 'GOOGLE_ANALYTICS_TRACKINGID';
    public const GOOGLE_SITE_VERIFICATION = 'GOOGLE_SITE_VERIFICATION';
}
