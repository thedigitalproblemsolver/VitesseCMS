<?php

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Core\AbstractController;
use VitesseCms\Shop\Models\Discount;

/**
 * Class DiscountController
 */
class DiscountController extends AbstractController
{
    /**
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function parsecodeAction(): void
    {
        $redirectUrl = null;
        if (
            $this->request->isPost()
            || $this->request->isAjax()
            || ($this->request->isGet() && $this->request->get('code'))
        ) :
            Discount::setFindValue('code', $this->request->get('code'));
            /** @var Discount $discount */
            $discount = Discount::findFirst();
            if ($discount) :
                if (
                    $this->shop->discount->getAmountOfUsedOrders($discount) === 0
                    && $this->shop->discount->isValid($discount)
                ) :
                    $this->session->set('discountId', (string)$discount->getId());
                    $this->log->write(
                        $discount->getId(),
                        Discount::class,
                        'Discount-code <b>' . $discount->_('code') . '</b> entered'
                    );
                    $this->flash->_('SHOP_DISCOUNT_CODE_FOUND_AND_SET');
                else :
                    $this->flash->_('SHOP_DISCOUNT_CODE_UNKNOWN_OR_USED', 'error');
                endif;

                if($this->request->isGet() && $this->request->get('code')) :
                    $redirectUrl = $this->shop->checkout->getStep()->_('slug');
                endif;
            else :
                $this->flash->_('SHOP_DISCOUNT_CODE_UNKNOWN_OR_USED', 'error');
            endif;
        endif;

        $this->redirect($redirectUrl);
    }
}
