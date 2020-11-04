<?php declare(strict_types=1);

namespace VitesseCms\Shop\ShippingTypes;

use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Core\Utils\FileUtil;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Shop\AbstractShippingType;
use VitesseCms\Shop\Enum\MyparcelShippingEnum;
use VitesseCms\Shop\Enum\ShippingEnum;
use VitesseCms\Shop\Factories\MyParcelCustomsItemFactory;
use VitesseCms\Shop\Forms\ShippingForm;
use VitesseCms\Shop\Models\Country;
use VitesseCms\Shop\Models\Order;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository;
use Phalcon\Di;
use SplFileObject;

class MyparcelShipping extends AbstractShippingType
{
    public function buildAdminForm(ShippingForm $form)
    {
        $form->addText('API-key', 'apiKey', (new Attributes())->setRequired(true));
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

    public function calculateCartTotal(array $items): float
    {
        return 99.99;
    }

    public function calculateCartVat(array $items): float
    {
        return 99.99;
    }

    public function getLabelLink(Order $order): string
    {
        if ($this->shipping->_('apiKey')):
            return parent::getLabelLink($order);
        endif;

        return '';
    }

    public function getLabel(Order $order, ?string $packageType): ?string
    {
        $pdfDir = Di::getDefault()->get('config')->get('accountDir') . 'files/shop/shippinglabels/';
        DirectoryUtil::exists($pdfDir, true);
        $pdfFile = $order->_('orderId') . '_label.pdf';
        switch ($packageType) :
            case ShippingEnum::PACKAGE:
                $myparcelPackageType = MyparcelShippingEnum::PACKAGETYPE_PACKAGE;
                break;
            case ShippingEnum::ENVELOPE:
                $myparcelPackageType = MyparcelShippingEnum::PACKAGETYPE_MAILBOX_PACKAGE;
                break;
            default:
                $myparcelPackageType = $order->_('myparcelPackageType');
        endswitch;

        $recieverCountry = Country::findById($order->_('shiptoAddress')['country']);
        if (!is_file($pdfDir . $pdfFile)) :
            $consignment = (new MyParcelConsignmentRepository())
                ->setApiKey($this->shipping->_('apiKey'))
                ->setReferenceId($order->_('orderId'))
                ->setLabelDescription($order->_('orderId'))
                ->setCountry($recieverCountry->_('short'))
                ->setPerson(
                    $order->_('shiptoAddress')['firstName'] . ' ' .
                    $order->_('shiptoAddress')['lastName']
                )
                ->setCompany($order->_('shiptoAddress')['companyName'])
                ->setFullStreet(
                    $order->_('shiptoAddress')['street'] . ' ' .
                    $order->_('shiptoAddress')['houseNumber']
                )
                ->setPostalCode($order->_('shiptoAddress')['zipCode'])
                ->setCity($order->_('shiptoAddress')['city'])
                ->setEmail($order->_('shopper')['user']['email'])
                ->setPackageType($myparcelPackageType);

            $myParcelCustomsItem = MyParcelCustomsItemFactory::createFromOrder($order);
            $consignment->addItem($myParcelCustomsItem);

            $myParcelCollection = (new MyParcelCollection())
                ->addConsignment($consignment)->setPdfOfLabels();

            $shippingType = $order->_('shippingType');
            $shippingType['barcode'] = $consignment->getBarcode();
            $order->set('shippingType', $shippingType);
            $order->save();

            $file = new SplFileObject($pdfDir . $pdfFile, 'w');

            $file->fwrite($myParcelCollection->getLabelPdf());
        endif;

        FileUtil::display($pdfDir . $pdfFile);

        return null;
    }

    public function getTrackAndTraceLink(Order $order): string
    {
        if (isset($order->_('shippingType')['barcode'])) :
            $country = Country::findById($order->_('shiptoAddress')['country']);
            return 'https://jouw.postnl.nl/?D=NL&T=C#!/track-en-trace/' .
                $order->_('shippingType')['barcode'] .
                '/' . strtoupper($country->_('short')) . '/' .
                strtoupper($order->_('shiptoAddress')['zipCode']);
        endif;

        return '';
    }
}
