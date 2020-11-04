<?php

namespace VitesseCms\Sef\Factories;

use VitesseCms\Sef\Models\Redirect;

/**
 * Class RedirectFactory
 */
class RedirectFactory
{
    /**
     * @param string $from
     * @param string $to
     * @param string $languageShort
     * @param bool $published
     *
     * @return Redirect
     */
    public static function create(
        string $from,
        string $to,
        string $languageShort = null,
        bool $published = false
    )
    {
        $collection = new Redirect();
        $collection->set('from', $from);
        $collection->set('to', $to);
        $collection->set('languageShort', $languageShort);
        $collection->set('published', $published);

        return $collection;
    }
}
