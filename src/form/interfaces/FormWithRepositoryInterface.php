<?php declare(strict_types=1);

namespace VitesseCms\Form\Interfaces;

use VitesseCms\Database\Interfaces\BaseCollectionInterface;
use VitesseCms\Database\Interfaces\BaseRepositoriesInterface;

interface FormWithRepositoryInterface
{
    public function buildForm(): FormWithRepositoryInterface;

    public function setRepositories(BaseRepositoriesInterface $baseRepositories): FormWithRepositoryInterface;

    public function setEntity($entity);
}
