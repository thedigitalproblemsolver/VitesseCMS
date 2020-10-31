<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use Phalcon\Events\Manager;

class InitiateListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('FieldImage', new ImportFieldImageListener());
        $eventsManager->attach('FieldPrice', new ImportFieldPriceListener());
        $eventsManager->attach('FieldDatagroup', new ImportFieldDatagroupListener());
    }
}
