<?php

namespace VitesseCms\Shop\Helpers;

use VitesseCms\Shop\Interfaces\TaxRateInterface;

/**
 * Class TaxrateHelper
 */
class TaxrateHelper
{
    /**
     * @param TaxRateInterface $taxrate
     * @param float $price
     *
     * @return float
     */
    public static function calculateExVatPrice(
        TaxRateInterface $taxrate,
        float $price
    ): float {
        $taxBase = 100 +  $taxrate->_('taxrate');

        return ( $price / $taxBase ) * 100;
    }
}
