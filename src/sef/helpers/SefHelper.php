<?php declare(strict_types=1);

namespace VitesseCms\Sef\Helpers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Language\Models\Language;
use VitesseCms\Sef\Factories\RedirectFactory;
use VitesseCms\Sef\Models\Redirect;
use Phalcon\Di;

class SefHelper
{
    public static function redirect(string $from): void
    {
        Redirect::setFindValue('from', $from);
        $redirect = Redirect::findFirst();
        if (
            $redirect
            && $redirect->_('to') !== $_SERVER['REQUEST_URI']
        ) :
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$redirect->_('to'));
            die();
        endif;
    }

    public static function getComponentURL(
        string $module,
        string $controller,
        string $action
    ): string {
        return Di::getDefault()->get('url')->getBaseUri().$module.'/'.$controller.'/'.$action.'/';
    }

    public static function saveRedirectFromItem(string $oldItemId, array $newSlug): void
    {
        $oldItem = Item::findById($oldItemId);
        if ($oldItem) :
            Language::setFindPublished(false);
            foreach (Language::findAll() as $language) :
                if (
                    isset($newSlug[$language->_('short')])
                    && $newSlug[$language->_('short')] !== ''
                    && $oldItem->_('slug', $language->_('short')) !== ''
                    && $oldItem->_('slug', $language->_('short')) !== $newSlug[$language->_('short')]
                    && substr_count($newSlug[$language->_('short')], 'page:') === 0
                ) :
                    $from = '/'.$oldItem->_('slug', $language->_('short'));
                    $to = '/'.$newSlug[$language->_('short')];

                    //check of to + from al bestaat en verwijder deze
                    Redirect::setFindValue('to', $from);
                    Redirect::setFindValue('from', $to);
                    Redirect::setFindValue('languageShort', $language->_('short'));
                    $toRedirects = Redirect::findAll();
                    if ($toRedirects) :
                        /** @var AbstractCollection $toRedirect */
                        foreach ($toRedirects as $toRedirect) :
                            $toRedirect->delete();
                        endforeach;
                    endif;

                    //check of to als from als bestaat en verwijder deze
                    Redirect::setFindValue('from', $to);
                    Redirect::setFindValue('languageShort', $language->_('short'));
                    $toRedirects = Redirect::findAll();
                    if ($toRedirects) :
                        /** @var AbstractCollection $toRedirect */
                        foreach ($toRedirects as $toRedirect) :
                            $toRedirect->delete();
                        endforeach;
                    endif;

                    //check of from al bestaat
                    Redirect::setFindValue('from', $from);
                    Redirect::setFindValue('languageShort', $language->_('short'));
                    $fromRedirect = Redirect::findFirst();
                    if (!$fromRedirect) :
                        $fromRedirect = RedirectFactory::create(
                            $from,
                            $to,
                            $language->_('short'),
                            true
                        );
                    endif;

                    //set new to and save
                    $fromRedirect->set('to', $to);
                    $fromRedirect->save();
                endif;
            endforeach;
        endif;
    }

    /**
     * @TODO move to litener
     */
    public static function parsePlaceholders(string $string, ?string $currentId = null): string
    {
        $parsed = [];

        preg_match_all("/href=\"page:([[:alnum:]]*)/", $string, $aMatches);
        foreach ((array)$aMatches[1] as $key => $value) :
            if (!in_array($value, $parsed, true) && MongoUtil::isObjectId($value)) :
                $item = Item::findById($value);
                $replace = Di::getDefault()->get('url')->getBaseUri();
                $name = '';
                if ($item) :
                    $replace .= $item->_('slug');
                    $name = $item->_('name');
                endif;
                $string = str_replace(
                    ['href="page:'.$value.'"', 'page:'.$value.':name'],
                    ['href="'.$replace.'"', $name],
                    $string
                );
                $parsed[] = $value;
            endif;
        endforeach;

        if ($currentId !== null):
            $string = str_replace(
                ['class="'.$currentId.'"'],
                ['class="active '.$currentId.'"'],
                $string
            );
        endif;

        return $string;
    }
}
