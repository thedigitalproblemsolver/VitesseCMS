<?php declare(strict_types=1);

namespace VitesseCms\Install\Forms;

use VitesseCms\Form\AbstractForm;

class SeotoolsForm extends AbstractForm
{
    public function build(): SeotoolsForm
    {
        $this->addText(
            'Google analytic tracking-ID',
            'ga_tracking_id'
        )->addText(
            'Google Site Verification',
            'google_site_verification'
        )->addSubmitButton('create');

        return $this;
    }
}
