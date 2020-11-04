<?php
namespace VitesseCms\Joomla\Models;

use VitesseCms\Core\AbstractModel;

/**
 * Class K2Category
 */
class K2Category extends AbstractModel
{
    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'jos_k2_categories';
    }
}
