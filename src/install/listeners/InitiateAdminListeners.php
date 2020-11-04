<?php declare(strict_types=1);

namespace VitesseCms\Install\Listeners;

use VitesseCms\Content\Listeners\ModelItemListener;
use VitesseCms\Content\Models\Item;
use Phalcon\Events\Manager;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach(Item::class, new ModelItemListener());
    }
}
