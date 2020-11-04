<?php declare(strict_types=1);

namespace VitesseCms\Shop\Factories;

use VitesseCms\Shop\Models\Discount;
use \DateTime;

class DiscountFactory
{
    public static function create(
        string $name,
        string $target,
        string $code = '',
        ?int $amount = null,
        ?DateTime $fromDate = null,
        ?DateTime $tillDate = null
    ): Discount
    {
        $till = $from = null;

        if($fromDate !== null) {
            /** @var DateTime $fromDate */
            $from = $fromDate->format('Y-m-d');
        }
        if($tillDate !== null) {
            /** @var DateTime $tillDate */
            $till = $tillDate->format('Y-m-d');
        }

        return (new Discount())
            ->set('name', $name)
            ->set('target', $target)
            ->set('code', $code)
            ->set('fromDate', $from)
            ->set('tillDate', $till)
            ->set('amount', $amount)
        ;
    }

    public static function createRandom(
        string $name,
        string $target,
        ?int $amount = null,
        string $prefix = '',
        ?DateTime $tillDate = null,
        ?DateTime $fromDate = null
    ): Discount {
        $code = null;
        $count = 1;
        while ($count !== 0) :
            $code = $prefix.substr(uniqid('',true),2,8);
            Discount::setFindValue('code', $code);
            $count = Discount::count();
        endwhile;

        return self::create($name, $target, $code, $amount, $fromDate, $tillDate);
    }
}
