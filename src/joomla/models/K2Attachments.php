<?php
namespace VitesseCms\Joomla\Models;

use VitesseCms\Core\AbstractModel;

/**
 * Class K2Attachments
 */
class K2Attachments extends AbstractModel
{
    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'jos_k2_attachments';
    }
}
