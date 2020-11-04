<?php declare(strict_types=1);

namespace VitesseCms\Import;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\AbstractModule;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Import\Repositories\ImportDatafieldRepository;
use VitesseCms\Import\Repositories\RepositoryCollection;
use VitesseCms\Import\Repositories\ImportTypeRepository;
use VitesseCms\Language\Repositories\LanguageRepository;
use Phalcon\DiInterface;

class Module extends AbstractModule
{
    public function registerServices(DiInterface $di, string $string = null)
    {
        parent::registerServices($di, 'Import');

        $di->setShared('repositories', new RepositoryCollection(
            new ImportTypeRepository(),
            new LanguageRepository(),
            new DatagroupRepository(),
            new ItemRepository(),
            new ImportDatafieldRepository()
        ));
    }
}
