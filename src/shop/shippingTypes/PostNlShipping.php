<?php declare(strict_types=1);

namespace VitesseCms\Shop\ShippingTypes;

use DivideBV\Postnl\ComplexTypes\Address;
use DivideBV\Postnl\ComplexTypes\ArrayOfAddress;
use DivideBV\Postnl\ComplexTypes\Shipment;
use DivideBV\Postnl\Postnl;
use VitesseCms\Core\Utils\DebugUtil;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Shop\AbstractShippingType;
use VitesseCms\Shop\Forms\ShippingForm;
use VitesseCms\Shop\Models\Country;
use VitesseCms\Shop\Models\Order;
use Phalcon\Di;

class PostNlShipping extends AbstractShippingType
{
    public function buildAdminForm(ShippingForm $form)
    {
        $attributes = (new Attributes())->setRequired(true);

        $form->addText('PostNL customer number', 'customerNumber', $attributes)
            ->addText('PostNL customer code', 'customerCode', $attributes)
            ->addText('PostNL customer name', 'customerName', $attributes)
            ->addText('PostNL API-username', 'apiUsername', $attributes)
            ->addText('PostNL API-password', 'apiPassword', $attributes)
            ->addText('PostNL Collection location', 'collectionLocation', $attributes)
            ->addText('PostNL Globalpack', 'globalpack', $attributes);
    }

    public function calculateOrderAmount(Order $order): float
    {
        return 99.99;
    }

    public function calculateOrderVat(Order $order): float
    {
        return 99.99;
    }

    public function calculateCartAmount(array $items): float
    {
        return 99.99;
    }

    public function calculateCartVat(array $items): float
    {
        return 99.99;
    }

    public function getLabelLink(Order $order): string
    {
        if (
            (
                isset($order->_('orderState')['printShippingLabel'])
                && $order->_('orderState')['printShippingLabel'] === '1'
            )
            && $this->shipping->_('customerNumber')
            && $this->shipping->_('customerCode')
            && $this->shipping->_('customerName')
            && $this->shipping->_('apiUsername')
            && $this->shipping->_('apiPassword')
        ) :
            return Di::getDefault()->get('url')->getBaseUri() . 'admin/shop/adminshipping/shippingLabel/' . $order->getId();
        endif;

        return '';
    }

    public function getLabel(Order $order, ?string $packageType): ?string
    {
        $pdfDir = Di::getDefault()->get('config')->get('accountDir') . 'files/shop/shippinglabels/';
        DirectoryUtil::exists($pdfDir, true);
        $pdfFile = $order->_('orderId') . '_label.pdf';

        if (!is_file($pdfDir . $pdfFile)) :
            $useSandbox = false;
            if (DebugUtil::isDev()) :
                $useSandbox = true;
            endif;

            // Create client class using credentials received from PostNL.
            $client = new Postnl(
                $this->shipping->_('customerNumber'),   // Customer number
                $this->shipping->_('customerCode'),     // Customer code
                $this->shipping->_('customerName'),  // Customer name
                $this->shipping->_('apiUsername'),     // Username
                $this->shipping->_('apiPassword'), // Password
                (int)$this->shipping->_('collectionLocation'),     // Collection location
                $this->shipping->_('globalpack'),   // Globalpack
                $useSandbox        // Whether to use PostNL's sandbox environment.
            );

            $recieverCountry = Country::findById($order->_('shiptoAddress')['country']);
            $receiverAddress = Address::create()
                ->setAddressType('01')
                ->setFirstName($order->_('shiptoAddress')['firstName'])
                ->setName($order->_('shiptoAddress')['lastName'])
                ->setCompanyName($order->_('shiptoAddress')['companyName'])
                ->setStreet($order->_('shiptoAddress')['street'])
                ->setHouseNr($order->_('shiptoAddress')['houseNumber'])
                ->setHouseNrExt('')
                ->setZipcode($order->_('shiptoAddress')['zipCode'])
                ->setCity($order->_('shiptoAddress')['city'])
                ->setCountrycode($recieverCountry->_('short'));

            $senderAddress = Address::create()
                ->setAddressType('02')
                ->setFirstName('')
                ->setName('')
                ->setCompanyName($this->setting->get('CONTACT_ADDRESS_COMPANYNAME'))
                ->setStreet($this->setting->get('CONTACT_ADDRESS_STREET'))
                ->setHouseNr($this->setting->get('CONTACT_ADDRESS_HOUSENUMBER'))
                ->setHouseNrExt('')
                ->setZipcode($this->setting->get('CONTACT_ADDRESS_ZIPCODE'))
                ->setCity($this->setting->get('CONTACT_ADDRESS_CITY'))
                ->setCountrycode(strtoupper($this->setting->get('CONTACT_ADDRESS_COUNTRYSHORT')));

            // Request a barcode from PostNL
            $barcode = $client->generateBarcodeByDestination($receiverAddress->getCountrycode());

            $shippingType = $order->_('shippingType');
            $shippingType['barcode'] = $barcode->getBarcode();
            $order->set('shippingType', $shippingType);
            $order->save();

            // Create a shipment.
            $shipment = Shipment::create()
                ->setAddresses(new ArrayOfAddress([
                    $receiverAddress,
                    $senderAddress,
                ]))
                ->setBarcode($barcode)
                ->setDimension($order->_('dimension'))
                ->setProductCodeDelivery($order->_('productCodeDelivery'));

            $result = $client->generateLabel($shipment);
            $label = $result->getLabels()[0];
            $file = new \SplFileObject($pdfDir . $pdfFile, 'w');
            $file->fwrite($label->getContent());
        endif;

        FileUtil::display($pdfDir . $pdfFile);
    }

    public function calculateCartTotal(array $items): float
    {
        // TODO: Implement calculateCartTotal() method.
    }
}
