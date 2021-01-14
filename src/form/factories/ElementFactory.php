<?php declare(strict_types=1);

namespace VitesseCms\Form\Factories;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Form\Utils\ElementUiUtil;
use Phalcon\Di;
use Phalcon\Forms\Element;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Email;
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\ElementInterface;
use Phalcon\Validation\Validator\File as FileValidator;

class ElementFactory
{
    public static function button(string $type, string $label, string $name = '', array $attributes = []): ElementInterface
    {
        switch ($type) :
            case 'submit':
                $disabled = isset($attributes['disabled']) ? 'disabled' : '';
                $useRecaptcha = $attributes['useRecaptcha'] ?? false;

                return new Submit($label, [
                    'inputClass' => 'btn btn-success btn-block',
                    'template' => 'button',
                    'buttonType' => 'submit',
                    'disabled' => $disabled,
                    'useRecaptcha' => $useRecaptcha
                ]);
                break;
            case 'reset':
                return new Submit($label, [
                    'inputClass' => 'btn btn-outline-danger btn-block',
                    'template' => 'button',
                    'buttonType' => 'reset'
                ]);
                break;
            case 'empty':
                return new Submit($label, [
                    'inputClass' => 'btn btn-outline-danger btn-block btn-form-emtpy',
                    'template' => 'button',
                    'buttonType' => 'reset',
                ]);
                break;
            case 'button':
            default:
                return (new Submit($label, [
                    'inputClass' => 'btn btn-outline-info btn-block',
                    'template' => 'button',
                    'buttonType' => 'button',
                    'elementId' => $name,
                ]));
                break;
        endswitch;
    }

    public static function checkbox(string $label, string $name, array $attributes = []): Check
    {
        $element = new Check($name, $attributes);
        self::parseDefaults($element, $label, 'checkbox');

        if ($element->getDefault() === '') :
            $element->setDefault(true);
        endif;

        return $element;
    }

    public static function email(
        string $label,
        string $name,
        array $attributes = []
    ): Email
    {
        $element = new Email($name, $attributes);
        self::parseDefaults($element, $label, 'email');

        return $element;
    }

    public static function file(
        string $label,
        string $name,
        array $attributes = []
    ): File
    {
        if (isset($attributes['filemanager']) && $attributes['filemanager'] === true) :
            $attributes['readonly'] = 'readonly';
        endif;

        $element = new File($name, $attributes);
        ElementHelper::setDefaults($element, $label);
        ElementHelper::setValue($element);

        if (
            isset($attributes['allowedTypes'])
            && is_array($attributes['allowedTypes'])
            && (
                Di::getDefault()->get('request')->getPost() === 0
                || (
                    isset($_FILES[$name])
                    && $_FILES[$name]['name'] !== ''
                )
            )
        ) :
            $element->addValidator(
                new FileValidator([
                    'allowedTypes' => $attributes['allowedTypes'],
                    'messageType' => 'Allowed file types are :types',
                ])
            );
        endif;

        $element = ElementUiUtil::setTemplate($element, 'file');

        return $element;
    }

    public static function hidden(string $name, array $attributes = []): Hidden
    {
        $element = new Hidden($name, $attributes);
        ElementHelper::setValue($element);
        $element = ElementUiUtil::setTemplate($element, 'hidden');

        return $element;
    }

    public static function html(array $attributes, string $prefix = 'html'): Hidden
    {
        $name = $prefix . '_' . uniqid('', true);

        $element = new Hidden($name);
        if (isset($attributes['html'])) :
            $element->setDefault($attributes['html']);
        endif;
        $element->setLabel($name);

        return $element;
    }

    public static function number(
        string $label,
        string $name,
        array $attributes = []
    ): Numeric
    {
        $element = new Numeric($name, $attributes);
        self::parseDefaults($element, $label, 'number');

        return $element;
    }

    public static function password(
        string $label,
        string $name,
        array $attributes = []
    ): Password
    {
        $element = new Password($name, $attributes);
        self::parseDefaults($element, $label, 'password');

        return $element;
    }

    /**
     * @param string $label
     * @param string $name
     * @param array $attributes
     *
     * @return Select
     * @deprecated should us addDropdown from Form
     *
     */
    public static function select(
        string $label,
        string $name,
        array $attributes = []
    ): Select
    {
        $options = [];
        if (isset($attributes['options'])) :
            $options = $attributes['options'];
            unset($attributes['options']);
        endif;

        $newOptions = [];
        if (!isset($attributes['noEmptyText'])) :
            $newOptions[] = [
                'value' => '',
                'label' => '%ADMIN_NO_SELECTION%',
                'selected' => false,
            ];
        endif;

        if (\is_string($options)) :
            $options::addFindOrder('name');
            $items = $options::Findall();
            /** @var AbstractCollection $item */
            foreach ($items as $item) :
                $newOptions[] = [
                    'value' => (string)$item->getId(),
                    'label' => ucfirst($item->_('name')),
                    'selected' => false,
                ];
            endforeach;
        elseif (
            \count($options) > 0
            && isset($options[0])
            && \is_object($options[0])
            && substr_count(get_class($options[0]), 'Models') > 0
        ) :
            /** @var AbstractCollection $option */
            foreach ($options as $option) :
                $newOptions[] = [
                    'value' => (string)$option->getId(),
                    'label' => $option->_('name'),
                    'selected' => false,
                ];
            endforeach;
        else :
            $newOptions = $options;
        endif;
        $options = $newOptions;

        if (isset($attributes['value'])) :
            foreach ($options as $key => $option) :
                if ($option['value'] === $attributes['value']) :
                    $options[$key]['selected'] = true;
                endif;
            endforeach;
        endif;

        $element = new Select($name, $options, $attributes);
        $element->setAttribute('options', $options);
        self::parseDefaults($element, $label, 'select');

        return $element;
    }

    public static function dropdown(
        string $label,
        string $name,
        Attributes $attributes
    ): Select
    {
        $element = new Select($name, $attributes->getOptions(), (array)$attributes);
        $element->setAttribute('options', $attributes->getOptions());
        self::parseDefaults($element, $label, 'select');

        return $element;
    }

    public static function tel(
        string $label,
        string $name,
        array $attributes = []
    ): Text
    {
        $element = new Text($name, $attributes);
        self::parseDefaults($element, $label, 'tel');

        return $element;
    }

    public static function date(
        string $label,
        string $name,
        ?Attributes $attributes = null
    ): Date
    {
        if ($attributes === null) :
            $attributes = new Attributes();
        endif;
        $attributes->setInputType('date');

        $element = new Date($name, (array)$attributes);
        self::parseDefaults($element, $label, 'text');

        return $element;
    }

    public static function text(
        string $label,
        string $name,
        array $attributes = []
    ): Text
    {
        $element = new Text($name, $attributes);
        self::parseDefaults($element, $label, 'text');

        return $element;
    }

    public static function textarea(
        string $label,
        string $name,
        array $attributes = []
    ): TextArea
    {
        $element = new TextArea($name, $attributes);
        self::parseDefaults($element, $label, 'textarea');

        return $element;
    }

    public static function url(
        string $label,
        string $name,
        array $attributes = []
    ): Text
    {
        $element = new Text($name, $attributes);
        self::parseDefaults($element, $label, 'url');

        return $element;
    }

    public static function parseDefaults(
        Element $element,
        string $label,
        string $template = ''
    ): void
    {
        ElementHelper::setDefaults($element, $label);
        ElementHelper::setRequired($element);
        ElementHelper::setValue($element);
        if (!empty($template)) :
            $element = ElementUiUtil::setTemplate($element, $template);
        endif;
    }
}
