<?php declare(strict_types=1);

namespace VitesseCms\Shop;

use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Core\Interfaces\ExtendAdminFormInterface;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Shop\Interfaces\DiscountInterface;
use VitesseCms\Shop\Interfaces\Item;

abstract class AbstractDiscountType extends AbstractInjectable implements
    DiscountInterface,
    ExtendAdminFormInterface
{
    public function buildAdminForm(AbstractForm $form): void
    {
    }
}
