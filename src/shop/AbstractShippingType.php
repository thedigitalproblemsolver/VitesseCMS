<?php declare(strict_types=1);

namespace VitesseCms\Shop;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Shop\Enum\DiscountEnum;
use VitesseCms\Shop\Enum\ShippingEnum;
use VitesseCms\Shop\Forms\ShippingForm;
use VitesseCms\Shop\Helpers\CheckoutHelper;
use VitesseCms\Shop\Helpers\DiscountHelper;
use VitesseCms\Shop\Interfaces\ShippingTypeInterface;
use VitesseCms\Shop\Models\Discount;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Models\Shipping;
use VitesseCms\User\Models\User;
use MongoDB\BSON\ObjectId;
use Phalcon\Di;
use Phalcon\Tag;

abstract class AbstractShippingType extends AbstractCollection implements
    ShippingTypeInterface
{
    /**
     * @var Shipping
     */
    protected $shipping;

    /**
     * @var string
     */
    public $barcode;

    /**
     * @param ShippingForm $form
     */
    public function buildAdminForm(ShippingForm $form)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLabelLink(Order $order): string
    {
        if (
            isset($order->_('orderState')['printShippingLabel'])
            && $order->_('orderState')['printShippingLabel'] === '1'
        ) :
            $link = 'admin/shop/adminshipping/shippingLabel/'.$order->getId();

            return Tag::linkTo([
                    'action' => $link.'?packageType='.ShippingEnum::ENVELOPE,
                    'text'   => '&nbsp;envelope label',
                    'class'  => 'fa fa-envelope',
                    'target' => '_blank',
                ]).
                '<br />'.
                Tag::linkTo([
                        'action' => $link.'?packageType='.ShippingEnum::PACKAGE,
                        'text'   => '&nbsp;package label',
                        'class'  => 'fa fa-gift',
                        'target' => '_blank',
                    ]
                );
        endif;

        return '';
    }

    public function getLabel(Order $order, ?string $packageType): ?string
    {
        return '';
    }

    /**
     * @inheritdoc
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    public function hasFreeShippingItems(array $items): bool
    {
        /** @var User $user */
        $user = Di::getDefault()->get('user');
        $shipToAddress = CheckoutHelper::getShiptoAddress($user);
        if (\is_array($items['products'])) :
            foreach ($items['products'] as $product) :
                if (\is_array($product->_('discount'))) :
                    $ids = [];
                    foreach ($product->_('discount') as $discountId) :
                        if (MongoUtil::isObjectId($discountId)) :
                            $ids[] = new ObjectId($discountId);
                        endif;
                    endforeach;
                    Discount::setFindValue('_id', ['$in' => $ids]);
                    Discount::setFindValue('target', DiscountEnum::TARGET_FREE_SHIPPING);
                    if (
                        $shipToAddress !== null
                        && $user->isLoggedIn()
                        && !empty($shipToAddress->_('country'))
                    ) :
                        $discounts = Discount::findAll();
                        foreach ($discounts as $discount):
                            if (\in_array($shipToAddress->_('country'), $discount->_('countries'), true)) :
                                return true;
                            endif;
                        endforeach;
                    else :
                        if (Discount::count()):
                            return true;
                        endif;
                    endif;
                endif;
            endforeach;
        endif;

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTrackAndTraceLink(Order $order): string
    {
        return '';
    }

    public function hasFreeShippingCodeForAll(): bool
    {
        /** @var Discount $discount */
        $discount = DiscountHelper::getFromSession(DiscountEnum::TARGET_FREE_SHIPPING);

        if ($discount && (new DiscountHelper())->isValid($discount)) :
            foreach ($discount->_('countries') as $country) :
                if ($country === 'all') {
                    return true;
                }
            endforeach;
        endif;

        return false;
    }

    public function getBarcode(): string
    {
        return $this->barcode??'';
    }
}
