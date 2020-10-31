<?php declare(strict_types=1);

namespace VitesseCms\Shop\Helpers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\User\Models\User;
use Phalcon\Di;

class CheckoutHelper extends AbstractInjectable
{
    public function getStep(int $step = 1): Item
    {
        Item::setFindValue('datagroup', $this->setting->get('SHOP_DATAGROUP_CHECKOUT'));
        $steps = Item::findAll();

        return $steps[$step - 1];
    }

    public function isCurrentItemCheckout(): bool
    {
        if (\is_object($this->view->getVar('currentItem'))) :
            return $this->view->getVar('currentItem')->_('datagroup') === $this->setting->get('SHOP_DATAGROUP_CHECKOUT');
        endif;

        return false;
    }

    public function getNextStep()
    {
        $doReturn = false;
        Item::setFindValue('datagroup', $this->setting->get('SHOP_DATAGROUP_CHECKOUT'));
        Item::addFindOrder('ordering');
        $steps = Item::findAll();
        /** @var AbstractCollection $step */
        foreach ($steps as $step) :
            if ($doReturn) :
                return $step;
            endif;
            if ((string)$step->_('_id') === $this->view->getVar('currentId')) :
                $doReturn = true;
            endif;
        endforeach;

        return false;
    }

    public function getPreviousStep()
    {
        Item::setFindValue('datagroup', $this->setting->get('SHOP_DATAGROUP_CHECKOUT'));
        Item::addFindOrder('ordering');
        $steps = Item::findAll();
        $previousStep = false;
        foreach ($steps as $step) :
            if ((string)$step->_('_id') === $this->view->getVar('currentId')) :
                return $previousStep;
            endif;
            $previousStep = $step;
        endforeach;

        return false;
    }

    /**
     * @param User $user
     *
     * @return array|bool|Item|User|null|\Phalcon\Mvc\CollectionInterface
     * @deprecated should use service
     */
    public static function getShiptoAddress(User $user)
    {
        $session = Di::getDefault()->get('session');
        $shiptoAddresses = null;

        if (
            $session->get('shiptoAddress') !== 'invoice'
            && $session->get('shiptoAddress') !== null
            && MongoUtil::isObjectId($session->get('shiptoAddress'))
        ) :
            $shiptoAddresses = Item::findById($session->get('shiptoAddress'));
        else :
            $shiptoAddresses = User::findById($user->getId());
        endif;

        return $shiptoAddresses;
    }
}
