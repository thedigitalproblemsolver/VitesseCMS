<?php declare(strict_types=1);

namespace VitesseCms\Import\Interfaces;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Repositories\DatagroupRepository;
use VitesseCms\Import\Repositories\ImportDatafieldRepository;
use VitesseCms\Import\Repositories\ImportTypeRepository;
use VitesseCms\Language\Repositories\LanguageRepository;

/**
 * @property ImportTypeRepository $importType
 * @property LanguageRepository $language
 * @property DatagroupRepository $datagroup
 * @property ItemRepository $item
 * @property ImportDatafieldRepository $importDatafield
 */
interface RepositoryInterface
{
}
