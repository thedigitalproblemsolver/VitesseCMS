<?php declare(strict_types=1);

namespace VitesseCms\Communication\Services;

use DrewM\MailChimp\MailChimp;
use VitesseCms\Content\Models\Item;
use VitesseCms\Setting\Models\Setting;
use VitesseCms\Core\Services\ConfigService;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Setting\Services\SettingService;
use VitesseCms\Shop\Models\Order;
use Phalcon\Exception;
use Phalcon\Session\Adapter\Files as Session;

class MailchimpService
{
    /**
     * @var MailChimp
     */
    protected $mailchimp;

    /**
     * @var string
     */
    protected $storeId;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var SettingService
     */
    protected $setting;

    /**
     * @var ConfigService
     */
    protected $configuration;

    /**
     * @var UrlService
     */
    protected $url;

    public function __construct(
        Session $session,
        SettingService $setting,
        UrlService $url,
        ConfigService $configuration
    ) {
        $this->session = $session;
        $this->setting = $setting;
        $this->url = $url;
        $this->configuration = $configuration;

        if (
            $this->setting->has('MAILCHIMP_API_KEY')
            && $this->setting->has('MAILCHIMP_STORE_LISTID')
        ) :
            try {
                $this->mailchimp = new MailChimp($this->setting->get('MAILCHIMP_API_KEY'));
            } catch (\Exception $exception) {
                echo $exception->getMessage();
                die();
            }

            if (empty($this->setting->get('MAILCHIMP_STORE_ID'))) :
                try {
                    Setting::setFindValue('calling_name', 'MAILCHIMP_STORE_ID');
                    Setting::setFindPublished(false);
                    /** @var Setting $mailchipStoreIdSetting */
                    $mailchipStoreIdSetting = Setting::findFirst();
                    $mailchipStoreIdSetting->set('type', 'SettingText')
                        ->set('value', uniqid('', true), true)
                        ->save();
                    $this->createStore(
                        $mailchipStoreIdSetting->_('value'),
                        $this->setting->get('MAILCHIMP_STORE_LISTID'),
                        $this->url->getBaseUri()
                    );
                } catch (Exception $exception) {
                    echo $exception->getMessage();
                    die();
                }
            else :
                $this->setStoreId($this->setting->get('MAILCHIMP_STORE_ID'));
            endif;
        endif;
    }

    public function createStore(string $storeId, string $listId, string $name): array
    {
        $this->storeId = $storeId;

        return $this->mailchimp->post('/ecommerce/stores', [
            'id'            => $storeId,
            'list_id'       => $listId,
            'name'          => $name,
            'currency_code' => 'EUR',
        ]);
    }

    public function setStoreId(string $storeId): MailchimpService
    {
        $this->storeId = $storeId;

        return $this;
    }

    public function addOrder(Order $order, string $mailchimpCampaignId): array
    {
        $shopper = (array)$order->_('shopper');
        $user = (array)$shopper['user'];

        //$this->createProduct();

        return $this->mailchimp->post('/ecommerce/stores/'.$this->storeId.'/orders', [
            'id'            => (string)$order->getNumber(),
            'customer'      => [
                'id'            => $shopper['userId'],
                'email_address' => $user['email'],
                'opt_in_status' => false,
            ],
            'campaign_id'   => $mailchimpCampaignId,
            'currency_code' => 'EUR',
            'order_total'   => $order->getTotal(),
            'lines'         => [
                [
                    'id'                 => '1',
                    'product_id'         => '1',
                    'product_variant_id' => '1',
                    'quantity'           => 1,
                    'price'              => 1,
                ],
            ],
        ]);
    }

    public function createProduct(Item $item): array
    {
        return (array)$this->mailchimp->post(
            '/ecommerce/stores/'.$this->storeId.'/products',
            $this->createProductFromItem($item)
        );
    }

    public function updateProduct(Item $item): array
    {
        return (array)$this->mailchimp->patch(
            '/ecommerce/stores/'.$this->storeId.'/products/'.$item->getId(),
            $this->createProductFromItem($item)
        );
    }

    public function getProductById(string $id): array
    {
        return (array)$this->mailchimp->get('/ecommerce/stores/'.$this->storeId.'/products/'.$id);
    }

    public function getStores(): array
    {
        return (array)$this->mailchimp->get('/ecommerce/stores/');
    }

    protected function createProductFromItem(Item $item): array
    {
        return [
            'id'        => (string)$item->getId(),
            'title'     => $item->_('name'),
            'url'       => $this->url->getBaseUri().$item->_('slug'),
            'image_url' => $this->configuration->getUploadUri().$item->_('image'),
            'variants'  => [
                [
                    'id'    => (string)$item->getId(),
                    'title' => $item->_('name'),
                    'price' => $item->_('price_sale'),
                ],
            ],
        ];
    }
}
