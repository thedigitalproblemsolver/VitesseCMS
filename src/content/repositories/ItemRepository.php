<?php declare(strict_types=1);

namespace VitesseCms\Content\Repositories;

use VitesseCms\Content\Models\Item;
use VitesseCms\Content\Models\ItemIterator;
use VitesseCms\Database\Models\FindValueIterator;

class ItemRepository
{
    public function findAll(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true,
        ?int $limit = null
    ): ItemIterator {
        Item::setFindPublished($hideUnpublished);
        Item::addFindOrder('name');
        if($limit !== null) :
            Item::setFindLimit($limit);
        endif;
        $this->parsefindValues($findValues);

        return new ItemIterator(Item::findAll());
    }

    public function getById(string $id, bool $hideUnpublished = true, $renderFields = true): ?Item
    {
        Item::setFindPublished($hideUnpublished);
        Item::setRenderFields($renderFields);

        /** @var Item $item */
        $item = Item::findById($id);
        if (is_object($item)):
            return $item;
        endif;

        return null;
    }

    public function getHomePage(): ?Item
    {
        Item::setFindValue('homepage', '1');

        /** @var Item $item */
        $item = Item::findFirst();
        if (is_object($item)):
            return $item;
        endif;

        return null;
    }

    public function findFirst(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true
    ): ?Item {
        Item::setFindPublished($hideUnpublished);
        $this->parsefindValues($findValues);

        /** @var Item $item */
        $item = Item::findFirst();
        if (is_object($item)):
            return $item;
        endif;

        return null;
    }

    protected function parsefindValues(?FindValueIterator $findValues = null): void
    {
        if ($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                Item::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;
    }

    public function findBySlug(string $slug, $languageShortCode): ?Item
    {
        Item::setFindValue('slug.'.$languageShortCode, $slug);
        Item::addFindOrder('createdAt',-1);

        /** @var Item $item */
        $item = Item::findFirst();
        if (is_object($item)):
            return $item;
        endif;

        return null;
    }
}
