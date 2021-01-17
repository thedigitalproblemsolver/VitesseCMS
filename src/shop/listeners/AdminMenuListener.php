<?php declare(strict_types=1);

namespace VitesseCms\Shop\Listeners;

use VitesseCms\Admin\Models\AdminMenu;
use VitesseCms\Admin\Models\AdminMenuNavBarChildren;
use VitesseCms\Core\Models\Datagroup;
use Phalcon\Di;
use Phalcon\Events\Event;

class AdminMenuListener
{
    public function AddChildren(Event $event, AdminMenu $adminMenu): void
    {
        if ($adminMenu->getUser()->getPermissionRole() === 'superadmin') :
            $webshopProductGroups = $adminMenu->getGroups()->getByKey('webshopProduct');
            $webshopContentGroups = $adminMenu->getGroups()->getByKey('webshopContent');
            $children = new AdminMenuNavBarChildren();

            if ($webshopProductGroups !== null) :
                /** @var Datagroup $contentGroup */
                foreach ($webshopProductGroups->getDatagroups() as $webshopProductGroup) :
                    $children->addChild($webshopProductGroup->_('name'),
                        'admin/content/adminitem/adminList/?filter[datagroup]='.$webshopProductGroup->getId());
                endforeach;
                $children->addLine();
            endif;

            $children->addChild('Orders', 'admin/shop/adminorder/adminList');
            $children->addChild('OrderStates', 'admin/shop/adminorderstate/adminList');

            if($webshopContentGroups !== null) :
                $children->addLine();
                foreach ($webshopContentGroups->getDatagroups() as $webshopContentGroup) :
                    $children->addChild($webshopContentGroup->_('name'), 'admin/content/adminitem/adminList/?filter[datagroup]='.$webshopContentGroup->getId());
                endforeach;
            endif;

            $children->addLine();
            $children->addChild('Discounts', 'admin/shop/admindiscount/adminList');
            $children->addChild('PaymentTypes', 'admin/shop/adminpayment/adminList');
            $children->addChild('ShippingTypes', 'admin/shop/adminshipping/adminList');
            $children->addChild('Countries', 'admin/shop/admincountry/adminList');
            $children->addChild('Tax rates', 'admin/shop/admintaxrate/adminList');

            $children->addLine();
            $children->addChild('Settings', 'admin/setting/adminsetting/adminList?filter[name.'.
                Di::getDefault()->get('configuration')->getLanguageShort().
                ']=shop');
            $children->addChild('EAN management', 'admin/shop/adminean/adminList');
            $children->addChild('Stock check', 'admin/shop/adminstock/check');

            $adminMenu->addDropdown('Shop', $children);
        endif;
    }
}
