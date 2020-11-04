<?php declare(strict_types=1);

namespace VitesseCms\Shop\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Shop\AbstractShippingType;
use VitesseCms\Shop\Factories\ShippingTypeFactory;

class Order extends AbstractCollection
{
    /**
     * @var OrderState
     */
    public $orderState;

    /**
     * @var AbstractShippingType
     */
    public $shippingType;

    /**
     * @var ?string
     */
    public $affiliateId;

    /**
     * @var string
     */
    public $ipAddress;

    /**
     * @var string
     */
    public $property;

    /**
     * @var int
     */
    public $orderId;

    /**
     * @var Shopper
     */
    public $shopper;

    /**
     * @var array
     */
    public $shiptoAddress;

    /**
     * @var array
     */
    public $items;

    /**
     * @var float
     */
    public $shippingAmount;

    /**
     * @var float
     */
    public $shippingTax;

    /**
     * @var string
     */
    public $shippingTaxDisplay;

    /**
     * @var string
     */
    public $shippingAmountDisplay;

    /**
     * @var Payment
     */
    public $paymentType;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var ?string
     */
    public $orderMessage;

    /**
     * @var Discount
     */
    public $discount;

    /**
     * @var float
     */
    public $totalDiscount;

    /**
     * @var string
     */
    public $totalDiscountDisplay;

    /**
     * @var float
     */
    public $total;

    /**
     * @var string
     */
    public $totalDisplay;

    public function getNumber(): int
    {
        return (int)$this->_('orderId');
    }

    public function getTotal(): float
    {
        return (float)$this->_('total');
    }

    public function getShippingType(): AbstractShippingType
    {
        if (is_array($this->shippingType)):
            return ShippingTypeFactory::createFromArray($this->shippingType);
        endif;

        return $this->shippingType;
    }

    public function getAffiliateId(): ?string
    {
        if (!is_string($this->affiliateId)) :
            return null;
        endif;

        return $this->affiliateId;
    }

    public function setOrderState(OrderState $orderState): Order
    {
        $this->orderState = $orderState;

        return $this;
    }

    public function setShippingType(AbstractShippingType $shippingType): Order
    {
        $this->shippingType = $shippingType;

        return $this;
    }

    public function setAffiliateId($affiliateId): Order
    {
        $this->affiliateId = $affiliateId;

        return $this;
    }

    public function setIpAddress(string $ipAddress): Order
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function setProperty(string $property): Order
    {
        $this->property = $property;

        return $this;
    }

    public function setOrderId(int $orderId): Order
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function setShopper(Shopper $shopper): Order
    {
        $this->shopper = $shopper;

        return $this;
    }

    public function setShiptoAddress(ShiptoAddress $shiptoAddress): Order
    {
        $this->shiptoAddress = $shiptoAddress;

        return $this;
    }

    public function getShiptoAddress(): array
    {
        return $this->shiptoAddress??[];
    }

    public function setItems(array $items): Order
    {
        $this->items = $items;

        return $this;
    }

    public function setShippingAmount(float $shippingAmount): Order
    {
        $this->shippingAmount = $shippingAmount;

        return $this;
    }

    public function setShippingTax(float $shippingTax): Order
    {
        $this->shippingTax = $shippingTax;

        return $this;
    }

    public function setShippingAmountDisplay(string $shippingAmountDisplay): Order
    {
        $this->shippingAmountDisplay = $shippingAmountDisplay;

        return $this;
    }

    public function setShippingTaxDisplay(string $shippingTaxDisplay): Order
    {
        $this->shippingTaxDisplay = $shippingTaxDisplay;

        return $this;
    }

    public function setPaymentType(Payment $paymentType): Order
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function setCurrency(string $currency): Order
    {
        $this->currency = $currency;

        return $this;
    }

    public function setOrderMessage(?string $orderMessage): Order
    {
        $this->orderMessage = $orderMessage;

        return $this;
    }

    public function setDiscount(Discount $discount): Order
    {
        $this->discount = $discount;

        return $this;
    }

    public function setTotalDiscount(float $totalDiscount): Order
    {
        $this->totalDiscount = $totalDiscount;

        return $this;
    }

    public function setTotalDiscountDisplay(string $totalDiscountDisplay): Order
    {
        $this->totalDiscountDisplay = $totalDiscountDisplay;

        return $this;
    }

    public function setTotal(float $total): Order
    {
        $this->total = $total;

        return $this;
    }

    public function setTotalDisplay(string $totalDisplay): Order
    {
        $this->totalDisplay = $totalDisplay;

        return $this;
    }

    public function getProducts(): array
    {
        return $this->items['products']??[];
    }
}
