<?php declare(strict_types=1);

namespace VitesseCms\Core\Repositories;

use VitesseCms\Core\Models\Datafield;
use VitesseCms\Core\Models\DatafieldIterator;
use VitesseCms\Database\Models\FindValueIterator;

class DatafieldRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?Datafield
    {
        Datafield::setFindPublished($hideUnpublished);

        /** @var Datafield $datafield */
        $datafield = Datafield::findById($id);
        if (is_object($datafield)):
            return $datafield;
        endif;

        return null;
    }

    public function findFirst(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true
    ): ?Datafield {
        Datafield::setFindPublished($hideUnpublished);
        $this->parsefindValues($findValues);

        /** @var Datafield $item */
        $item = Datafield::findFirst();
        if (is_object($item)):
            return $item;
        endif;

        return null;
    }

    public function findAll(?FindValueIterator $findValues = null, bool $hideUnpublished = true): DatafieldIterator
    {
        Datafield::setFindPublished($hideUnpublished);
        Datafield::addFindOrder('name');
        $this->parsefindValues($findValues);

        return new DatafieldIterator(Datafield::findAll());
    }

    protected function parsefindValues(?FindValueIterator $findValues = null): void
    {
        if ($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                Datafield::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;
    }
}
