<?php

namespace VitesseCms\Shop\Factories;

use VitesseCms\Core\AbstractFactory;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Core\Interfaces\FactoryInterface;
use VitesseCms\Shop\Models\Cart;

/**
 * Class CartFactory
 */
class CartFactory extends AbstractFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public static function create(BaseObjectInterface $bindData = null) : BaseObjectInterface
    {
        $cart = parent::createCollection(Cart::class);

        return $cart;
    }
}
