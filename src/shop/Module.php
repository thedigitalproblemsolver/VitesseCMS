<?php declare(strict_types=1);

namespace VitesseCms\Shop;

use VitesseCms\Communication\Services\MailchimpService;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\AbstractModule;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Shop\Repositories\DiscountRepository;
use VitesseCms\Shop\Repositories\OrderRepository;
use VitesseCms\Shop\Repositories\OrderStateRepository;
use VitesseCms\Shop\Repositories\PaymentRepository;
use VitesseCms\Shop\Repositories\RepositoryCollection;
use VitesseCms\Shop\Repositories\ShippingTypeRepository;
use VitesseCms\Shop\Repositories\ShopperRepository;
use Phalcon\DiInterface;

class Module extends AbstractModule
{
    public function registerServices(DiInterface $di, string $string = null)
    {
        parent::registerServices($di, 'Shop');

        $di->setShared('mailchimp', new MailchimpService(
            $di->get('session'),
            $di->get('setting'),
            $di->get('url'),
            $di->get('configuration')
        ));

        $di->setShared('repositories', new RepositoryCollection(
            new ShippingTypeRepository(),
            new ItemRepository(),
            new OrderRepository(),
            new ShopperRepository(),
            new PaymentRepository(),
            new DiscountRepository(),
            new OrderStateRepository(),
            new DatagroupRepository(),
            new DatafieldRepository()
        ));
    }
}
