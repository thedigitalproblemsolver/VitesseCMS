<?php declare(strict_types=1);

namespace VitesseCms\Form\Helpers;

use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Utils\FormUtil;
use VitesseCms\Language\Repositories\LanguageRepository;
use VitesseCms\Media\Enums\AssetsEnum;
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\ElementInterface;
use Phalcon\Tag;

class ElementUiHelper implements InjectableInterface
{
    /**
     * @var LanguageRepository
     */
    protected $languageRepository;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    private function generatePreview(ElementInterface $element, string $key = null): string
    {
        if (get_class($element) === File::class) :
            $file = null;
            $value = $element->getValue();

            if (is_string($value)) :
                $file = FileUtil::getTag($value);
            elseif (is_array($value)) :
                if (isset($value[$key])) :
                    $file = FileUtil::getTag($value[$key]);
                endif;
            endif;

            if ($file !== null) :
                return '<div class="file-preview">'.$file.'</div>';
            endif;
        endif;

        return '';
    }

    public function renderElement(ElementInterface $element, AbstractForm $form): string
    {
        $inputClass = $element->getAttribute('inputClass', '');
        $entity = $form->getEntity();

        if (substr_count($inputClass, 'editor')) :
            $form->assets->load(AssetsEnum::EDITOR);
        endif;

        if (empty($element->getAttribute('template'))) :
            $element->setAttribute('template', 'text');
        endif;

        if ($element->getAttribute('multilang')) :
            return $this->renderMultiLanguageElement($element, $form);
        else :
            return $this->renderSingleLanguageElement($element, $form, $entity, $inputClass);
        endif;
    }

    private function renderMultiLanguageElement(ElementInterface $element, AbstractForm $form): string
    {
        $return = '';
        $element->setAttribute('multilang', null);
        $languageIterator = $this->languageRepository->findAll();

        while ($languageIterator->valid()) :
            $language = $languageIterator->current();
            $shortCode = $language->getShortCode();

            if (isset($element->getValue()[$shortCode])) :
                $element->setDefault($element->getValue()[$shortCode]);
            endif;

            if (get_class($element) === Select::class) :
                if ($element->getAttribute('multiple')) :
                    $element->setDefault($element->getValue());
                endif;

                $options = $element->getAttribute('options');
                foreach ($options as $key => $option) :
                    if (
                        isset($element->getDefault()[$shortCode])
                        && in_array($option['value'], $element->getDefault()[$shortCode], true)
                    ) :
                        $option['selected'] = true;
                    else :
                        $option['selected'] = false;
                    endif;
                    $options[$key] = $option;
                endforeach;
                $element->setAttribute('options', $options);
            endif;

            $return .= FormUtil::renderInputTemplate($element, $shortCode, $form);
            $return .= $this->generatePreview($element);
            $languageIterator->next();
        endwhile;

        return $return;
    }

    private function renderSingleLanguageElement(
        ElementInterface $element,
        AbstractForm $form,
        $entity,
        string $inputClass
    ): string {
        $return = '';

        if ($element->getAttribute('multiple')) :
            $fieldName = str_replace('[]', '', $element->getName());
            if ($entity) :
                $element->setDefault($entity->_($fieldName));
            endif;
        endif;

        if (substr_count($inputClass, 'select2-sortable') > 0) :
            $value = '';
            if (is_array($element->getValue())) :
                $value = implode(',', $element->getValue());
            endif;
            $return .= Tag::hiddenField([
                $element->getAttribute('id').'_select2Sortable',
                'value' => $value,
            ]);
        endif;

        if (
            is_object($entity) &&
            (
                $entity->_($element->getName()) === true
                || $entity->_($element->getName()) === '1'
            )
        ) :
            $element->setAttribute('checked', true);
        endif;

        if (
            is_object($entity)
            && get_class($element) === Text::class
            && substr_count($element->getName(), '[')
            && substr_count($element->getName(), ']')
        ) :
            $index = ElementHelper::parseTextNameAttribute(
                $element->getName()
            );
            $array = $entity->_($index[0]);

            if (isset($array[$index[1]])) :
                $element->setDefault(trim($array[$index[1]]));
            endif;
        endif;

        if (
            is_object($entity)
            && get_class($element) === Select::class
        ) :
            if (
                substr_count($element->getName(), '[')
                && substr_count($element->getName(), ']')
            ):
                $index = ElementHelper::parseTextNameAttribute(
                    $element->getName()
                );
                $array = $entity->_($index[0]);

                if (isset($array[$index[1]])) :
                    $element->setDefault(trim($array[$index[1]]));
                    $options = $element->getAttribute('options');
                    foreach ($options as $key => $option) :
                        if ($option['value'] === trim($array[$index[1]])) :
                            $options[$key]['selected'] = true;
                        endif;
                    endforeach;
                    $element->setAttribute('options', $options);
                endif;
            else:
                $value = $entity->_($element->getName());
                if (is_numeric($value)):
                    $value = (int)$value;
                endif;
                $options = $element->getAttribute('options');
                foreach ($options as $key => $option) :
                    if ($option['value'] === $value) :
                        $options[$key]['selected'] = true;
                    endif;
                endforeach;
                $element->setAttribute('options', $options);
            endif;
        endif;

        $return .= FormUtil::renderInputTemplate($element, null, $form);
        $return .= $this->generatePreview($element);

        return $return;
    }
}
