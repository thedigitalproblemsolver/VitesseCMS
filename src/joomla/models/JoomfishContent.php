<?php

namespace VitesseCms\Joomla\Models;

use VitesseCms\Core\AbstractModel;

/**
 * Class JoomfishContent
 */
class JoomfishContent extends AbstractModel
{
    /**
     * @return string
     */
    public function getSource() : string
    {
        return 'jos_jf_content';
    }
}
