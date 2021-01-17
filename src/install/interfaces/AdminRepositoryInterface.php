<?php declare(strict_types=1);

namespace VitesseCms\Install\Interfaces;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;

/**
 * @property ItemRepository $item
 * @property DatagroupRepository $datagroup
 * @property DatafieldRepository $datafield
 */
interface AdminRepositoryInterface
{
}
