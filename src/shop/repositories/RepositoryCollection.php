<?php declare(strict_types=1);

namespace VitesseCms\Shop\Repositories;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Repositories\DatafieldRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Shop\Interfaces\RepositoryInterface;

class RepositoryCollection implements RepositoryInterface
{
    /**
     * @var ShippingTypeRepository
     */
    public $shippingType;

    /**
     * @var ItemRepository
     */
    public $item;

    /**
     * @var OrderRepository
     */
    public $order;

    /**
     * @var ShopperRepository
     */
    public $shopper;

    /**
     * @var PaymentRepository
     */
    public $payment;

    /**
     * @var DiscountRepository
     */
    public $discount;

    /**
     * @var OrderStateRepository
     */
    public $orderState;

    /**
     * @var DatagroupRepository
     */
    public $datagroup;

    /**
     * @var DatafieldRepository
     */
    public $datafield;

    public function __construct(
        ShippingTypeRepository $shippingTypeRepository,
        ItemRepository $itemRepository,
        OrderRepository $orderRepository,
        ShopperRepository $shopperRepository,
        PaymentRepository $paymentRepository,
        DiscountRepository $discountRepository,
        OrderStateRepository $orderStateRepository,
        DatagroupRepository $datagroupRepository,
        DatafieldRepository $datafieldRepository
    ) {
        $this->shippingType = $shippingTypeRepository;
        $this->item = $itemRepository;
        $this->order = $orderRepository;
        $this->shopper = $shopperRepository;
        $this->payment = $paymentRepository;
        $this->discount = $discountRepository;
        $this->orderState = $orderStateRepository;
        $this->datagroup = $datagroupRepository;
        $this->datafield = $datafieldRepository;
    }
}
