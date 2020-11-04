<?php declare(strict_types=1);

namespace VitesseCms\Shop\Models;

use VitesseCms\Shop\AbstractShippingType;
use VitesseCms\Shop\Helpers\ShippingHelper;

class Shipping extends AbstractShippingType
{
    /**
     * @var AbstractShippingType
     */
    protected $engine;

    public function afterFetch()
    {
        parent::afterFetch();

        if($this->_('type')) :
            $object = ShippingHelper::getClass($this->_('type'));
            $this->engine = new $object();
            $this->engine->set('shipping', $this);
        endif;
    }

    public function calculateOrderAmount(Order $order): float
    {
        return $this->engine->calculateOrderAmount($order);
    }

    public function calculateOrderVat(Order $order): float
    {
        return $this->engine->calculateOrderVat($order);
    }

    public function calculateOrderTotal(Order $order): float
    {
        return $this->engine->calculateOrderAmount($order) + $this->engine->calculateOrderVat($order);
    }

    public function calculateCartAmount(array $items): float
    {
        return $this->engine->calculateCartAmount($items);
    }

    public function calculateCartVat(array $items): float
    {
        return $this->engine->calculateCartVat($items);
    }

    public function calculateCartTotal(array $items): float
    {
        return $this->engine->calculateCartAmount($items) + $this->engine->calculateCartVat($items);
    }

    public function getLabelLink(Order $order): string
    {
        return $this->engine->getLabelLink($order);
    }

    public function getLabel(Order $order, ?string $packageType): ?string
    {
        return $this->engine->getLabel($order, $packageType);
    }

    public function hasFreeShippingItems(array $items): bool
    {
        return false;
    }

    public function getTrackAndTraceLink(Order $order): string
    {
        return $this->engine->getTrackAndTraceLink($order);
    }
}
