<?php declare(strict_types=1);

namespace VitesseCms\Install\Repositories;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;

class AdminRepositoryCollection
{
    /**
     * @var ItemRepository
     */
    public $item;

    /**
     * @var DatagroupRepository
     */
    public $datagroup;

    /**
     * @var DatafieldRepository
     */
    public $datafield;

    public function __construct(
        ItemRepository $itemRepository,
        DatagroupRepository $datagroupRepository,
        DatafieldRepository $datafieldRepository
    ) {
        $this->item = $itemRepository;
        $this->datagroup = $datagroupRepository;
        $this->datafield = $datafieldRepository;
    }
}
