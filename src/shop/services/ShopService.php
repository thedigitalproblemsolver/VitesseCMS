<?php declare(strict_types=1);

namespace VitesseCms\Shop\Services;

use VitesseCms\Shop\Helpers\CartHelper;
use VitesseCms\Shop\Helpers\CheckoutHelper;
use VitesseCms\Shop\Helpers\DiscountHelper;

class ShopService
{
    /**
     * @var DiscountHelper
     */
    public $discount;

    /**
     * @var CheckoutHelper
     */
    public $checkout;

    /**
     * @var CartHelper
     */
    public $cart;

    public function __construct(
        CartHelper $cart,
        DiscountHelper $discount,
        CheckoutHelper $checkout
    ) {
        $this->discount = $discount;
        $this->checkout = $checkout;
        $this->cart = $cart;
    }
}
