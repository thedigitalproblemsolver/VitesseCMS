<?php

namespace VitesseCms\Joomla\Models;

use VitesseCms\Core\AbstractModel;

/**
 * Class Comprofiler
 */
class Comprofiler extends AbstractModel
{

    /**
     * initialize
     */
    public function initialize()
    {
        //$this->belongsTo("product_id", "VirtuemartProduct", "product_id");
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'jos_comprofiler';
    }
}
