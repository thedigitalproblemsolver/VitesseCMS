<?php declare(strict_types=1);

namespace VitesseCms\Shop\Interfaces;

use VitesseCms\Shop\Models\Order;

interface ShippingTypeInterface
{
    public function calculateOrderAmount(Order $order) : float;

    public function calculateOrderVat(Order $order) : float;

    public function calculateCartAmount(array $items) : float;

    public function calculateCartVat(array $items) : float;

    public function calculateCartTotal(array $items) : float;

    public function getLabelLink(Order $order): string;

    public function getLabel(Order $order, ?string $packageType): ?string;

    public function hasFreeShippingItems(array $items): bool;

    public function getTrackAndTraceLink(Order $order): string;
}
