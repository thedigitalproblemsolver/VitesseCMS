<?php declare(strict_types=1);

namespace VitesseCms\Form\Models;

class Attributes
{
    /**
     * @var string
     */
    public $inputType;

    /**
     * @var bool
     */
    public $required;

    /**
     * @var bool
     */
    public $readonly;

    /**
     * @var string
     */
    public $inputClass;

    /**
     * @var array
     */
    public $options;

    /**
     * @var bool
     */
    public $multiple;

    /**
     * @var bool
     */
    public $multilang;

    /**
     * @var string
     */
    public $template;

    /**
     * @var bool
     */
    public $filemanager;

    /**
     * @var string
     */
    public $dataUrl;

    public function __construct()
    {
        $this->inputClass = '';
        $this->inputType = '';
        $this->required = false;
        $this->multiple = false;
        $this->multilang = false;
        $this->options = [];
        $this->template = '';
        $this->filemanager = false;
    }

    public function setInputType(string $inputType): Attributes
    {
        $this->inputType = $inputType;

        return $this;
    }

    public function setRequired(bool $required): Attributes
    {
        $this->required = $required;

        return $this;
    }

    public function setInputClass(string $inputClass): Attributes
    {
        $this->inputClass = $inputClass;

        return $this;
    }

    public function getInputClass(): string
    {
        return $this->inputClass;
    }

    public function setOptions(array $options): Attributes
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setMultiple(bool $multiple): Attributes
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function setReadonly(bool $readonly): Attributes
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function setMultilang(bool $multilang): Attributes
    {
        $this->multilang = $multilang;

        return $this;
    }

    public function setTemplate(string $template): Attributes
    {
        $this->template = $template;

        return $this;
    }

    public function setFilemanager(bool $filemanager): Attributes
    {
        $this->filemanager = $filemanager;

        return $this;
    }

    public function setDataUrl(string $dataUrl): Attributes
    {
        $this->dataUrl = $dataUrl;

        return $this;
    }
}
