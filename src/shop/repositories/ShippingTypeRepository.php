<?php declare(strict_types=1);

namespace VitesseCms\Shop\Repositories;

use MicheleAngioni\PhalconRepositories\AbstractCollectionRepository;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Shop\AbstractShippingType;
use VitesseCms\Shop\Models\Shipping;
use VitesseCms\Shop\Models\ShippingIterator;

class ShippingTypeRepository extends AbstractCollectionRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?AbstractShippingType
    {
        Shipping::setFindPublished($hideUnpublished);

        /** @var AbstractShippingType $shipping */
        $shipping = Shipping::findById($id);
        if (is_object($shipping)):
            return $shipping;
        endif;

        return null;
    }

    public function findAll(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true
    ): ShippingIterator {
        Shipping::setFindPublished($hideUnpublished);

        if($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                Shipping::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;

        return new ShippingIterator(Shipping::findAll());
    }
}
