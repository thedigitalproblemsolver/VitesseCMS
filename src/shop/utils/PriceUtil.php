<?php

namespace VitesseCms\Shop\Utils;

/**
 * Class PriceUtil
 */
class PriceUtil
{
    /**
     * @param float $price
     *
     * @return string
     */
    public static function formatDisplay(float $price ) : string
    {
        return number_format($price, 2, ',', '.');
    }
}
