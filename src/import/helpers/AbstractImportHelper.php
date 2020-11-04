<?php declare(strict_types=1);

namespace VitesseCms\Import\Helpers;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Core\Helpers\InjectableHelper;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Import\Forms\ImportTypeForm;
use VitesseCms\Import\Models\ImportType;
use VitesseCms\Language\Models\Language;
use Phalcon\Http\Request\FileInterface;

abstract class AbstractImportHelper extends AbstractInjectable implements AbstractImportHelperInterface
{
    /**
     * @var FileInterface
     */
    protected $file;

    /**
     * @var AbstractCollection
     */
    protected $class;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @var InjectableInterface
     */
    protected $di;

    /**
     * @var array
     */
    protected $fields;

    public function __construct()
    {
        $this->di = new InjectableHelper();
    }

    public function onConstruct() {
        $this->fields = [];
    }

    public function setFile( FileInterface $file): void
    {
        $this->file = $file;
    }

    public function setLanguage(string $locale): void
    {
        Language::setFindValue('locale', $locale);
        $this->language = Language::findFirst();
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public static function buildAdminForm(ImportTypeForm $form, ImportType $item): void
    {
    }

    public function isNew(): bool
    {
        return false;
    }
}
