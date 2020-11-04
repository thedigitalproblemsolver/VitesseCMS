<?php

namespace VitesseCms\User\Factories;

use VitesseCms\User\Models\PermissionRole;
use VitesseCms\User\Models\User;
use Phalcon\Di;

/**
 * Class UserFactory
 */
class UserFactory
{
    /**
     * @param string $email
     * @param string $calling_name
     * @param bool $published
     * @param string|null $password
     *
     * @return User
     */
    public static function create(
        string $email,
        string $password = null,
        string $calling_name = 'registered',
        bool $published = false
    ) : User
    {
        $user = new User();
        $user->set('email', $email);
        $user->set('published', $published);
        if($password) :
            $user->set('password', Di::getDefault()->get('security')->hash($password));
        endif;
        $user->set('published', $published);

        PermissionRole::setFindValue('calling_name', $calling_name);
        $user->set('role', (string)PermissionRole::findFirst()->getId());

        /*if( is_object($bindData)) :
            Datagroup::setFindPublished(false);
            $datagroup = Datagroup::findById($datagroupId);
            foreach ($datagroup->_('datafields') as $datafieldId => $groupDatafield ) :
                Datafield::setFindPublished(false);
                $datafield = Datafield::findById($datafieldId);

    var_dump('UserFactory '.$datafield->_('calling_name'));
            die();
            endforeach;
        endif;*/

        return $user;
    }

    /**
     * @return User
     */
    public static function createGuest(): User
    {
        $user = new User();
        $user->set('role', 'guest');

        return $user;
    }
}
