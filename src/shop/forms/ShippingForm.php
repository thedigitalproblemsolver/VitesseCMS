<?php

namespace VitesseCms\Shop\Forms;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Shop\Helpers\ShippingHelper;
use VitesseCms\Shop\Models\Shipping;
use Phalcon\Di;

/**
 * Class PaymentForm
 */
class ShippingForm extends AbstractForm
{

    /**
     * @param Shipping|null $item
     */
    public function initialize( Shipping $item = null)
    {
        if( $item === null) :
            $item = new Shipping();
            $item ->set('type', null);
        endif;

        $this->_(
            'text',
            '%CORE_NAME%',
            'name',
            [
                'required' => 'required',
                'multilang' => true,
            ]
        );

        if( !$item->_('type') ) :
            $this->_(
                'select',
                '%ADMIN_TYPE%',
                'type',
                [
                    'options' => ElementHelper::arrayToSelectOptions(ShippingHelper::getTypes(
                        $this->configuration->getRootDir(),
                        $this->configuration->getAccount()
                    )),
                    'required' => 'required'
                ]
            );
        else :
            $object = ShippingHelper::getClass($item->_('type'));
            /** @var AbstractCollection $item */
            $item = new $object();
            $item->buildAdminForm($this);
        endif;

        $this->_(
            'submit',
            '%CORE_SAVE%'
        );
    }
}
