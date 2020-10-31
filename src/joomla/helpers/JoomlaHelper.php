<?php

namespace VitesseCms\Joomla\Helpers;

use VitesseCms\Content\Factories\ItemFactory;
use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Core\Interfaces\FactoryInterface;
use VitesseCms\Language\Models\Language;
use VitesseCms\Core\Utils\SystemUtil;
use VitesseCms\Craftbeershirts\Import\Helpers\JoomlaImportHelper;
use VitesseCms\User\Factories\UserFactory;
use VitesseCms\User\Models\User;

/**
 * Class JoomlaHelper
 */
class JoomlaHelper
{
    /**
     * @param string $joomlaId
     * @param string $datagroup
     * @param string $fieldname
     *
     * @return Item
     */
    public static function getCoreItemById(
        string $joomlaId,
        string $datagroup,
        string $fieldname = 'joomlaId'
    ): Item
    {
        Item::setFindPublished(false);
        Item::setFindValue($fieldname, $joomlaId);
        Item::setFindValue('datagroup', $datagroup);
        $item = Item::findFirst();

        if(!$item) :
            Item::setFindPublished(false);
            Item::setFindValue($fieldname, (int)$joomlaId);
            Item::setFindValue('datagroup', $datagroup);
            $item = Item::findFirst();
            if(!$item) :
                $item = ItemFactory::create($datagroup);
            endif;
        endif;

        return $item;
    }

    /**
     * @param string $joomlaId
     * @param string $datagroup
     * @param string $fieldname
     *
     * @return Item
     */
    public static function getCoreUserById(
        string $joomlaId,
        string $fieldname = 'joomlaId'
    ): User
    {
        User::setFindPublished(false);
        User::setFindValue($fieldname, $joomlaId);
        $user = User::findFirst();

        if(!$user) :
            User::setFindPublished(false);
            User::setFindValue($fieldname, (int)$joomlaId);
            $item = User::findFirst();
            if(!$user) :
                $user = UserFactory::create();
            endif;
        endif;

        return $user;
    }

    /**
     * @param string $modelClass
     * @param string $joomlaId
     * @param BaseObjectInterface|null $bindData
     * @param string $fieldname
     *
     * @return AbstractCollection
     */
    public static function getCoreModelById(
        string $modelClass,
        string $joomlaId,
        BaseObjectInterface $bindData = null,
        string $fieldname = 'joomlaId'
    ): AbstractCollection
    {
        $modelClass::setFindPublished(false);
        $modelClass::setFindValue($fieldname, $joomlaId);
        $item = $modelClass::findFirst();

        if(!$item) :
            $modelClass::setFindPublished(false);
            $modelClass::setFindValue($fieldname, (int)$joomlaId);
            $item = $modelClass::findFirst();
            if(!$item) :
                $factory = str_replace('Models','Factories',$modelClass) . 'Factory';
                $item = $factory::create($bindData);
            endif;
        endif;

        return $item;
    }

    /**
     * @param BaseObjectInterface $joomlaItem
     * @param string $joomlaTable
     * @param Item $item
     * @param array $bindMap
     * @param string $baseLanguageShort
     * @param string $JoomlaIdField
     *
     * @return Item
     */
    public static function bindJoomlaToItem(
        BaseObjectInterface $joomlaItem,
        string $joomlaTable,
        AbstractCollection $item,
        array $bindMap,
        string $baseLanguageShort = 'nl',
        string $JoomlaIdField = 'id'
    ) : AbstractCollection
    {
        foreach ($bindMap as $map) :
            if($map['multilang']) :
                $languageParsed = [$baseLanguageShort,'be'];
                $item->set(
                    $map['to'],
                    $joomlaItem->_($map['from']),
                    $map['multilang'],
                    $baseLanguageShort
                );
                $item->set(
                    $map['to'],
                    $joomlaItem->_($map['from']),
                    $map['multilang'],
                    'be'
                );
                Language::setFindPublished(false);
                foreach (Language::findAll() as $language) :
                    if(!in_array($language->_('short'), $languageParsed)) :
                        $translatedItem = clone $joomlaItem;
                        if(isset($map['emptyBeforeTranslate'])) :
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
                            $language->_('short'),
                            $JoomlaIdField
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
                $item->set($map['to'], $joomlaItem->_($map['from']));
            endif;
        endforeach;

        return $item;
    }

}
