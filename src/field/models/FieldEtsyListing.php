<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Core\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Field\AbstractField;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Models\Attributes;

class FieldEtsyListing extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {
        $form->addText('Etsy Id', 'etsyId')
            ->addText('Etsy last sync date', 'etsyLastSyncDate', (new Attributes())->setReadonly(true));

        if ($data !== null && $data->isPublished()) :
            $form->addHtml('<div class="row">
                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-3 col-xl-3"></div>
                <div class="col-xs-12 col-sm-12 col-md-8 col-lg-9 col-xl-9">
                    <a href="'.$this->url->getBaseUri().'etsy/listing/sync/'.$data->getId().'">Create/Update a listing</a>
                </div>
            </div>'
            );
        endif;
    }
}
