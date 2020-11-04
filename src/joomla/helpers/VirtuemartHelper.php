<?php

namespace VitesseCms\Joomla\Helpers;

use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Interfaces\BaseObjectInterface;
use VitesseCms\Joomla\Models\VirtuemartProduct;
use VitesseCms\Joomla\Models\VirtuemartProductPrice;
use VitesseCms\Joomla\Models\VirtuemartTaxRate;
use VitesseCms\Shop\Models\TaxRate;

/**
 * Class VirtuemartHelper
 */
class VirtuemartHelper
{
    /**
     * @var BaseObjectInterface
     */
    protected static $productBase;

    /**
     * @var BaseObjectInterface
     */
    protected static $productPrice;

    /**
     * @var BaseObjectInterface
     */
    protected static $productTaxRate;

    /**
     * @param string $virtuemartId
     */
    public static function init(string $virtuemartId)
    {
        self::$productBase = VirtuemartProduct::findFirst("product_id = " . $virtuemartId);
        self::$productPrice = VirtuemartProductPrice::findFirst("product_id = " . self::$productBase->_('product_id'));
        self::$productTaxRate = VirtuemartTaxRate::findFirst('tax_rate_id = ' . self::$productBase->_('product_tax_id'));
    }

    /**
     * @param Item $item
     *
     * @return Item
     */
    public static function bindTaxrate(Item $item)
    {
        self::init($item->_('virtuemartId'));

        $taxNumber = (int)round(self::$productTaxRate->_('tax_rate') * 100, 0);
        TaxRate::setFindValue("taxrate", $taxNumber);
        $taxRate = TaxRate::findFirst();
        if (!$taxRate) :
            die('geen TaxRate gevonden');
        endif;
        $item->set('taxrate', (string)$taxRate->getId());

        return $item;
    }

    /**
     * @param Item $item
     *
     * @return Item
     */
    public static function bindPrices(Item $item)
    {
        self::init($item->_('virtuemartId'));
        $price_sale = round(self::$productPrice->_('product_price') * ( 1 + self::$productTaxRate->_('tax_rate') ),2);

        $item->set('price', self::$productPrice->_('product_price'));
        $item->set('price_purchase', self::$productBase->_('product_cost_price'));
        $item->set('price_sale', $price_sale);

        return $item;
    }
}
