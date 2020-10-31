<?php

namespace VitesseCms\Shop\Forms;

use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Shop\Models\OrderState;

/**
 * Class OrderOrderStateForm
 */
class OrderOrderStateForm extends AbstractForm
{

    /**
     * @param BaseObjectInterface|null $item
     */
    public function initialize( BaseObjectInterface $item = null)
    {
        //TODO waar gaat het mis? De reset hoort hier niet
        OrderState::reset();
        $currentOrderState = OrderState::findById($item->_('orderStateId'));
        OrderState::reset();
        OrderState::setFindValue('parentId',$item->_('orderStateId'));
        $orderStates = OrderState::findAll();
        if(count($orderStates) > 0 ) :
            $this->_(
                'select',
                $currentOrderState->_('name'),
                'orderState',
                [
                    'options' => ElementHelper::arrayToSelectOptions(
                        $orderStates,
                        [
                            $item->_('orderStateId')
                        ]
                    )
                ]
            );
        elseif(\is_object($currentOrderState) && (bool)$currentOrderState->_('canSwitchToSameLevel')) :
            OrderState::setFindValue('parentId',$currentOrderState->_('parentId'));
            $orderStates = OrderState::findAll();
            $this->_(
                'select',
                $currentOrderState->_('name'),
                'orderState',
                [
                    'options' => ElementHelper::arrayToSelectOptions(
                        $orderStates,
                        [
                            $item->_('orderStateId')
                        ]
                    )
                ]
            );
        else :
            $this->_(
                'html',
                '',
                'html',
                [
                    'html' => $currentOrderState?$currentOrderState->_('name'):''
                ]
            );
        endif;
    }
}
