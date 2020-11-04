<?php declare(strict_types=1);

namespace VitesseCms\Shop\Models;

use VitesseCms\Database\AbstractCollection;

class OrderState extends AbstractCollection
{
    /**
     * @var string
     */
    public $stockAction;

    public function getStockAction(): string
    {
        return $this->stockAction;
    }
}
