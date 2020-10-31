<?php

namespace VitesseCms\Communication\Helpers;

use VitesseCms\Database\AbstractCollection;

/**
 * Class NewsletterListHelper
 */
class NewsletterListHelper
{
    /**
     * @param $email
     * @param $newsletterList
     *
     * @return bool
     */
    public static function emailExistsAsMember(
        string $email,
        AbstractCollection $newsletterList
    ): bool {
        if( \is_array($newsletterList->_('members'))) :
            foreach ($newsletterList->_('members') as $member):
                if (isset($member['email']) && $member['email'] === $email) :
                    return true;
                endif;
            endforeach;
        endif;

        return false;
    }
}
