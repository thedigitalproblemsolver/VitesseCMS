<?php

namespace VitesseCms\Shop\Forms;

use VitesseCms\Communication\Models\Newsletter;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Shop\Enum\OrderStateEnum;
use VitesseCms\Shop\Models\OrderState;

/**
 * Class OrderForm
 */
class OrderStateForm extends AbstractForm
{

    /**
     * @param OrderState|null $item
     */
    public function initialize( OrderState $item = null)
    {
        $this->_(
            'text',
            '%CORE_NAME%',
            'name',
            [
                'multilang' => true
            ]
        )->_(
            'textarea',
            '%ADMIN_ORDERSTATE_PAGE_TEXT%',
            'bodytext',
            [
                'multilang' => true,
                'inputClass' => 'editor'
            ]

        )->_(
            'text',
            '%ADMIN_SYSTEM_MESSAGE%',
            'messageText',
            ['multilang' => true]
        )->_(
            'select',
            '%ADMIN_SYSTEM_MESSAGE_TYPE%',
            'messageType',
            [
                'options' => ElementHelper::arrayToSelectOptions([
                    'success' => '%ADMIN_ALERT_SUCCESS%',
                    'error' => '%ADMIN_ALERT_DANGER%',
                    'notice' => '%ADMIN_ALERT_INFO%',
                    'warning' => '%ADMIN_ALERT_WARNING%',
                ])
            ]
        )->_(
            'select',
            '%ADMIN_ORDERSTATE_CHANGE_STOCK%',
            'stockAction',
            [
                'options' => ElementHelper::arrayToSelectOptions([
                    OrderStateEnum::STOCK_ACTION_INCREASE => '%ADMIN_INCREASE%',
                    OrderStateEnum::STOCK_ACTION_DECREASE => '%ADMIN_DECREASE%',
                ])
            ]
        )->_(
            'select',
            '%ADMIN_ORDERSTATE_CHANGE_ANALYTICS_TRIGGERS%',
            'analyticsTriggers',
            [
                'multiple' => true,
                'options' => ElementHelper::arrayToSelectOptions(OrderStateEnum::ANALYTICS_TRIGGERS),
            ]
        );

        Newsletter::setFindValue('parentId',null);
        $newsletters = Newsletter::findAll();
        $this->_(
            'select',
            'Add to newsletter',
            'addToNewsletters',
            [
                'multilang' => true,
                'multiple' => true,
                'options'  => ElementHelper::arrayToSelectOptions($newsletters),
                'inputClass' => 'select2'
            ]
        )->_(
            'select',
            'Unsubscribe from newsletter<br /><small>never end again</small>',
            'unsubscribeFromNewsletters',
            [
                'multilang' => true,
                'multiple' => true,
                'options'  => ElementHelper::arrayToSelectOptions($newsletters),
                'inputClass' => 'select2'
            ]
        )->_(
            'select',
            'Remove from newsletters<br /><small>Can be send again</small>',
            'removeFromNewsletters',
            [
                'multilang' => true,
                'multiple' => true,
                'options'  => ElementHelper::arrayToSelectOptions($newsletters),
                'inputClass' => 'select2'
            ]
        )->_(
            'checkbox',
            '%ADMIN_ORDERSTATE_CLEAR_THE_CART%',
            'clearCart'
        )->_(
            'checkbox',
            '%ADMIN_ORDERSTATE_PRINT_SHIPPING_LABEL%',
            'printShippingLabel'
        )->_(
            'checkbox',
            'Can switch to state on same level',
            'canSwitchToSameLevel'
        )->_(
            'text',
            '%ADMIN_CALLING_NAME%',
            'calling_name'
        )->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}
