<?php declare(strict_types=1);

namespace VitesseCms\User;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\AbstractModule;
use VitesseCms\User\Repositories\RepositoryCollection;
use VitesseCms\User\Repositories\UserRepository;
use Phalcon\DiInterface;

class Module extends AbstractModule
{
    public function registerServices(DiInterface $di, string $string = null)
    {
        parent::registerServices($di, 'User');
        $di->setShared('repositories', new RepositoryCollection(
            new UserRepository(),
            new ItemRepository()
        ));
    }
}
