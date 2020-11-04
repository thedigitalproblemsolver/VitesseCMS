<?php declare(strict_types=1);

namespace VitesseCms\Import\Repositories;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Language\Repositories\LanguageRepository;

class RepositoryCollection
{
    /**
     * @var ImportTypeRepository
     */
    public $importType;

    /**
     * @var LanguageRepository
     */
    public $language;

    /**
     * @var DatagroupRepository
     */
    public $datagroup;

    /**
     * @var ItemRepository
     */
    public $item;

    /**
     * @var ImportDatafieldRepository
     */
    public $importDatafield;

    public function __construct(
        ImportTypeRepository $importTypeRepository,
        LanguageRepository $languageRepository,
        DatagroupRepository $datagroupRepository,
        ItemRepository $itemRepository,
        ImportDatafieldRepository $importDatafieldRepository
    ) {
        $this->importType = $importTypeRepository;
        $this->language = $languageRepository;
        $this->datagroup = $datagroupRepository;
        $this->item = $itemRepository;
        $this->importDatafield = $importDatafieldRepository;
    }
}
