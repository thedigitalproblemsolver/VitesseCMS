<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Helpers\ItemHelper;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Field\AbstractField;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use Phalcon\Utils\Slug;

class FieldDatagroup extends AbstractField
{
    public static function beforeMaincontent(Item $item, Datafield $datafield): void
    {
        $test = $item->_($datafield->getCallingName());
        if (is_string($test) && MongoUtil::isObjectId($test)) :
            $object = Item::findById($item->_($datafield->getCallingName()));
            if ($object) :
                $item->set($datafield->getCallingName().'Display', $object);
            endif;
        endif;
    }

    public function buildItemFormElement(AbstractForm $form, Datafield $datafield, AbstractCollection $data = null)
    {
        Item::setFindValue('datagroup', $datafield->getDatagroup());
        if ($datafield->_('itemParent')) :
            Item::setFindValue('parentId', $datafield->_('itemParent'));
        endif;

        $options = [];
        /** @var Item $item */
        foreach (Item::findAll() as $item) :
            $value = (string)$item->getId();
            $name = [$item->getNameField()];

            if ($item->_('formValue')) :
                $value = $item->_('formValue');
            endif;

            if ($item->getParentId()) :
                $name = [];
                $pathItems = ItemHelper::getPathFromRoot($item);
                /** @var Item $pathItem */
                foreach ($pathItems as $pathItem) :
                    $name[] = $pathItem->getNameField();
                endforeach;
            endif;
            $options[$value] = implode(' > ', $name);
        endforeach;
        $options = array_flip($options);
        ksort($options);
        $options = array_flip($options);

        $this->setOption('options', ElementHelper::arrayToSelectOptions($options));

        if ($datafield->_('multiple')) :
            $this->setOption('multiple', true);
            $this->setOption('inputClass', 'select2');
        endif;

        $form->_(
            'select',
            $datafield->_('name'),
            $datafield->_('calling_name'),
            $this->getOptions()
        );
    }

    public function renderFilter(AbstractFormInterface $filter, Datafield $datafield): void
    {
        $fieldName = $this->getFieldname($datafield);

        Item::addFindOrder('name');
        Item::setFindValue('datagroup', $datafield->getDatagroup());
        if ($datafield->_('itemParent')) :
            Item::setFindValue('parentId', $datafield->_('itemParent'));
        endif;
        $this->setOption('options', Item::findAll());
        $this->setOption('multiple', true);
        $this->setOption('noEmptyText', true);
        $this->setOption('inputClass', 'select2');

        $filter->_(
            'select',
            $datafield->getNameField(),
            $fieldName.'[]',
            $this->getOptions()
        );
    }

    public function renderAdminlistFilter(AbstractFormInterface $filter, Datafield $datafield): void
    {
        $fieldName = $this->getFieldname($datafield);

        Item::addFindOrder('name');
        Item::setFindValue('datagroup', $datafield->getDatagroup());
        if ($datafield->_('itemParent')) :
            Item::setFindValue('parentId', $datafield->_('itemParent'));
        endif;
        $this->setOption('options', Item::findAll());

        $filter->_(
            'select',
            $datafield->getNameField(),
            $fieldName,
            $this->getOptions()
        );
    }

    public function renderSlugPart(AbstractCollection $item, string $languageShort, Datafield $datafield): string
    {
        $datagroupItem = Item::findById($item->_($datafield->_('calling_name')));
        if ($datagroupItem) :
            $slug = $datagroupItem->_('slug', $languageShort);
            if (\is_string($slug)) :
                return Slug::generate($slug);
            endif;
        endif;

        return '';
    }
}
