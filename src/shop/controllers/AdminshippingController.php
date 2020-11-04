<?php declare(strict_types=1);

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Shop\Forms\ShippingForm;
use VitesseCms\Shop\Interfaces\AdminRepositoriesInterface;
use VitesseCms\Shop\Interfaces\AdminRepositoryInterface;
use VitesseCms\Shop\Interfaces\ShippingTypeInterface;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Models\Shipping;

class AdminshippingController extends AbstractAdminController implements AdminRepositoriesInterface
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Shipping::class;
        $this->classForm = ShippingForm::class;
    }

    public function shippingLabelAction(string $id): void
    {
        if ($id) :
            $order = $this->repositories->order->getById($id, false);
            $shippingType = $this->repositories->shippingType->getById($order->_('shippingType')['_id']);
            $shippingType->getLabel($order, $this->request->get('packageType'));
        endif;

        parent::disableView();
    }

    public function setBarcodeAction(string $orderId): void
    {
        $order = $this->repositories->order->getById($orderId);
        $shippingType = $order->_('shippingType');
        $shippingType['barcode'] = trim($this->request->get('barcode'));
        $order->set('shippingType', $shippingType);
        $order->save();

        $this->redirect();
    }
}
