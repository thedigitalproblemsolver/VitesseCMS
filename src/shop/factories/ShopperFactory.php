<?php

namespace VitesseCms\Shop\Factories;

use VitesseCms\Core\AbstractFactory;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Core\Interfaces\FactoryInterface;
use VitesseCms\Shop\Models\Shopper;
use VitesseCms\User\Models\User;

/**
 * Class ShopperFactory
 */
class ShopperFactory extends AbstractFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public static function create(BaseObjectInterface $bindData = null) : BaseObjectInterface
    {
        $shopper = parent::createCollection(Shopper::class);

        return $shopper;
    }

    /**
     * @param User $user
     * @param array $data
     *
     * @return Shopper
     */
    public static function createFromUser(User $user, array $data = []): Shopper
    {
        $shopper = new Shopper();
        //$shopper->set('userId', (string)$user->getId());
        $shopper->set('user', $user);
        $shopper->addShopperInformation($data);
        $shopper->set('published', true);

        return $shopper;
    }
}
