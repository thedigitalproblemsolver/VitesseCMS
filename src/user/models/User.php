<?php declare(strict_types=1);

namespace VitesseCms\User\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\Models\Datafield;
use VitesseCms\Field\AbstractField;
use VitesseCms\Core\Models\Datagroup;

class User extends AbstractCollection
{
    /**
     * @var null
     */
    protected $permissionRole;

    /**
     * @var bool
     */
    protected $forcePasswordReset;

    /**
     * @var bool
     */
    protected $passwordReset;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    public $email;

    public function afterFetch()
    {
        $this->set('name', $this->_('email'));

        parent::afterFetch();
    }

    public function getEmail(): string{
        return $this->email;
    }

    /**
     * @deprecated should use isLoggedIn
     */
    public function loggedIn(): bool
    {
        return $this->isLoggedIn();
    }

    public function isLoggedIn(): bool
    {
        if ($this->getId()) :
            return true;
        endif;

        return false;
    }

    public function getPermissionRole(): string
    {
        if ($this->getId()) :
            if ($this->permissionRole === null) :
                $this->permissionRole = PermissionRole::findById($this->_('role'));
            endif;

            return $this->permissionRole->_('calling_name');
        endif;

        return 'guest';
    }

    public function hasAdminAccess(): bool
    {
        if ($this->getId()) :
            if ($this->permissionRole === null) :
                $this->permissionRole = PermissionRole::findById($this->_('role'));
            endif;

            if (
                $this->permissionRole->_('adminAccess')
                || 'superadmin' === $this->getPermissionRole()
            ) :
                return true;
            endif;
        endif;

        return false;
    }

    public function createLogin(string $email, string $password, string $role = null): User
    {
        if ($role === null) :
            $role = 'registered';
        endif;
        PermissionRole::setFindValue('calling_name', $role);
        $role = PermissionRole::findFirst();

        $user = new self();
        $user->set('email', $email);
        $user->set('password', $this->di->security->hash($password));
        $user->set('role', (string)$role->getId());
        $user->set('published', true);
        $user->save();

        return $user;
    }

    public function beforeSave(): void
    {
        if ($this->di->setting->has('USER_DATAGROUP_PERSONALINFORMATION')) :
            $datagroup = Datagroup::findById(
                $this->di->setting->get('USER_DATAGROUP_PERSONALINFORMATION')
            );
            foreach ($datagroup->_('datafields') as $datafieldObject) :
                /** @var Datafield $datafield */
                $datafield = Datafield::findById($datafieldObject['id']);
                if (is_object($datafield)) :
                    $static = 'VitesseCms\\Field\Models\\'.$datafield->_('type');
                    /** @var AbstractField $static */
                    $static::beforeSave($this, $datafield);
                endif;
            endforeach;
        endif;
    }

    public function addPersonalInformation(array $data): User
    {
        if ($this->di->setting->has('USER_DATAGROUP_PERSONALINFORMATION')) :
            $datagroup = Datagroup::findById(
                $this->di->setting->get('USER_DATAGROUP_PERSONALINFORMATION')
            );
            if ($datagroup) :
                $this->bindByDatagroup($datagroup, $data);
            endif;
        endif;

        return $this;
    }

    public function hasForcedPasswordReset(): bool
    {
        return (bool)$this->forcePasswordReset;
    }

    public function setForcePasswordReset(bool $forcePasswordReset): User
    {
        $this->forcePasswordReset = $forcePasswordReset;

        return $this;
    }

    public function setPasswordReset(bool $passwordReset): User
    {
        $this->passwordReset = $passwordReset;

        return $this;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }
}
