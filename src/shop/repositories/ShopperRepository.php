<?php declare(strict_types=1);

namespace VitesseCms\Shop\Repositories;

use VitesseCms\Shop\Models\Shopper;

class ShopperRepository
{
    public function getByUserid(string $userId): ?Shopper
    {
        Shopper::setFindValue('userId', $userId);
        /** @var Shopper $shopper */
        $shopper = Shopper::findFirst();

        if($shopper instanceof Shopper):
            return $shopper;
        endif;

        return null;
    }
}
