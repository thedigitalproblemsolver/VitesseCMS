<?php declare(strict_types=1);

namespace VitesseCms\Shop\Repositories;

use VitesseCms\Shop\Models\TaxRate;

class TaxRateRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?TaxRate
    {
        TaxRate::setFindPublished($hideUnpublished);

        /** @var TaxRate $taxrate */
        $taxrate = TaxRate::findById($id);
        if (is_object($taxrate)):
            return $taxrate;
        endif;

        return null;
    }
}
