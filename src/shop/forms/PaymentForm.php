<?php declare(strict_types=1);

namespace VitesseCms\Shop\Forms;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Shop\AbstractPaymentType;
use VitesseCms\Shop\Models\Payment;

class PaymentForm extends AbstractForm
{
    public function initialize(Payment $item = null)
    {
        if ($item === null) :
            $item = new Payment();
            $item->set('type', null);
        endif;

        $this->addText('%CORE_NAME%', 'name', (new Attributes())->setRequired(true)->setMultilang(true));

        if (!$item->_('type')) :
            $this->addDropdown(
                '%ADMIN_TYPE%',
                'type',
                (new Attributes())
                    ->setRequired(true)
                    ->setOptions(ElementHelper::arrayToSelectOptions((new Payment)->getTypes()))
            );
        else :
            $object = $item->getTypeClass();
            /** @var AbstractPaymentType $paymentType */
            $paymentType = new $object();
            $paymentType->buildAdminForm($this);
        endif;

        $this->addSubmitButton('%CORE_SAVE%');
    }
}
