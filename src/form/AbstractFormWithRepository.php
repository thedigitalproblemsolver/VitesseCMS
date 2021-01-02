<?php declare(strict_types=1);

namespace VitesseCms\Form;

use VitesseCms\Database\Interfaces\BaseCollectionInterface;
use VitesseCms\Database\Interfaces\BaseRepositoriesInterface;
use VitesseCms\Form\Interfaces\FormWithRepositoryInterface;

abstract class AbstractFormWithRepository extends AbstractForm implements FormWithRepositoryInterface
{
    abstract function buildForm(): FormWithRepositoryInterface;

    public function setRepositories(BaseRepositoriesInterface $baseRepositories): FormWithRepositoryInterface
    {
        $this->repositories = $baseRepositories;

        return $this;
    }

    public function setEntity($entity) {
        $this->_entity = $entity;
    }
}
