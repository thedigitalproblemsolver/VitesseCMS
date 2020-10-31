<?php

namespace VitesseCms\Install\Controllers;

use VitesseCms\Setting\Models\Setting;
use VitesseCms\Install\AbstractCreatorController;
use VitesseCms\Setting\Factory\SettingFactory;
use VitesseCms\Shop\Models\Country;

/**
 * Class UserController
 */
class UserController extends AbstractCreatorController
{

    /**
     * createPersonalInformation
     */
    public function createPersonalInformation()
    {
        $fields = [
            'Voornaam'      => [
                'calling_name'      => 'firstName',
                'type'              => 'text',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Tussenvoegsel' => [
                'calling_name'      => 'middleName',
                'type'              => 'text',
                'datafieldSettings' => ['inputType' => 'text']
            ],
            'Achternaam'    => [
                'calling_name'      => 'lastName',
                'type'              => 'text',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Straat'        => [
                'calling_name'      => 'street',
                'type'              => 'text',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Huisnummer'    => [
                'calling_name'      => 'houseNumber',
                'type'              => 'text',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Postcode'      => [
                'calling_name'      => 'zipCode',
                'type'              => 'text',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Woonplaats'    => [
                'calling_name'      => 'city',
                'type'              => 'text',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Land' => [
                'calling_name'      => 'country',
                'type'              => 'FieldModel',
                'datafieldSettings' => ['model' => Country::class],
                'required'          => true,
            ],
            'Telefoon'      => [
                'calling_name'      => 'phoneNumber',
                'type'              => 'text',
                'datafieldSettings' => ['inputType' => 'tel'],
                'required'          => true,
            ],
        ];
        $fieldIds = $this->createDatafields($fields,'calling_name');

        $personalInformationGroup = $this->createDatagroup(
            'NAW',
            'name.' . $this->configuration->getLanguageShort(),
            '',
            'user',
            $fieldIds
        );

        Setting::setFindValue('calling_name','USER_DATAGROUP_PERSONALINFORMATION');
        if(Setting::count() === 0 ) :
            SettingFactory::create(
                'USER_DATAGROUP_PERSONALINFORMATION',
                'SettingDatagroup',
                (string)$personalInformationGroup->getId(),
                'User - Datagroup - Personal Information',
                true
            )->save();
        endif;
    }
}
