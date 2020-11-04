<?php declare(strict_types=1);

namespace VitesseCms\Shop\PaymentTypes;

use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Shop\AbstractPaymentType;
use VitesseCms\Shop\Enum\PaymentEnum;
use VitesseCms\Shop\Helpers\CartHelper;
use VitesseCms\Shop\Models\Country;
use VitesseCms\Shop\Models\Order;
use VitesseCms\Shop\Models\Payment;
use MultiSafepayAPI\Client;
use Phalcon\Di;

class Multisafepay extends AbstractPaymentType
{
    protected const API_URL = 'https://api.multisafepay.com/v1/json/';
    protected const API_TEST_URL = 'https://testapi.multisafepay.com/v1/json/';

    public function buildAdminForm(AbstractForm $form): void
    {
        $form->addText('Account ID', 'accountId', (new Attributes())->setRequired(true))
            ->addText('API-key', 'apiKey', (new Attributes())->setRequired(true));
    }

    public function prepareOrder(Order $order): void
    {
        if (
            !isset($order->_('paymentType')['transactionId'])
            && Di::getDefault()->get('request')->get('transactionid')
        ) :
            $paymentType = $order->_('paymentType');
            $paymentType['transactionId'] = Di::getDefault()->get('request')->get('transactionid');
            $order->set('paymentType', $paymentType);
        endif;
    }

    public function doPayment(Order $order, Payment $payment): void
    {
        $shipToAddress = $order->_('shiptoAddress');
        $country = Country::findById($shipToAddress->_('country'));
        $cart = $order->_('items');

        $client = new Client();
        $client->setApiKey($payment->_('apiKey'));
        $client->setApiUrl(self::API_URL);

        $record = [
            "type" => 'redirect',
            "manual" => 'false',
            "order_id" => $order->_('orderId'),
            "currency" => 'EUR',
            "amount" => round($order->_('total'), 2) * 100,
            "description" => 'Your order ' . $order->getId() . ' at ' . $this->setting->get('website_default_name'),
            "items" => $this->buildItemlist($cart['products']),
            "payment_options" => [
                "notification_url" => Di::getDefault()->get('url')->getBaseUri() . "shop/payment/process/" . $order->getId(),
                "redirect_url" => Di::getDefault()->get('url')->getBaseUri() . "shop/payment/redirect/" . $order->getId(),
                "cancel_url" => Di::getDefault()->get('url')->getBaseUri() . "shop/payment/cancel/" . $order->getId(),
                "close_window" => "false",
            ],
            "customer" => [
                "locale" => Di::getDefault()->get('configuration')->getLanguageLocale(),
                "ip_address" => $order->_('ipAddress'),
                "forwarded_ip" => $_SERVER['SERVER_ADDR'],
                "first_name" => $shipToAddress->_('firstName'),
                "last_name" => $shipToAddress->_('lastName'),
                "address1" => $shipToAddress->_('street'),
                "address2" => "",
                "house_number" => $shipToAddress->_('houseNumber'),
                "zip_code" => $shipToAddress->_('zipCode'),
                "city" => $shipToAddress->_('city'),
                "state" => "",
                "country" => $country->_('short'),
                "phone" => $shipToAddress->_('phoneNumber'),
                "email" => $shipToAddress->_('email'),
            ],
            "google_analytics" => [
                "account" => $this->setting->get('google_analytics_trackingId'),
            ],
        ];
        $client->orders->post($record);

        $this->log->write(
            $order->getId(),
            Order::class,
            'Order ' . $order->_('orderId') . ' user redirected to Multisafepay'
        );

        header("Location: " . $client->orders->getPaymentLink());
        die();
    }

    public function getTransactionState(int $transactionId, Payment $payment): string
    {
        $client = new Client();
        $client->setApiKey($payment->_('apiKey'));
        $client->setApiUrl(self::API_URL);
        $transaction = $client->orders->get('orders', $transactionId, [], false);

        if (
            isset($transaction->payment_details->type)
            && $transaction->payment_details->type === 'BANKTRANS'
        ):
            return PaymentEnum::BANKTRANSFER;
        endif;

        switch ($transaction->status) :
            case 'completed':
            case 'refunded':
                return PaymentEnum::PAID;
            case 'void':
            case 'declined':
            case 'expired':
                return PaymentEnum::ERROR;
            case 'canceled':
                return PaymentEnum::CANCELLED;
            case 'uncleared':
            case 'reserved':
            case 'reversed':
            case 'initialized':
            default:
                return PaymentEnum::PENDING;
        endswitch;
    }

    protected function buildItemlist(array $items): string
    {
        $item_list = '';
        foreach ($items as $item) :
            $item_list .= $item->_('quantity') . " x " . CartHelper::getLogNameFromItem($item) . "\n";
        endforeach;

        return $item_list;
    }
}