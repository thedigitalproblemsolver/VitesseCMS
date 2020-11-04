<?php

namespace VitesseCms\Import\Forms;

use VitesseCms\Form\AbstractForm;

/**
 * Class UploadFileForm
 */
class UploadFileForm extends AbstractForm
{

    public function initialize()
    {
        $this->_(
            'file',
            '%ADMIN_SELECT_A_FILE%',
            'file',
            [
                'required' => 'required',
            ]
        );

        $this->_(
            'submit',
            '%ADMIN_UPLOAD_FILE%'
        );
    }
}
