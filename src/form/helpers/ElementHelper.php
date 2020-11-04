<?php declare(strict_types=1);

namespace VitesseCms\Form\Helpers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Factories\ObjectFactory;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Language\Helpers\LanguageHelper;
use Phalcon\Di;
use Phalcon\Filter;
use Phalcon\Forms\ElementInterface;
use Phalcon\Forms\Form;
use Phalcon\Validation\Validator\PresenceOf;
use ArrayIterator;

class ElementHelper
{
    /**
     * @var Form
     */
    public static $form;

    public static function setDefaults(ElementInterface $element, $label): void
    {
        $element->setLabel($label);
        $element->setAttribute('id', (new Filter())
            ->sanitize(
                $element->getName(),
                Filter::FILTER_ALPHANUM
            )
        );
    }

    public static function setRequired(ElementInterface $element): void
    {
        if ($element->getAttribute('required')) :
            $element->addValidators([
                new PresenceOf([
                    'message' => LanguageHelper::_(
                        'FORM_REQUIRED_MESSAGE',
                        [strtolower($element->getLabel())]
                    ),
                ]),
            ]);
        endif;
    }

    public static function setValue(ElementInterface $element): void
    {
        $request = Di::getDefault()->get('request');

        if (
            substr_count($element->getName(), '[]') === 0
            && substr_count($element->getName(), '[') > 0
        ) :
            $fields = explode('[', str_replace(']', '', $element->getName()));
            if (isset($request->get($fields[0])[$fields[1]])) :
                $element->setDefault($request->get($fields[0])[$fields[1]]);
            endif;
        endif;

        if (
            $element->getAttribute('multiple')
            && substr_count($element->getName(), '[]') === 1
        ) :
            $fieldName = str_replace('[]', '', $element->getName());
            if ($request->getPost('items')) :
                $element->setDefault($request->getPost('items'));
            elseif (is_object(self::$form->getEntity())) :
                $element->setDefault(self::$form->getEntity()->_($fieldName));
            endif;
        endif;

        if (
            empty($element->getDefault())
            && !empty($element->getAttribute('value'))
        ) :
            $element->setDefault($element->getAttribute('value'));
        endif;
    }

    public static function arrayToSelectOptions(
        array $array,
        array $selected = [],
        bool $nameWithParents = false
    ): array {
        $selectedCheck = ObjectFactory::create();
        foreach ($selected as $value) :
            if (is_array($value)) :
                foreach ($value as $key => $id) :
                    $selectedCheck->set($id, true);
                endforeach;
            elseif(is_string($value)) :
                $selectedCheck->set($value, true);
            endif;
        endforeach;

        $options = [[
            'value'    => '',
            'label'    => '%FORM_CHOOSE_AN_OPTION%',
            'selected' => false,
        ]];
        if (
            count($array) > 0
            && isset($array[0])
            && is_object($array[0])
            && substr_count(get_class($array[0]), 'Models') > 0
        ) :
            /** @var Item $item */
            foreach ($array as $item) :
                $label = [$item->getNameField()];
                if($nameWithParents && $item->hasParent()) {
                    $label = [];
                    $pathFromRootItems = ItemHelper::getPathFromRoot($item);
                    /** @var AbstractCollection $pathFromRootItem */
                    foreach ($pathFromRootItems as $pathFromRootItem) :
                        $label[] = $pathFromRootItem->getNameField();
                    endforeach;
                }
                $options[] = [
                    'value'    => (string)$item->getId(),
                    'label'    => implode(' > ', $label),
                    'selected' => $selectedCheck->_((string)$item->getId()),
                ];
            endforeach;
        else :
            foreach ($array as $value => $label) :
                $options[] = [
                    'value'    => $value,
                    'label'    => $label,
                    'selected' => $selectedCheck->_((string) $value),
                ];
            endforeach;
        endif;

        return $options;
    }

    public static function modelIteratorToOptions(ArrayIterator $iterator): array
    {
        $options = [[
            'value'    => '',
            'label'    => '%FORM_CHOOSE_AN_OPTION%',
            'selected' => false,
        ]];

        while ($iterator->valid()) :
            /** @var AbstractCollection $item */
            $item = $iterator->current();

            $options[] = [
                'value'    => (string)$item->getId(),
                'label'    => $item->getNameField(),
                'selected' => null,
            ];

            $iterator->next();
        endwhile;

        return $options;
    }

    public static function parseTextNameAttribute(string $name): array
    {
        $return = explode('[',$name);
        $return[1] = str_replace(']','',$return[1]);

        return $return;
    }
}
