<?php

namespace VitesseCms\Joomla\Models;

use VitesseCms\Core\AbstractModel;

/**
 * Class K2Item
 */
class K2item extends AbstractModel
{
    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'jos_k2_items';
    }
}
