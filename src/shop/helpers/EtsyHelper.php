<?php declare(strict_types=1);

namespace VitesseCms\Shop\Helpers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\AbstractInjectable;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Spreadshirt\Models\Design;
use Phalcon\Di;

class EtsyHelper extends AbstractInjectable
{
    /**
     * @var \OAuth
     */
    protected $oauth;

    /**
     * @var int
     */
    protected $engine;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var \oauth_client_class
     */
    protected $oauthClient;

    public function __construct()
    {
        $this->baseUrl = 'https://openapi.etsy.com/v2/';

        require_once __DIR__.'/../../../vendor/hatframework/oauth-api/httpclient/http.php';
        require_once __DIR__.'/../../../vendor/hatframework/oauth-api/oauth-api/oauth_client.php';

        $this->oauthClient = new \oauth_client_class();
        $this->oauthClient->debug = true;
        $this->oauthClient->debug_http = true;
        $this->oauthClient->server = 'Etsy';
        $this->oauthClient->configuration_file = __DIR__.'/../../../vendor/hatframework/oauth-api/oauth-api/oauth_configuration.json';
        $this->oauthClient->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
            dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/login_with_etsy.php';
        $this->oauthClient->client_id = $this->setting->get('ETSY_CONSUMER_KEY');
        $this->oauthClient->client_secret = $this->setting->get('ETSY_CONSUMER_SECRET');
        $this->oauthClient->access_token = $this->setting->get('ETSY_ACCESS_TOKEN');
        $this->oauthClient->access_token_secret = $this->setting->get('ETSY_ACCESS_SECRET');

        /*if(strlen($client->client_id) == 0
            || strlen($client->client_secret) == 0)
            die('Please go to Etsy Developers page https://www.etsy.com/developers/register , '.
                'create an application, and in the line '.$application_line.
                ' set the client_id to key string and client_secret with shared secret. '.
                'The Callback URL must be '.$client->redirect_uri);
        */
        if(($success = $this->oauthClient->Initialize()))
        {
            if(($success = $this->oauthClient->Process()))
            {
                if(strlen($this->oauthClient->access_token))
                {
                    $success = $this->oauthClient->CallAPI(
                        $this->baseUrl.'users/__SELF__',
                        'GET', [], ['FailOnAccessError'=>true], $user);
                }
            }
            $success = $this->oauthClient->Finalize($success);
        }

        /*$this->oauth = new \OAuth(
            SettingHelper::_('ETSY_CONSUMER_KEY'),
            SettingHelper::_('ETSY_CONSUMER_SECRET'),
            OAUTH_SIG_METHOD_HMACSHA1,
            OAUTH_AUTH_TYPE_URI
        );

        if (\defined('OAUTH_REQENGINE_CURL')) {
            $this->engine = OAUTH_REQENGINE_CURL;
            $this->oauth->setRequestEngine(OAUTH_REQENGINE_CURL);
        } elseif (\defined('OAUTH_REQENGINE_STREAMS')) {
            $this->engine = OAUTH_REQENGINE_STREAMS;
            $this->oauth->setRequestEngine(OAUTH_REQENGINE_STREAMS);
        } else {
            throw new \Exception('Warning: cURL engine not present on OAuth PECL package: sudo apt-get install libcurl4-dev or sudo yum install curl-devel');
        }

        $this->oauth->setToken(
            SettingHelper::_('ETSY_ACCESS_TOKEN'),
            SettingHelper::_('ETSY_ACCESS_SECRET')
        );

        $this->baseUrl = 'https://openapi.etsy.com/v2/';*/
    }

    /**
     * @param Item $item
     *
     * @return mixed
     * @throws \OAuthException
     * @throws \Phalcon\Mvc\Collection\Exception
     */
    public function createListingFromItem(Item $item)
    {
        $clothingCategory = Item::findById($item->_('parentId'));

        $params = [
            'title'                => $item->_('name'),
            'description'          => $this->builDescription($item),
            'price'                => $item->_('price_sale'),
            'shipping_template_id' => $this->setting->get('ETSY_SHIPPING_TEMPLATEID'),
            'category_id'          => (int)$clothingCategory->_('etsyCategoryId'),
            'quantity'             => 2,
            'shop_id'              => $this->setting->get('ETSY_SHOP_ID'),
            'who_made'             => 'collective',
            'is_supply'            => true,
            'when_made'            => '2010_2018',
            'language' => Di::getDefault()->get('configuration')->getLanguageShort(),
        ];

        return $this->fetch('listings', $params);
    }

    /**
     * @param string $imagePath
     * @param int $listingId
     *
     * @return mixed
     * @throws \OAuthException
     */
    public function addImageToListing(string $imagePath, int $listingId)
    {
        return $this->fetch('listings/' . $listingId . '/images', ['@image' => '@' . $imagePath . ';type=image/jpeg']);
    }

    /**
     * @param int $listingId
     *
     * @return mixed
     * @throws \OAuthException
     */
    public function getListing(int $listingId)
    {
        return $this->fetch('listings/' . $listingId, [], OAUTH_HTTP_METHOD_GET);
    }

    /**
     * @param int $listingId
     *
     * @return mixed
     * @throws \OAuthException
     */
    public function getInventory(int $listingId)
    {
        return $this->fetch('listings/' . $listingId. '/inventory', [], OAUTH_HTTP_METHOD_GET);
    }

    /**
     * @param Item $item
     *
     * @return mixed
     * @throws \OAuthException
     */
    public function updateInventoryFromItem(Item $item)
    {
        $products = [];
        if(!empty($item->_('variations'))) :
            foreach((array)$item->_('variations') as $variation) :
                $products[] = $this->inventoryItemFactory(
                    1,
                    (int)$this->getSizeId($variation['size']),
                    (int)$variation['stock'],
                    19.95
                );
            endforeach;

            return $this->fetch('listings/' . $item->_('etsyId').'/inventory',
                [
                    'products' => json_encode($products),
                    'quantity_on_property' => '200,62809790395',
                    /*'sku_on_property' => '',
                    'price_on_property' => '',*/
                ],
                'PUT'
            );
        endif;

        return null;
    }

    /**
     * @param string $apiCall
     * @param array $params
     * @param string $method
     *
     * @return mixed
     * @throws \OAuthException
     */
    protected function fetch(string $apiCall, array $params = [], $method = 'POST' )
    {
        var_dump($this->baseUrl.$apiCall);
        var_dump($params);
        $this->oauthClient->CallAPI(
            $this->baseUrl.$apiCall,
            $method,
            $params,
            ['FailOnAccessError'=>true],
            $response
        );
        $this->oauthClient->error;
        return $response;
    }

    /**
     * @param Item $item
     *
     * @return string
     */
    protected function builDescription(Item $item):string
    {
        $description = strip_tags($item->_('introtext'));

        if(MongoUtil::isObjectId($item->_('design'))) :
            $design = Design::findById($item->_('design'));
            if($design) :
                if(!empty($description)) :
                    $description .= "

";
                endif;
                $description .= strip_tags($design->_('introtext'));
            endif;
        endif;

        if(MongoUtil::isObjectId($item->_('manufacturer'))) :
            $manufacturer = Item::findById($item->_('manufacturer'));
            if($manufacturer) :
                $description .= "
".$manufacturer->_('name')."

".strip_tags($manufacturer->_('introtext'));
            endif;
        endif;

        return trim($description);
    }

    /**
     * @param $size
     *
     * @return int
     */
    protected function getSizeId(string $size): int
    {
        switch (strtoupper($size)){
            case 'S':
                return 2015;
                break;
            case 'M':
                return 2017;
                break;
            case 'L':
                return 2019;
                break;
            case 'XL':
                return 2022;
                break;
            case 'XXL':
                return 2025;
                break;

        }

        /*
         * <option value="2011">XXS Letter size</option>
         * <option value="2013">XS Letter size</option>
         * <option value="2015">S Letter size</option>
         * <option value="2017">M Letter size</option>
         * <option value="2022">XL Letter size</option>
         * <option value="2025">2X Letter size</option>
         * <option value="2027">3X Letter size</option>
         * <option value="2029">4X Letter size</option>
         *
         */

        echo 'Maat onbekend : '. $size;
        die();
    }

    /**
     * @param int $colorId
     * @param int $sizeId
     * @param int $quantity
     * @param float $price
     *
     * @return mixed
     */
    protected function inventoryItemFactory(
        int $colorId,
        int $sizeId,
        int $quantity,
        float $price
    ) {
        $enabled = '1';
        if($quantity < 1 ) :
            $enabled = '0';
        endif;

        /*unserialize('O:8:"stdClass":5:{s:10:"product_id";i:2519322786;s:3:"sku";s:0:"";s:15:"property_values";a:2:{i:0;O:8:"stdClass":6:{s:11:"property_id";i:200;s:13:"property_name";s:13:"Primary color";s:8:"scale_id";N;s:10:"scale_name";N;s:9:"value_ids";a:1:{i:0;i:1;}s:6:"values";a:1:{i:0;s:5:"Black";}}i:1;O:8:"stdClass":6:{s:11:"property_id";i:62809790395;s:13:"property_name";s:4:"Size";s:8:"scale_id";i:42;s:10:"scale_name";s:11:"Letter size";s:9:"value_ids";a:1:{i:0;i:2019;}s:6:"values";a:1:{i:0;s:1:"L";}}}s:9:"offerings";a:1:{i:0;O:8:"stdClass":5:{s:11:"offering_id";i:2376563313;s:5:"price";O:8:"stdClass":6:{s:6:"amount";i:2000;s:7:"divisor";i:100;s:13:"currency_code";s:3:"EUR";s:24:"currency_formatted_short";s:8:"€20.00";s:23:"currency_formatted_long";s:12:"€20.00 EUR";s:22:"currency_formatted_raw";s:5:"20.00";}s:8:"quantity";i:2;s:10:"is_enabled";i:1;s:10:"is_deleted";i:0;}}s:10:"is_deleted";i:0;}');*/

        $inventoryItem = unserialize('O:8:"stdClass":5:{s:10:"product_id";i:2519322786;s:3:"sku";s:0:"";s:15:"property_values";a:2:{i:0;O:8:"stdClass":6:{s:11:"property_id";i:200;s:13:"property_name";s:13:"Primary color";s:8:"scale_id";N;s:10:"scale_name";N;s:9:"value_ids";a:1:{i:0;i:1;}s:6:"values";a:1:{i:0;s:5:"Black";}}i:1;O:8:"stdClass":6:{s:11:"property_id";i:62809790395;s:13:"property_name";s:4:"Size";s:8:"scale_id";i:42;s:10:"scale_name";s:11:"Letter size";s:9:"value_ids";a:1:{i:0;i:2019;}s:6:"values";a:1:{i:0;s:1:"L";}}}s:9:"offerings";a:1:{i:0;O:8:"stdClass":5:{s:11:"offering_id";i:2376563313;s:5:"price";O:8:"stdClass":6:{s:6:"amount";i:2000;s:7:"divisor";i:100;s:13:"currency_code";s:3:"EUR";s:24:"currency_formatted_short";s:8:"€20.00";s:23:"currency_formatted_long";s:12:"€20.00 EUR";s:22:"currency_formatted_raw";s:5:"20.00";}s:8:"quantity";i:2;s:10:"is_enabled";i:1;s:10:"is_deleted";i:0;}}s:10:"is_deleted";i:0;}',[\stdClass::class]);

        unset(
            $inventoryItem->product_id,
            $inventoryItem->sku,
            $inventoryItem->property_values[0]->scale_id,
            $inventoryItem->property_values[0]->scale_name,
            $inventoryItem->property_values[0]->property_name,
            $inventoryItem->property_values[0]->values,
            $inventoryItem->property_values[1]->scale_id,
            $inventoryItem->property_values[1]->scale_name,
            $inventoryItem->property_values[1]->property_name,
            $inventoryItem->property_values[1]->values,
            $inventoryItem->offerings[0]->price,
            $inventoryItem->offerings[0]->is_deleted,
            $inventoryItem->is_deleted
        );

        $inventoryItem->property_values[0]->value_ids[0] = $colorId;
        $inventoryItem->property_values[1]->value_ids[0] = $sizeId;

        $inventoryItem->offerings[0]->price = $price;
        $inventoryItem->offerings[0]->quantity = 1;
        $inventoryItem->offerings[0]->is_enabled = $enabled;

        return $inventoryItem;
    }
}
