<?php declare(strict_types=1);

namespace VitesseCms\Shop\Interfaces;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Shop\Repositories\DiscountRepository;
use VitesseCms\Shop\Repositories\OrderRepository;
use VitesseCms\Shop\Repositories\OrderStateRepository;
use VitesseCms\Shop\Repositories\PaymentRepository;
use VitesseCms\Shop\Repositories\ShippingTypeRepository;
use VitesseCms\Shop\Repositories\ShopperRepository;

/**
 * Interface RepositoryInterface
 * @property ItemRepository $item
 * @property ShippingTypeRepository $shippingType
 * @property OrderRepository $order
 * @property ShopperRepository $shopper
 * @property PaymentRepository $payment
 * @property DiscountRepository $discount
 * @property OrderStateRepository $orderState
 * @property DatagroupRepository $datagroup
 * @property DatafieldRepository $datafield
 */
interface RepositoryInterface
{
}
