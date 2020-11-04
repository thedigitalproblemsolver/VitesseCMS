<?php

namespace VitesseCms\Import\Helpers;

use VitesseCms\Import\Forms\ImportTypeForm;
use VitesseCms\Import\Models\ImportType;
use Phalcon\Http\Request\FileInterface;

/**
 * Class AbstractExportHelperInterface
 */
interface AbstractImportHelperInterface
{

    /**
     * @param FileInterface $file
     */
    public function setFile( FileInterface $file): void;

    /**
     * @param string $languageLocale
     */
    public function setLanguage(string $languageLocale): void;

    /**
     * @param string $class
     */
    public function setClass(string $class): void;

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void;

    /**
     * @param ImportType $importType
     */
    public function processImport(?ImportType $importType = null): void;

    /**
     * @return bool
     */
    public function isNew(): bool;

    /**
     * @param ImportTypeForm $form
     * @param ImportType $item
     */
    public static function buildAdminForm(ImportTypeForm $form, ImportType $item): void;
}
