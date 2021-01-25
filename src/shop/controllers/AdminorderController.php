<?php declare(strict_types=1);

namespace VitesseCms\Shop\Controllers;

use VitesseCms\Admin\AbstractAdminController;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Shop\Forms\OrderForm;
use VitesseCms\Shop\Helpers\OrderHelper;
use VitesseCms\Shop\Interfaces\RepositoriesInterface;
use VitesseCms\Shop\Models\Order;

class AdminorderController extends AbstractAdminController implements RepositoriesInterface
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = Order::class;
        $this->classForm = OrderForm::class;
        $this->listOrder = 'orderId';
        $this->listOrderDirection = -1;
    }

    public function editAction(
        string $itemId = null,
        string $template = 'adminEditForm',
        string $templatePath = 'src/core/resources/views/',
        AbstractForm $form = null
    ): void {
        parent::editAction(
            $itemId,
            'orderEdit',
            'src/shop/resources/views/admin/'
        );
    }

    public function saveAction(?string $itemId = null, AbstractCollection $item = null, AbstractForm $form = null): void
    {
        die('Order saving not allowed.');
    }

    public function changeOrderStateAction(): void
    {
        if ($this->dispatcher->getParam(0) !== null) :
            $order = $this->repositories->order->getById($this->dispatcher->getParam(0), false);
            if ($order !== null) :
                OrderHelper::setOrderState(
                    $order,
                    $this->repositories->orderState->getById($this->request->get('orderState'))
                );
                $order->save();

                $this->flash->setSucces('ADMIN_STATE_CHANGE_SUCCESS', ['Order']);
            endif;
        endif;

        $this->redirect();
    }

    public function sendEmailAction(): void
    {
        if ($this->dispatcher->getParam(0) !== null) :
            OrderHelper::sendEmail(
                $this->repositories->order->getById($this->dispatcher->getParam(0)),
                $this->view
            );
        endif;

        $this->redirect();
    }
}
