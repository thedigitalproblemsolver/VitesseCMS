<?php declare(strict_types=1);

namespace VitesseCms\Shop\Models;

use VitesseCms\Core\Models\Datagroup;
use VitesseCms\User\Models\User;
use MongoDB\BSON\ObjectID;

class Shopper extends User
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $companyName;

    public function afterFetch()
    {
        parent::afterFetch();
        if (
            !$this->_('user')
            && $this->_('userId')
        ) :
            User::reset();
            User::setFindValue('_id', new ObjectID($this->_('userId')));
            $this->set('user', User::findFirst());
        endif;
    }

    public function addShopperInformation(array $data): void
    {
        if ($this->di->setting->has('SHOP_DATAGROUP_SHOPPERINFORMATION')) :
            $datagroup = Datagroup::findById($this->di->setting->get('SHOP_DATAGROUP_SHOPPERINFORMATION'));
            if ($datagroup) :
                $this->bindByDatagroup($datagroup, $data);
            endif;
        endif;
    }

    public function _(string $key, string $languageShort = null)
    {
        if (\is_object($this->user) && $this->user->_($key)) :
            return $this->user->_($key);
        endif;

        if (\is_object($this->user) && $this->user->_(strtolower($key))) :
            return $this->user->_(strtolower($key));
        endif;

        if (\is_array($this->user) && isset($this->user[$key])) :
            return $this->user[$key];
        endif;

        if (\is_array($this->user) && isset($this->user[strtolower($key)])) :
            return $this->user[strtolower($key)];
        endif;

        return parent::_($key);
    }

    //TODO move to factory
    public static function createNew(array $data, User $user = null): Shopper
    {
        if ($user === null) :
            $user = (new User())->createLogin($data['email'], $data['password']);
        endif;
        $user->addPersonalInformation($data)->save();

        $shopper = new Shopper();
        $shopper->set('userId', (string)$user->getId());
        $shopper->set('user', $user);
        $shopper->addShopperInformation($data);
        $shopper->set('published', true);
        $shopper->save();

        return $shopper;
    }

    public function getCompanyName(): string
    {
        return $this->companyName??'';
    }
}
