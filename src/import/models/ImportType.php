<?php declare(strict_types=1);

namespace VitesseCms\Import\Models;

use Phalcon\Tag;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Admin\Utils\AdminUtil;
use VitesseCms\Import\Helpers\AbstractImportHelperInterface;

class ImportType extends AbstractCollection
{
    /**
     * @var AbstractImportHelperInterface
     */
    protected $importHelper;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $language;

    /**
     * @var string
     */
    public $datagroup;

    /**
     * @var string
     */
    public $imageFolder;

    public function afterFetch()
    {
        parent::afterFetch();

        if (AdminUtil::isAdminPage()) :
            $this->adminListName = Tag::linkTo([
                    'action' => 'import/index/index/' . (string)$this->getId(),
                    'target' => '_blank',
                    'class' => 'fa fa-external-link'
                ]) . '&nbsp;' .
                Tag::linkTo(
                    [
                        'action' => 'admin/import/admincontent/edit/' . (string)$this->getId(),
                        'class' => 'openmodal',
                        'text' => $this->_('name')
                    ]
                );
        endif;
    }

    public function getImportHelper(): ?AbstractImportHelperInterface
    {
        if ($this->type === null):
            return null;
        endif;

        return new $this->type;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getDatagroup(): string
    {
        return $this->datagroup;
    }

    public function getImageFolder(): string
    {
        return $this->imageFolder ?? '';
    }

    public function hasType(): bool
    {
        return $this->type !== null;
    }

    public function getTypeClass(): string
    {
        if (substr_count($this->type, 'VitesseCms\\Import\\Helpers')) :
            return $this->type;
        endif;

        if (substr_count($this->type, 'Modules')) :
            return str_replace('Modules', 'VitesseCms', $this->type);
        endif;

        return 'VitesseCms\\Field\\Models\\' . $this->type;
    }
}
