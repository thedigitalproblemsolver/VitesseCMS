<?php

namespace VitesseCms\Joomla\Models;

use VitesseCms\Core\AbstractModel;

/**
 * Class VirtuemartProduct
 */
class VirtuemartProduct extends AbstractModel
{

    /**
     * initialize
     */
    public function initialize() {
        //$this->hasOne("product_id", "VirtuemartProductPrice", "product_id");
        /*$this->hasOne("product_id", "produktGerelateerd", "product_id");
        $this->hasOne("product_tax_id", "produktBelasting", "tax_rate_id");
        $this->belongsTo("product_id", "bier", "virtuemartID");*/
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'jos_vm_product';
    }

    /*public static function berekenPrijs($aProdukt = '' ) {
        $prijsMetBtw = $aProdukt['prijs'] * ( 1 + $aProdukt['btw']);
        return $prijsMetBtw;
    }*/

    /*public static function weergavePrijs($aProdukt) {
        return str_replace(
            '.',',',
            number_format(
                round(produkt::berekenPrijs($aProdukt),2),
                2
            )
        );
    }*/

    /*public static function afbeelding($aProdukt) {
        $srcPath = SITE_PATH.'images/shop/'.$aProdukt['artikelnummer'];

        if ( is_file( $srcPath.'.jpg') ) :
                if( getimagesize(SITE_PATH . 'images/shop/' . $aProdukt['artikelnummer'] . '.jpg') ) :
                    return SITE_URL . 'images/shop/' . $aProdukt['artikelnummer'] . '.jpg';
                else :
                    //mail('jasper@biernavigatie.nl','api afbeelding corrupt',SITE_URL . 'images/shop/' . $aProdukt['artikelnummer'] . '.jpg');
                    return SITE_URL.'images/fles_grijs.png';
                endif;
        endif;

        if(
            is_file(SITE_PATH.'../posmanager/'.$aProdukt['artikelnummer'].'.jpg')
            && copy(
                SITE_PATH.'../posmanager/'.$aProdukt['artikelnummer'].'.jpg',
                SITE_PATH.'images/shop/'.$aProdukt['artikelnummer'].'.jpg'
            )
        ) :
            return SITE_URL.'images/shop/'.$aProdukt['artikelnummer'].'.jpg';
        endif;

        return SITE_URL.'images/fles_grijs.png';
    }*/

    /*public function getRelatedProdukten() {
        $aGeretaleerd = explode('|',$this->produktGerelateerd->related_products);
        $aReturn = array();
        foreach( $aGeretaleerd as $iProduktId ) :
            array_push($aReturn,produkt::findfirst($iProduktId));
        endforeach;
        return $aReturn;
    }*/
}
