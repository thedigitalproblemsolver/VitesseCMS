<?php

namespace VitesseCms\Joomla\Helpers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Language\Models\Language;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\UrlUtil;
use VitesseCms\Craftbeershirts\Import\Helpers\JoomlaImportHelper;
use VitesseCms\Joomla\Models\K2Category;
use VitesseCms\Joomla\Models\K2ExtraFields;
use VitesseCms\Sef\Factories\RedirectFactory;
use Phalcon\Di;
use Phalcon\Utils\Slug;

/**
 * Class K2ImportHelper
 */
class K2ImportHelper
{

    static $extrafieldsArray = [];

    /**
     * @param BaseObjectInterface $joomlaItem
     * @param Item $item
     * @param string $slugPost
     * @param string $itemImageField
     *
     * @return Item
     */
    public static function bindImage(
        BaseObjectInterface $joomlaItem,
        Item $item,
        string $slugPost = '',
        string $itemImageField = 'image'
    ): Item {
        $k2ImageHash = md5('Image' . $joomlaItem->_('id'));
        $url = 'https://craftbeershirts.nl/media/items/' . Di::getDefault()->get('config')->get('account') . '/src/';
        if (UrlUtil::exists($url . $k2ImageHash . '.png')) :
            $targetpath = Di::getDefault()->get('config')->get('uploadDir');
            if (DirectoryUtil::exists($targetpath, true)) :
                $newFilename = Slug::generate($item->_('name') . $slugPost) . '.png';
                file_put_contents(
                    $targetpath . $newFilename,
                    file_get_contents($url . $k2ImageHash . '.png')
                );
                $item->set($itemImageField, $newFilename);

                $redirect = RedirectFactory::create(
                    '/media/items/' . Di::getDefault()->get('config')->get('account') . '/src/' . $k2ImageHash . '.png',
                    '/uploads/craftbeershirts/' . $newFilename,
                    null,
                    true
                );
                $redirect->save();
            else :
                die('falied to create directory');
            endif;
        endif;

        return $item;
    }

    /**
     * @param BaseObjectInterface $joomlaItem
     * @param Item $item
     * @param array $bindMap
     * @param string $baseLanguageShort
     *
     * @return Item
     */
    public static function bindExtrafields(
        BaseObjectInterface $joomlaItem,
        Item $item,
        array $bindMap,
        string $baseLanguageShort = 'nl'
    ): Item {
        $category = K2Category::findFirst("id = " . $joomlaItem->_('catid'));
        $extrafields = K2ExtraFields::getFieldsOfGroup($category->_('extraFieldsGroup'));
        self::$extrafieldsArray = [];
        foreach ($bindMap as $map) :
            if (isset($extrafields[$map['from']])) :
                if ($map['multilang']) :
                    die('verwerk multilang');
                    $languageParsed = [$baseLanguageShort];
                    $item->set(
                        $map['to'],
                        $joomlaItem->_($map['from']),
                        $map['multilang'],
                        $baseLanguageShort
                    );
                    Language::setFindPublished(false);
                    /** @var Language $language */
                    foreach (Language::findAll() as $language) :
                        if (!in_array($language->_('short'), $languageParsed)) :
                            /** @var BaseObjectInterface $translatedItem */
                            $translatedItem = clone $joomlaItem;
                            if (isset($map['emptyBeforeTranslate'])) :
                                $translatedItem->set(
                                    $map['from'],
                                    '',
                                    $map['multilang'],
                                    $language->_('short')
                                );
                            endif;

                            $translatedItem = JoomfishHelper::translateObject(
                                $translatedItem,
                                $joomlaTable,
                                JoomlaImportHelper::getLanguageId((string)$language->getId()),
                                $language->_('short')
                            );

                            $item->set(
                                $map['to'],
                                $translatedItem->_($map['from'], $language->_('short')),
                                $map['multilang'],
                                $language->_('short')
                            );
                        endif;
                    endforeach;
                else :
                    if (isset($map['datagroup'])) :
                        $value = self::getExtraFieldValue($extrafields[$map['from']]->name, $joomlaItem);
                        if(empty($value)) :
                            $item->set($map['to'], '');
                        else :
                            Item::setFindValue('datagroup', $map['datagroup']);
                            Item::setFindValue('name.nl', $value);
                            $datagroupItem = Item::findFirst();
                            if (!$datagroupItem) :
                                //var_dump($datagroupItem->_('name'));
                                echo '<pre>';
                                var_dump($joomlaItem->title);
                                var_dump($extrafields[$map['from']]->name);
                                var_dump($map['datagroup']);
                                var_dump($value);
                                echo '<br />';
                                die('extrafield met datagroup niet gevonden');
                            else :
                                $item->set($map['to'], (string)$datagroupItem->getId());
                            endif;
                        endif;
                    else :
                        $value = self::getExtraFieldValue($extrafields[$map['from']]->name, $joomlaItem);
                        switch ($value) :
                            case 'ja':
                                $item->set($map['to'], true);
                                break;
                            case 'nee':
                                $item->set($map['to'], false);
                                break;
                            default:
                                $item->set($map['to'], $value);
                                break;
                        endswitch;
                    endif;

                endif;
            endif;
        endforeach;

        return $item;
    }

    /**
     * @param string $extrafieldName
     * @param BaseObjectInterface $joomlaItem
     *
     * @return string
     */
    public static function getExtraFieldValue(string $extrafieldName, BaseObjectInterface $joomlaItem): string
    {
        self::parseExtraFields($joomlaItem);
        $field = k2ExtraFields::findFirstByName($extrafieldName);
        if (isset(self::$extrafieldsArray[$field->id])) :
            $json = json_decode($field->value);
            foreach ($json as $value) :
                if (
                    $value->name !== null
                    && $value->value == self::$extrafieldsArray[$field->id]
                ) :
                    return $value->name;
                endif;
            endforeach;

            return self::$extrafieldsArray[$field->id];
        endif;

        return '';
    }

    /*public function getImage() {
        $sImageBase = 'media/items/'.$this->config->site->dirPrefix.'/src/'.md5("Image".$this->id);
        if( is_file(SITE_PATH.$sImageBase.'.png') ) :
            $sImgUrl = SITE_URL.$sImageBase.'.png';
        endif;
        if( is_file(SITE_PATH.$sImageBase.'.jpg') ) :
            $sImgUrl = SITE_URL.$sImageBase.'.jpg';
        endif;
        return $sImgUrl;
    }*/

    /**
     * @param Item $item
     */
    public static function parseExtraFields(BaseObjectInterface $joomlaItem)
    {
        if (
            count(self::$extrafieldsArray) == 0
            && !empty($joomlaItem->_('extra_fields'))
        ) :
            $itemFields = json_decode($joomlaItem->_('extra_fields'));
            $extrafields = [];
            foreach ($itemFields as $itemField) :
                $extrafields[$itemField->id] = $itemField->value;
            endforeach;
            self::$extrafieldsArray = $extrafields;
        endif;
    }
}
