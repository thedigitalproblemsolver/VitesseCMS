<?php declare(strict_types=1);

namespace VitesseCms\Form;

use VitesseCms\Block\Repositories\BlockFormBuilderRepository;
use VitesseCms\Block\Repositories\BlockRepository;
use VitesseCms\Communication\Repositories\NewsletterRepository;
use VitesseCms\Core\AbstractModule;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Form\Repositories\RepositoryCollection;
use VitesseCms\Language\Repositories\LanguageRepository;
use Phalcon\DiInterface;

class Module extends AbstractModule
{
    public function registerServices(DiInterface $di, string $string = null)
    {
        parent::registerServices($di, 'Form');
        $di->setShared('repositories', new RepositoryCollection(
            new BlockFormBuilderRepository(new BlockRepository()),
            new LanguageRepository(),
            new DatagroupRepository(),
            new NewsletterRepository()
        ));
    }
}
