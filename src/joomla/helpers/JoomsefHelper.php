<?php

namespace VitesseCms\Joomla\Helpers;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Language\Models\Language;
use VitesseCms\Joomla\Models\JoomsefUrls;
use VitesseCms\Sef\Factories\RedirectFactory;
use Phalcon\Mvc\ModelInterface;

/**
 * Class JoomsefHelper
 */
class JoomsefHelper
{
    /**
     * @param ModelInterface $joomlaObject
     * @param AbstractCollection $item
     */
    public static function redirectsFromObject(
        string $urlPart,
        BaseObjectInterface $joomlaObject,
        AbstractCollection $item
    )
    {
        preg_match_all("/{([A-Za-z_]*)}/", $urlPart, $matches);
        foreach (Language::findAll() as $language) :
            $seach = [];
            $replace = [];
            foreach ($matches[1] as $key => $match) :
                switch ($match) :
                    case 'languageShort':
                        $seach[] = '{languageShort}';
                        $replace[]= $language->_('short');
                        break;
                    default:
                        $seach[] = '{'.$match.'}';
                        $replace[]= $joomlaObject->_($match);
                        break;
                endswitch;
            endforeach;

            /** @var BaseObjectInterface $joomsefUrl */
            $joomsefUrl = JoomsefUrls::findFirst("origurl LIKE '%".str_replace($seach, $replace, $urlPart)."%'");
            if($joomsefUrl) :
                $redirect = RedirectFactory::create(
                    $joomsefUrl->_('sefurl'),
                    $item->_('slug', $language->_('short')),
                    $language->_('short')
                );
                $redirect->set('published', true);
                $redirect->save();
            endif;
        endforeach;
    }
}
