<?php declare(strict_types=1);

namespace VitesseCms\Shop\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Shop\Interfaces\DiscountInterface;

class Discount extends AbstractCollection implements DiscountInterface
{
    /**
     * @var string
     */
    public $target;

    /**
     * @var float
     */
    public $amount;

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function getTargetClass(): string
    {
        return 'VitesseCms\Shop\discountTypes\\' . $this->target;
    }

    public function getAmount(): ?float
    {
        if($this->amount !== null) :
            return (float)$this->amount;
        endif;

        return null;
    }
}
