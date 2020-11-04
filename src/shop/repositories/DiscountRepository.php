<?php declare(strict_types=1);

namespace VitesseCms\Shop\Repositories;

use VitesseCms\Shop\Models\Discount;

class DiscountRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?Discount
    {
        Discount::setFindPublished($hideUnpublished);

        /** @var Discount $discount */
        $discount = Discount::findById($id);
        if ($discount instanceof Discount):
            return $discount;
        endif;

        return null;
    }
}
