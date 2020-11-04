<?php declare(strict_types=1);

namespace VitesseCms\Shop\Interfaces;

use VitesseCms\Database\Interfaces\BaseCollectionInterface;

interface TaxRateInterface extends BaseCollectionInterface
{
    public function getAdminlistName(): string;

    public function getTaxRate(): int;
}
