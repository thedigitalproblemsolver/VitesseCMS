<?php declare(strict_types=1);

namespace VitesseCms\Import\Controllers;

use VitesseCms\Content\Factories\ItemFactory;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\AbstractController;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Core\Models\DatagroupIterator;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Import\Interfaces\RepositoriesInterface;
use VitesseCms\Import\Models\ImportDatafieldIterator;
use VitesseCms\Import\Models\ImportType;
use VitesseCms\Language\Models\Language;

class IndexController extends AbstractController implements RepositoriesInterface
{
    /**
     * @var bool
     * @deprecated find another way to parse this
     */
    protected $parseUpdateOnly;

    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function IndexAction(): void
    {
        set_time_limit(300);
        $redirect = true;
        $this->parseUpdateOnly = true;

        if ($this->dispatcher->getParam(0)):
            $importType = $this->repositories->importType->getById($this->dispatcher->getParam(0));
            if ($importType === null) {
                echo 'no importtype find';
                die();
            }

            $importHelper = $importType->getImportHelper();
            if ($importHelper !== null) :
                $language = $this->repositories->language->getById($importType->getLanguage());
                $datagroup = $this->repositories->datagroup->getById($importType->getDatagroup());
                if ($importHelper->isNew()) :
                    $importHelper->processImport($importType);
                elseif (
                    $language !== null
                    && $datagroup !== null
                    && ($handle = fopen($importType->_('url'), 'rb')) !== false
                ) :
                    $fieldsToParse = $this->getFieldsToParse($importType, $datagroup);
                    $parsedUrl = $this->parseUrl($importType->_('url'));
                    $header = $parsedUrl['header'];
                    $importData = (array)$parsedUrl['importData'];
                    $uniqueFields = $this->getUniqueFields($fieldsToParse);
                    $headerNameField = $this->getNameField($fieldsToParse);

                    foreach ($importData as $data) :
                        $parentId = null;
                        $parentItem = $this->getItemFromDatagroupPath(
                            $this->repositories->datagroup->getPathFromRoot($datagroup),
                            $importType,
                            $data,
                            $header,
                            $language
                        );

                        if ($parentItem === null):
                            echo 'No parent item is found';
                            die();
                        endif;

                        $item = $this->getBaseItem(
                            $uniqueFields,
                            $language,
                            $data,
                            $header,
                            $importType,
                            $parentItem,
                            $headerNameField
                        );

                        $item = $this->parseFields(
                            $fieldsToParse,
                            $this->parseUpdateOnly,
                            $data,
                            $header,
                            $item,
                            $language
                        );

                        $item = $this->parseFieldsImportValue(
                            $item,
                            $fieldsToParse,
                            $this->parseUpdateOnly
                        );

                        $this->eventsManager->fire(Item::class . ':beforeModelSave', $item, $this);
                        $item->save();

                        if ($parentItem !== null):
                            $this->setParentItemImage($item, $parentItem);
                        endif;
                    endforeach;
                endif;

                $redirect = false;
            endif;
        endif;

        if ($redirect) :
            $this->redirect();
        endif;

        $this->view->disable();
    }

    protected function parseFields(
        ImportDatafieldIterator $fieldsToParse,
        bool $parseUpdateOnly,
        array $data,
        array $header,
        Item $item,
        Language $language
    ): Item
    {
        while ($fieldsToParse->valid()) :
            $datafield = $fieldsToParse->current();
            if (
                $parseUpdateOnly === false
                || ($parseUpdateOnly === true && $datafield->isUpdate())
            ) :
                $value = '';
                if (isset($header[$datafield->getHeader()], $data[$header[$datafield->getHeader()]])) :
                    $value = $data[$header[$datafield->getHeader()]];
                endif;

                if (empty($value)) :
                    $value = $datafield->getEmptyValue();
                endif;

                if ($datafield->isMultilang()) :
                    $item->set($datafield->getCallingName(), $value, true, $language->getShortCode());
                else :
                    $item->set($datafield->getCallingName(), $value);
                endif;
            endif;
            $fieldsToParse->next();
        endwhile;
        $fieldsToParse->rewind();

        return $item;
    }

    protected function setParentItemImage(Item $item, Item $parentItem): void
    {
        if (
            $parentItem->_('image') === ''
            && $item->_('image') !== ''
        ) :
            $parentItem->set('image', $item->_('image'));
            $parentItem->save();
        endif;
    }

    protected function parseFieldsImportValue(
        Item $item,
        ImportDatafieldIterator $fieldsToParse,
        $parseUpdateOnly
    ): Item
    {
        while ($fieldsToParse->valid()) :
            $datafield = $fieldsToParse->current();
            if (
                $parseUpdateOnly === false
                || ($parseUpdateOnly === true && $datafield->isUpdate())
            ) :
                if (!$datafield->isMultilang()) :
                    $this->eventsManager->fire($datafield->getFieldType() . ':parse', $item, $datafield);
                endif;
            endif;
            $fieldsToParse->next();
        endwhile;
        $fieldsToParse->rewind();

        return $item;
    }

    protected function getBaseItem(
        array $uniqueFields,
        Language $language,
        array $data,
        array $header,
        ImportType $importType,
        Item $parentItem,
        $headerNameField
    ): Item
    {
        foreach ($uniqueFields as $calling_name => $headerField) :
            Item::setFindValue(
                $calling_name . '.' . $language->getShortCode(),
                $data[$header[$headerField]]
            );
        endforeach;

        Item::setFindPublished(false);
        Item::setFindValue('datagroup', $importType->_('datagroup'));
        Item::setFindValue('parentId', (string)$parentItem->getId());
        $item = Item::findFirst();
        $this->parseUpdateOnly = true;
        if (!$item) :
            $item = ItemFactory::create(
                $data[$header[$headerNameField]],
                $importType->_('datagroup'),
                [],
                true,
                (string)$parentItem->getId()
            );
            $this->parseUpdateOnly = false;
        endif;

        return $item;
    }

    protected function getItemFromDatagroupPath(
        DatagroupIterator $categoryGroups,
        ImportType $importType,
        array $data,
        array $header,
        Language $language
    ): ?Item
    {
        $parentId = null;
        $parentItem = null;

        while ($categoryGroups->valid()) :
            $categoryGroup = $categoryGroups->current();
            $key = $categoryGroups->key();
            if ((string)$categoryGroup->getId() !== $importType->getDatagroup()) :
                $parentTitle = null;
                $parentItem = null;

                if (MongoUtil::isObjectId($importType->_('category_' . $key))) :
                    $item = $this->repositories->item->getById($importType->_('category_' . $key));
                    if ($item !== null) :
                        $parentTitle = $item->getNameField();
                    endif;
                else :
                    $parentTitle = $data[$header[$importType->_('category_' . $key)]];
                endif;

                if ($parentTitle !== null) :
                    $parentItem = $this->repositories->item->findFirst(
                        new FindValueIterator([
                            new FindValue('datagroup', (string)$categoryGroup->getId()),
                            new FindValue('parentId', $parentId),
                            new FindValue('name.' . $language->getShortCode(), $parentTitle),
                        ]),
                        false
                    );
                endif;

                if ($parentItem === null) :
                    $parentItem = ItemFactory::create(
                        $parentTitle,
                        (string)$categoryGroup->getId(),
                        [],
                        true,
                        $parentId
                    );
                    $parentItem->save();
                endif;
                $parentId = (string)$parentItem->getId();
            endif;
            $categoryGroups->next();
        endwhile;

        return $parentItem;
    }

    protected function getFieldsToParse(ImportType $importType, Datagroup $datagroup): ImportDatafieldIterator
    {
        $fieldsToParse = new ImportDatafieldIterator();
        foreach ((array)$datagroup->_('datafields') as $datafieldValue) :
            $datafield = $this->repositories->importDatafield->getById($datafieldValue['id']);
            if ($datafield !== null) :
                if (
                    $importType->_('importField_' . $datafield->getCallingName())
                    || $importType->_('importField_empty_' . $datafield->getCallingName())
                ) :
                    $datafield->setHeader(
                        $importType->_('importField_' . $datafield->getCallingName())
                    );
                    $datafield->setEmptyValue(
                        $importType->_('importField_empty_' . $datafield->getCallingName())
                    );
                    $datafield->setUpdate(
                        (bool)$importType->_('importField_update_' . $datafield->getCallingName())
                    );
                    $datafield->setUnique(
                        (bool)$importType->_('importField_unique_' . $datafield->getCallingName())
                    );
                    $datafield->setImageFolder($importType->getImageFolder());

                    $fieldsToParse->add($datafield);
                endif;
            endif;
        endforeach;

        return $fieldsToParse;
    }

    protected function parseUrl(string $url): array
    {
        $handle = fopen($url, 'rb');
        $httpHeaders = get_headers($url, 1);
        $header = $importData = [];
        switch (strtolower($httpHeaders['Content-Type'][1])) :
            case 'text/csv':
            case 'text/csv; charset=utf-8':
                while (($header = fgetcsv($handle, 3000, ',')) !== false) :
                    break;
                endwhile;

                $row = 0;
                while (($data = fgetcsv($handle, 3000, ',')) !== false) :
                    if ($row > 0) :
                        $importData[] = $data;
                    endif;
                    $row++;
                endwhile;
                fclose($handle);
                $header = array_flip($header);
                break;
            case 'application/json':
            case 'application/json; charset=utf-8':
                $importData = json_decode(file_get_contents($url));
                foreach ((array)$importData[0] as $name => $value) :
                    $header[] = $name;
                endforeach;
                $header = array_combine($header, $header);
                break;
        endswitch;

        return [
            'header' => $header,
            'importData' => $importData,
        ];
    }

    protected function getUniqueFields(ImportDatafieldIterator $fieldsToParse): array
    {
        $fields = [];

        while ($fieldsToParse->valid()) :
            $datafield = $fieldsToParse->current();
            if ($datafield->isUnique()) :
                $fields[$datafield->getCallingName()] = $datafield->getHeader();
            endif;
            $fieldsToParse->next();
        endwhile;
        $fieldsToParse->rewind();

        return $fields;
    }

    protected function getNameField(ImportDatafieldIterator $fieldsToParse): string
    {
        while ($fieldsToParse->valid()) :
            $datafield = $fieldsToParse->current();
            if ($datafield->getCallingName() === 'name') :
                $fieldsToParse->rewind();

                return $datafield->getHeader();
            endif;
            $fieldsToParse->next();
        endwhile;
        $fieldsToParse->rewind();

        return 'name';
    }
}
