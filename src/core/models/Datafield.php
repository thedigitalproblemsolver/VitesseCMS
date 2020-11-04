<?php declare(strict_types=1);

namespace VitesseCms\Core\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Core\Utils\SystemUtil;
use VitesseCms\Form\Interfaces\AbstractFormInterface;

class Datafield extends AbstractCollection
{
    /**
     * @var string
     */
    public $calling_name;

    /**
     * @var string
     */
    public $datagroup;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $model;

    public function getTypes() : array
    {
        $files = $types = [];

        $directories = [
            $this->di->config->get('rootDir') . 'src/field/models/',
            $this->di->config->get('accountDir') . 'src/field/models/',
        ];

        foreach ($directories as $directory) :
            $files = array_merge($files, DirectoryUtil::getFilelist($directory));
        endforeach;
        ksort($files);

        foreach ($files as $path => $file) :
            $name = FileUtil::getName($file);
            $className = SystemUtil::createNamespaceFromPath($path);
            $types[$className] = substr($name, 5, strlen($name));
        endforeach;

        return $types;
    }

    public function getTemplates(): array
    {
        $templates = [];
        $dirs = DirectoryUtil::getChildren(
            $this->di->config->get('rootDir') . 'template/core/views/fields/'
        );
        foreach ($dirs as $name => $path) :
            $templates[$path] = strtolower($name);
        endforeach;

        return $templates;
    }

    public function renderFilter(AbstractFormInterface $filter ): void
    {
        $object = $this->getClass($this->_('type'));
        /** @noinspection PhpUndefinedMethodInspection */
        (new $object())->renderFilter($filter, $this);
    }

    public function renderAdminlistFilter(AbstractFormInterface $filter ): void
    {
        $object = $this->getClass($this->_('type'));
        /** @noinspection PhpUndefinedMethodInspection */
        (new $object())->renderAdminlistFilter($filter, $this);
    }

    public function getSlugPart(AbstractCollection $item, string $languageShort) : string
    {
        $object = $this->getClass($this->_('type'));
        /** @noinspection PhpUndefinedMethodInspection */
        return (new $object())->renderSlugPart($item, $languageShort, $this);
    }

    public function getSearchValue(AbstractCollection $item, string $languageShort)
    {
        $object = $this->getClass($this->_('type'));
        /** @noinspection PhpUndefinedMethodInspection */
        return (new $object())->getSearchValue($item, $languageShort, $this);
    }

    public function getCallingName(): string
    {
        return $this->calling_name??'';
    }

    public function isMultilang(): bool
    {
        return (bool)$this->_('multilang');
    }

    public function getDatagroup(): ?string
    {
        return $this->datagroup;
    }

    public function getFieldType(): ?string
    {
        if($this->type !== null) {
            return array_reverse(explode('\\',$this->type))[0];
        }
        return $this->type;
    }

    public function getClass(): string
    {
        if (substr_count($this->type,'Modules'  )) :
            return str_replace('Modules','VitesseCms',$this->type);
        endif;

        if (substr_count($this->type,'VitesseCms'  )) :
            return $this->type;
        endif;

        return 'VitesseCms\\Field\\Models\\'.$this->type;
    }

    public function getModel(): string
    {
        if (substr_count($this->model,'Modules'  )) :
            return str_replace('Modules','VitesseCms',$this->model);
        endif;

        return 'VitesseCms\\Field\\Models\\'.$this->model;
    }
}
