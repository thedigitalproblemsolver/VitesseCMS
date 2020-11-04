<?php declare(strict_types=1);

namespace VitesseCms\Shop\Listeners;

use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Core\Factories\ObjectFactory;
use VitesseCms\Shop\Controllers\AdminorderController;
use VitesseCms\Shop\Forms\OrderOrderStateForm;
use VitesseCms\Shop\Forms\ShippingBarcodeForm;
use VitesseCms\Shop\Models\Order;
use VitesseCms\User\Utils\PermissionUtils;
use Phalcon\Events\Event;
use Phalcon\Tag;

class AdminorderControllerListener
{
    public function beforeEdit(Event $event, AdminorderController $controller, Order $order): void
    {
        if (AdminUtil::isAdminPage()) :
            $shippingType = $controller->repositories->shippingType->getById((string)$order->getShippingType()->getId());
            if ($shippingType) :
                $controller->addRenderParam('shippingLabelLink', $shippingType->getLabelLink($order));
            endif;

            if (!empty($order->getAffiliateId())) :
                $controller->addRenderParam(
                    'affiliateName',
                    $controller->repositories->item->getById($order->getAffiliateId(), false)
                );
            endif;

            if (
                $controller->user->getPermissionRole() === 'superadmin'
                && empty($order->getShippingType()->getBarcode())
            ) :
                $barcodeForm = new ShippingBarcodeForm();
                $barcodeForm->addText('Barcode', 'barcode');
                $controller->addRenderParam('barcodeForm',
                    $barcodeForm->renderForm(
                        'admin/shop/adminshipping/setBarcode/'.$order->getId(),
                        'barcodeForm',
                        true
                    )
                );
            endif;
        endif;
    }

    public function adminListItem(Event $event, AdminorderController $controller, Order $order): void
    {
        $return = '';
        if (PermissionUtils::check(
            $controller->user,
            $controller->router->getModulePrefix().'shop',
            'adminorder',
            'sendemail'
        )) :
            $return .= Tag::linkTo([
                'action' => '/admin/shop/adminorder/sendemail/'.$order->getId(),
                'class'  => 'fa fa-envelope',
            ]);
        endif;

        if (PermissionUtils::check(
            $controller->user,
            $controller->router->getModulePrefix().'shop',
            'adminorder',
            'print'
        )) :
            $return .= Tag::linkTo(['action' => '', 'class' => 'fa fa-print']);
        endif;

        $order->setExtraAdminListButtons($return);

        if (PermissionUtils::check(
            $controller->user,
            $controller->router->getModulePrefix().'shop',
            'adminorder',
            'changeOrderState'
        )) :
            $tmpOrderState = ObjectFactory::create();
            if (is_string($order->_('orderState'))) :
                $tmpOrderState->set('orderStateId', $order->_('orderState'));
            else :
                $tmpOrderState->set('orderStateId', (string)$order->_('orderState')['_id']);
            endif;

            $orderStateForm = new OrderOrderStateForm($tmpOrderState);

            $form = $orderStateForm->renderForm(
                'shop/adminorder/changeOrderState/'.$order->getId(),
                'changeOrderState'
            );
            $orderStateForm = $form;
        else :
            $orderStateForm = $order->_('orderState')['name'][$controller->configuration->getLanguageShort()];
        endif;

        $order->setAdminListExtra($controller->view->renderModuleTemplate(
            'shop',
            'orderAdminListItem',
            'admin/',
            [
                'order'          => $order,
                'orderStateForm' => $orderStateForm,
            ]
        ));
    }
}
