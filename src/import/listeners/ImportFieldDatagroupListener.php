<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use VitesseCms\Content\Models\Item;
use VitesseCms\Import\Models\ImportDatafield;
use Phalcon\Events\Event;

class ImportFieldDatagroupListener
{
    public function parse(Event $event, Item $item, ImportDatafield $importDatafield): void
    {
        echo 'Wordt deze gebruit? Zo ja, nog te verwerken';

        /*Item::setFindValue('name.'.$this->configuration->getLanguageShort(), $value);
        Item::setFindValue('datagroup', $datafield->_('datagroup'));
        $valueItem = Item::findFirst();
        if (!$valueItem) {
            $valueItem = ItemFactory::create(
                $value,
                $datafield->_('datagroup'),
                [],
                true
            );
            $valueItem->save();
        }

        return (string)$valueItem->getId();*/
    }
}
