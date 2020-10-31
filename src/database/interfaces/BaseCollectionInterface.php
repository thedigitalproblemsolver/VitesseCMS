<?php declare(strict_types=1);

namespace VitesseCms\Database\Interfaces;

use VitesseCms\Core\Interfaces\BaseObjectInterface;
use Phalcon\Mvc\CollectionInterface;

interface BaseCollectionInterface extends BaseObjectInterface, CollectionInterface
{
    public function setPublished(bool $published): BaseCollectionInterface;

    public function isPublished(): bool;
}
