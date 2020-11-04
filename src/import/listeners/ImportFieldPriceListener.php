<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use VitesseCms\Content\Models\Item;
use VitesseCms\Import\Models\ImportDatafield;
use VitesseCms\Shop\Repositories\TaxRateRepository;
use Phalcon\Events\Event;

class ImportFieldPriceListener
{
    public function parse(Event $event, Item $item, ImportDatafield $importDatafield): void
    {
        $importValue = $item->_($importDatafield->getCallingName());
        $taxrate = (new TaxRateRepository())->getById($item->_('taxrate'));
        if ($taxrate !== null) :
            $item->set(
                $importDatafield->getCallingName(),
                $importValue * (1 - ($taxrate->getTaxRate() / 100))
            );
        endif;
        $item->set($importDatafield->getCallingName().'_purchase', 0);
        $item->set($importDatafield->getCallingName().'_sale', $importValue);
    }
}
