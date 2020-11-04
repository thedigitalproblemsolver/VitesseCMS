<?php declare(strict_types=1);

namespace VitesseCms\Install\Controllers;

use VitesseCms\Block\Models\BlockItemlist;
use VitesseCms\Block\Models\BlockShopCart;
use VitesseCms\Block\Models\BlockShopCheckoutInformation;
use VitesseCms\Block\Models\BlockShopCheckoutSummary;
use VitesseCms\Block\Models\BlockShopPaymentResult;
use VitesseCms\Block\Models\BlockShopUserOrders;
use VitesseCms\Block\Models\BlockUserChangePassword;
use VitesseCms\Block\Models\BlockUserLogin;
use VitesseCms\Core\Models\Datagroup;
use VitesseCms\Setting\Models\Setting;
use VitesseCms\Core\Utils\DirectoryUtil;
use VitesseCms\Install\AbstractCreatorController;
use VitesseCms\Setting\Factory\SettingFactory;
use VitesseCms\Shop\Enum\OrderStateEnum;
use VitesseCms\Shop\Factories\CountryFactory;
use VitesseCms\Shop\Factories\OrderStateFactory;
use VitesseCms\Shop\Factories\PaymentFactory;
use VitesseCms\Shop\Factories\ShippingFactory;
use VitesseCms\Shop\Factories\TaxrateFactory;
use VitesseCms\Shop\Models\Country;
use VitesseCms\Shop\Models\OrderState;
use VitesseCms\Shop\Models\Payment;
use VitesseCms\Shop\Models\Shipping;
use VitesseCms\Shop\Models\TaxRate;
use VitesseCms\User\Models\PermissionRole;
use Phalcon\Di;

class ShopController extends AbstractCreatorController
{
    /**
     * @var Datagroup
     */
    protected $mainProductCategory;

    /**
     * @var Datagroup
     */
    protected $subProductCategory;

    /**
     * @var Datagroup
     */
    protected $productGroup;

    /**
     * @var array
     */
    protected $mainProductCategoryItem;

    /**
     * @var array
     */
    protected $subProductCategoryItem;

    /**
     * @var Datagroup
     */
    protected $checkoutDatagroup;

    /**
     * @var array
     */
    protected $checkoutPages;

    public function createAction()
    {
        DirectoryUtil::copy(
            Di::getDefault()->get('config')->get('defaultTemplateDir').'samples/images',
            Di::getDefault()->get('config')->get('uploadDir')
        );

        $this->createShopRoles();
        $this->createShopTaxrates();
        $this->createProductsStructure();
        $this->createShopOrderstates();
        $this->createShopCheckout();

        $user = new UserController();
        $user->createPersonalInformation();
        $this->createShopShopper();
        $this->createShopShipTo();

        $this->createShopBlocks();

        $this->flash->success('Webshop created');
        parent::redirect();
    }

    /**
     * createShopRoles
     */
    protected function createShopRoles(): void
    {
        $this->createBasicPermissionRoles();

        PermissionRole::setFindValue('calling_name', 'admin');
        $adminPermissionRole = PermissionRole::findFirst();
        $adminPermissionRole->set('hasChildren', true)->save();

        $roles = [
            'Shop - admin' => [
                'calling_name' => 'shopadmin',
                'adminAccess'  => true,
                'parentId'     => (string)$adminPermissionRole->getId(),
            ],
        ];

        $this->createPermissionRoles($roles);
    }

    /**
     * createShopTaxrates
     */
    protected function createShopTaxrates(): void
    {
        TaxRate::setFindValue('taxrate', 21);
        if (TaxRate::count() === 0) :
            TaxrateFactory::create('21', 21, true)->save();
        endif;

        TaxRate::setFindValue('taxrate', 6);
        if (TaxRate::count() === 0) :
            TaxrateFactory::create('6', 6, true)->save();
        endif;
    }

    /**
     * createProductsStructure
     */
    protected function createProductsStructure(): void
    {
        $fields = [
            'Item naam'  => [
                'calling_name'      => 'name',
                'type'              => 'FieldText',
                'datafieldSettings' => [
                    'inputType' => 'text',
                    'multilang' => true,
                ],
                'required'          => true,
                'slug'              => true,
            ],
            'Afbeelding' => [
                'calling_name'      => 'image',
                'type'              => 'FieldImage',
                'datafieldSettings' => [
                    'allowedFiletypeGroups' => ['rasterizedImages'],
                ],
            ],
        ];
        $fieldIds = $this->createDatafields($fields, 'calling_name');

        $this->mainProductCategory = $this->createDatagroup(
            'Hoofd categorie',
            'name.'.$this->configuration->getLanguageShort(),
            'template/core/views/blocks/MainContent/shop_category_overview',
            'webshopProduct',
            $fieldIds,
            true
        );

        $this->subProductCategory = $this->createDatagroup(
            'Sub categorie',
            'name.'.$this->configuration->getLanguageShort(),
            'template/core/views/blocks/MainContent/shop_product_overview',
            'webshopProduct',
            $fieldIds,
            true,
            (string)$this->mainProductCategory->getId()
        );

        $fields = [
            'Item naam'        => [
                'calling_name'      => 'name',
                'type'              => 'FieldText',
                'datafieldSettings' => [
                    'inputType' => 'text',
                    'multilang' => true,
                ],
                'required'          => true,
                'slug'              => true,
            ],
            'Introtext'        => [
                'calling_name'      => 'introtext',
                'type'              => 'FieldTexteditor',
                'datafieldSettings' => [
                    'multilang' => true,
                ],
            ],
            'Bodytext'         => [
                'calling_name'      => 'bodytext',
                'type'              => 'FieldTexteditor',
                'datafieldSettings' => [
                    'multilang' => true,
                ],
            ],
            'Afbeelding'       => [
                'calling_name'      => 'image',
                'type'              => 'FieldImage',
                'datafieldSettings' => [
                    'allowedFiletypeGroups' => ['rasterizedImages'],
                ],
            ],
            'Prijs'            => [
                'calling_name'      => 'price',
                'type'              => 'FieldPrice',
                'datafieldSettings' => [],
            ],
            'Belasting'        => [
                'calling_name'      => 'taxrate',
                'type'              => 'FieldModel',
                'datafieldSettings' => [
                    'model' => TaxRate::class,
                ],
            ],
            'Voorraad'         => [
                'calling_name'      => 'stock',
                'type'              => 'FieldText',
                'datafieldSettings' => [
                    'inputType' => 'number',
                ],
            ],
            'Uit voorraad'     => [
                'calling_name'      => 'outOfStock',
                'type'              => 'FieldCheckbox',
                'datafieldSettings' => [],
            ],
            'Winkelwagen knop' => [
                'calling_name'      => 'addtocart',
                'type'              => 'FieldAddtocart',
                'datafieldSettings' => [],
            ],
        ];
        $fieldIds = $this->createDatafields($fields, 'calling_name');

        $this->productGroup = $this->createDatagroup(
            'Product',
            'name.'.$this->configuration->getLanguageShort(),
            'template/core/views/blocks/MainContent/shop_product',
            'webshopProduct',
            $fieldIds,
            true,
            (string)$this->subProductCategory->getId()
        );

        $this->mainProductCategoryItem = $this->createItems(
            [
                'Hoofd categorie' => [
                    'image' => ['value' => 'shop_main_category.jpg'],
                ],
            ],
            'name.'.$this->configuration->getLanguageShort(),
            $this->mainProductCategory
        );

        $this->subProductCategoryItem = $this->createItems(
            [
                'Sub categorie' => [
                    'image' => ['value' => 'shop_sub_category.jpg'],
                ],
            ],
            'name.'.$this->configuration->getLanguageShort(),
            $this->subProductCategory,
            $this->mainProductCategoryItem['pages']['Hoofd categorie']
        );

        TaxRate::setFindValue('taxrate', 21);
        $taxrate = TaxRate::findFirst();

        $productValues = [
            'introtext'      => [
                'value'     => '<p>Dit is de plek voor wat intro-tekst</p>',
                'multilang' => true,
            ],
            'bodytext'       => [
                'value'     => '<p>Dit is de plek voor wat body-tekst</p>',
                'multilang' => true,
            ],
            'image'          => ['value' => 'product_in_stock.jpg'],
            'price'          => ['value' => 16.4876],
            'price_purchase' => ['value' => 10.00],
            'price_sale'     => ['value' => 19.95],
            'taxrate'        => ['value' => (string)$taxrate->getId()],
            'taxrateName'    => ['value' => $taxrate->_('name')],
            'stock'          => ['value' => 16],
            'outOfStock'     => ['value' => false],
            'addtocart'      => ['value' => true],
        ];
        $this->createItems(
            ['Product in voorraad' => $productValues],
            'name.'.$this->configuration->getLanguageShort(),
            $this->productGroup,
            $this->subProductCategoryItem['pages']['Sub categorie']
        );

        $productValues['image']['value'] = 'product_not_in_stock.jpg';
        $productValues['stock']['value'] = 0;
        $productValues['outOfStock']['value'] = true;
        $this->createItems(
            ['Product uit voorraad' => $productValues],
            'name.'.$this->configuration->getLanguageShort(),
            $this->productGroup,
            $this->subProductCategoryItem['pages']['Sub categorie']
        );
    }

    /**
     * createShopOrderstates
     */
    protected function createShopOrderstates(): void
    {
        OrderState::setFindValue('calling_name', 'PENDING');
        OrderState::setFindValue('parentId', null);
        $parentPending = OrderState::findFirst();
        if (!$parentPending) :
            $parentPending = OrderStateFactory::create(
                'In bewerking',
                'PENDING',
                true
            );
            $parentPending->save();
        endif;

        OrderState::setFindValue('calling_name', 'CONFIRMED');
        OrderState::setFindValue('parentId', (string)$parentPending->getId());
        $parentConfirmed = OrderState::findFirst();
        if (!$parentConfirmed) :
            $parentConfirmed = OrderStateFactory::create(
                'Bevestigd',
                'CONFIRMED',
                true,
                (string)$parentPending->getId(),
                'decrease'
            );
            $parentConfirmed->save();
        endif;

        OrderState::setFindValue('calling_name', 'CANCELLED');
        OrderState::setFindValue('parentId', (string)$parentPending->getId());
        $parentCancelled = OrderState::findFirst();
        if (!$parentCancelled) :
            $parentCancelled = OrderStateFactory::create(
                'Geannuleerd',
                'CANCELLED',
                true,
                (string)$parentPending->getId(),
                '',
                '<p>Je bestelling is geannuleerd</p>',
                'Je bestelling is geannuleerd',
                'warning'
            );
            $parentCancelled->save();
        endif;

        OrderState::setFindValue('calling_name', 'CANCELLED');
        OrderState::setFindValue('parentId', (string)$parentConfirmed->getId());
        if (OrderState::count() === 0) :
            $parentConfirmed->set('hasChildren', true)->save();
            $cancelledClone = clone $parentCancelled;
            $cancelledClone->setId(null);
            $cancelledClone->set('parentId', (string)$parentConfirmed->getId())
                ->set('stockAction', OrderStateEnum::STOCK_ACTION_INCREASE)
                ->set('ordering', 4)
                ->save();
        endif;

        OrderState::setFindValue('calling_name', 'ERROR');
        OrderState::setFindValue('parentId', (string)$parentConfirmed->getId());
        if (OrderState::count() === 0) :
            OrderStateFactory::create(
                'Betalings fout',
                'ERROR',
                true,
                (string)$parentConfirmed->getId(),
                'increase',
                '',
                'Er is wat met de betaling misgegegaan',
                'error',
                [],
                false,
                false,
                5
            )->save();
        endif;

        OrderState::setFindValue('calling_name', 'PAID');
        OrderState::setFindValue('parentId', (string)$parentConfirmed->getId());
        $parentPaid = OrderState::findFirst();
        if (!$parentPaid) :
            $parentPaid = OrderStateFactory::create(
                'Betaald',
                'PAID',
                true,
                (string)$parentConfirmed->getId(),
                '',
                '<h1>Hartelijk dank voor je aankoop</h1>',
                'De betaling is geslaagd',
                'success',
                [],
                true,
                true,
                3
            );
            $parentPaid->save();
        endif;

        OrderState::setFindValue('calling_name', 'SHIPPED');
        OrderState::setFindValue('parentId', (string)$parentPaid->getId());
        $parentShipped = OrderState::findFirst();
        if (!$parentShipped) :
            $parentShipped = OrderStateFactory::create(
                'Verzonden',
                'SHIPPED',
                true,
                (string)$parentPaid->getId(),
                '',
                '',
                '',
                '',
                [],
                false,
                false
            );
            $parentShipped->save();
        endif;

        OrderState::setFindValue('calling_name', 'BANKTRANSFER');
        OrderState::setFindValue('parentId', (string)$parentConfirmed->getId());
        $parentBankTransfer = OrderState::findFirst();
        if (!$parentBankTransfer) :
            $parentBankTransfer = OrderStateFactory::create(
                'Bankoverschrijving',
                'BANKTRANSFER',
                true,
                (string)$parentConfirmed->getId(),
                '',
                '<h1>Hartelijk dank voor je aankoop</h1><p>Nadat het geld is overgeschreven, worden de producten verzonden.</p>',
                'Nadat het geld is overgeschreven, worden de producten verzonden.',
                'notice',
                [],
                true,
                false,
                2
            );
            $parentBankTransfer->save();
        endif;

        OrderState::setFindValue('calling_name', 'CANCELLED');
        OrderState::setFindValue('parentId', (string)$parentBankTransfer->getId());
        if (OrderState::count() === 0) :
            $parentBankTransfer->set('hasChildren', true)->save();
            $cancelledClone = clone $parentCancelled;
            $cancelledClone->setId(null);
            $cancelledClone->set('parentId', (string)$parentBankTransfer->getId())
                ->set('stockAction', OrderStateEnum::STOCK_ACTION_INCREASE)
                ->set('ordering', 2)
                ->save();
        endif;

        OrderState::setFindValue('calling_name', 'PAID');
        OrderState::setFindValue('parentId', (string)$parentBankTransfer->getId());
        $parentBankTransferPaid = OrderState::findFirst();
        if (!$parentBankTransferPaid) :
            $parentBankTransfer->set('hasChildren', true)->save();
            $parentBankTransferPaid = clone $parentPaid;
            $parentBankTransferPaid->setId(null);
            $parentBankTransferPaid->set('parentId', (string)$parentBankTransfer->getId());
            $parentBankTransferPaid->set('ordering', 1);
            $parentBankTransferPaid->save();
        endif;

        OrderState::setFindValue('calling_name', 'SHIPPED');
        OrderState::setFindValue('parentId', (string)$parentBankTransferPaid->getId());
        if (OrderState::count() === 0) :
            $parentBankTransferPaid->set('hasChildren', true)->save();
            $shippedClone = clone $parentShipped;
            $shippedClone->setId(null);
            $shippedClone->set('parentId', (string)$parentBankTransferPaid->getId());
            $shippedClone->save();
        endif;
    }

    /**
     * createShopCheckout
     */
    protected function createShopCheckout(): void
    {
        $fields = [
            'Item naam' => [
                'calling_name'      => 'name',
                'type'              => 'FieldText',
                'datafieldSettings' => [
                    'inputType' => 'text',
                    'multilang' => true,
                ],
                'required'          => true,
                'slug'              => true,
            ],
        ];
        $fieldIds = $this->createDatafields($fields, 'calling_name');

        $this->checkoutDatagroup = $this->createDatagroup(
            'Afrekenflow',
            'name.'.$this->configuration->getLanguageShort(),
            'template/core/views/blocks/MainContent/empty',
            'webshopContent',
            $fieldIds,
            false,
            null,
            'ordering'
        );

        if (!$this->setting->has('SHOP_DATAGROUP_CHECKOUT') === '') :
            SettingFactory::create(
                'SHOP_DATAGROUP_CHECKOUT',
                'SettingText',
                (string)$this->checkoutDatagroup->getId(),
                'Webshop - datagroup - checkout',
                true
            )->save();
        endif;

        $this->checkoutPages = $this->createItems(
            [
                'Winkelwagen' => [],
                'Je gegevens' => [],
                'Overzicht'   => [],
                'Betalen'     => [],
                'Bedankt'     => [],
            ],
            'name.'.$this->configuration->getLanguageShort(),
            $this->checkoutDatagroup
        );

        $fields = [
            'Item naam' => [
                'calling_name'      => 'name',
                'type'              => 'FieldText',
                'datafieldSettings' => [
                    'inputType' => 'text',
                    'multilang' => true,
                ],
                'required'          => true,
            ],
        ];
        $fieldIds = $this->createDatafields($fields, 'calling_name');

        $ratingDatagroup = $this->createDatagroup(
            'Waarderingen ',
            'name.'.$this->configuration->getLanguageShort(),
            'template/core/views/blocks/MainContent/empty',
            'webshopContent',
            $fieldIds,
            false,
            null,
            'ordering'
        );

        $this->createItems(
            [
                'Excellent!' => [],
                'Zeer Goed'  => [],
                'Goed'       => [],
                'Matig'      => [],
                'Tevreden'   => [],
                'Slecht'     => [],
            ],
            'name.'.$this->configuration->getLanguageShort(),
            $ratingDatagroup
        );

        Shipping::setFindValue('type', 'NoShipping');
        if (Shipping::count() === 0) :
            ShippingFactory::create('Geen verzendkosten', 'NoShipping', true)->save();
        endif;

        Payment::setFindValue('type', 'NoPayment');
        if (Payment::count() === 0) :
            PaymentFactory::create('Geen betaling', 'NoPayment', true)->save();
        endif;

        Country::setFindValue('short', 'NL');
        if (Country::count() === 0) :
            CountryFactory::create('Nederland', 'NL', 'NLD', true)->save();
        endif;
    }

    /**
     * createShopShopper
     */
    protected function createShopShopper()
    {
        //registration form
        $fields = [
            'Voornaam'                                  => [
                'calling_name'      => 'firstName',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Tussenvoegsel'                             => [
                'calling_name'      => 'middleName',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
            ],
            'Achternaam'                                => [
                'calling_name'      => 'lastName',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'E-mail'                                    => [
                'calling_name'      => 'email',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'email'],
                'required'          => true,
            ],
            'Wachtwoord'                                => [
                'calling_name'      => 'password',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'password'],
                'required'          => true,
            ],
            'Bevestig wachwoord'                        => [
                'calling_name'      => 'password2',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'password'],
                'required'          => true,
            ],
            'Bedrijfsnaam'                              => [
                'calling_name'      => 'company',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
            ],
            'Straat'                                    => [
                'calling_name'      => 'street',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Huisnummer'                                => [
                'calling_name'      => 'houseNumber',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Postcode'                                  => [
                'calling_name'      => 'zipCode',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Woonplaats'                                => [
                'calling_name'      => 'city',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Land'                                      => [
                'calling_name'      => 'country',
                'type'              => 'FieldModel',
                'datafieldSettings' => ['model' => Country::class],
                'required'          => true,
            ],
            'Telefoon'                                  => [
                'calling_name'      => 'phoneNumber',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'tel'],
                'required'          => true,
            ],
            'Waar ken je ons van?'                      => [
                'calling_name'      => 'refferer',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
            ],
            'Ik ga akkoord met de Leveringsvoorwaarden' => [
                'calling_name' => 'agreedTerms',
                'type'         => 'FieldCheckbox',
                'required'     => true,
            ],
        ];
        $fieldIds = $this->createDatafields($fields, 'calling_name');

        $registrationDatagroup = $this->createDatagroup(
            'Registratie',
            'name.'.$this->configuration->getLanguageShort(),
            'template/core/views/blocks/MainContent/empty',
            'form',
            $fieldIds,
            false,
            null
        );

        Setting::setFindValue('calling_name', 'SHOP_DATAGROUP_REGISTRATIONFORM');
        if (Setting::count() === 0) :
            SettingFactory::create(
                'SHOP_DATAGROUP_REGISTRATIONFORM',
                'SettingDatagroup',
                (string)$registrationDatagroup->getId(),
                'Webshop - Datagroup - Registrationform',
                true
            )->save();
        endif;

        $fields = [
            'Bedrijfsnaam'                              => [
                'calling_name'      => 'companyName',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
            ],
            'Waar ken je ons van?'                      => [
                'calling_name'      => 'refferer',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
            ],
            'Ik ga akkoord met de Leveringsvoorwaarden' => [
                'calling_name' => 'agreedTerms',
                'type'         => 'FieldCheckbox',
                'required'     => true,
            ],
        ];
        $fieldIds = $this->createDatafields($fields, 'calling_name');

        $shopperInformationGroup = $this->createDatagroup(
            'ShopperInformatie',
            'name.'.$this->configuration->getLanguageShort(),
            '',
            'user',
            $fieldIds
        );

        Setting::setFindValue('calling_name', 'SHOP_DATAGROUP_SHOPPERINFORMATION');
        if (Setting::count() === 0) :
            SettingFactory::create(
                'SHOP_DATAGROUP_SHOPPERINFORMATION',
                'SettingDatagroup',
                (string)$shopperInformationGroup->getId(),
                'Webshop - Datagroup - ShopperInformation',
                true
            )->save();
        endif;
    }

    /**
     * createShopShipTo
     */
    protected function createShopShipTo()
    {
        $fields = [
            'Voornaam'      => [
                'calling_name'      => 'firstName',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Tussenvoegsel' => [
                'calling_name'      => 'middleName',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
            ],
            'Achternaam'    => [
                'calling_name'      => 'lastName',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Bedrijfsnaam'  => [
                'calling_name'      => 'companyName',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
            ],
            'Straat'        => [
                'calling_name'      => 'street',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Huisnummer'    => [
                'calling_name'      => 'houseNumber',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Postcode'      => [
                'calling_name'      => 'zipCode',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Woonplaats'    => [
                'calling_name'      => 'city',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Land'          => [
                'calling_name'      => 'country',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'Telefoon'      => [
                'calling_name'      => 'phoneNumber',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'text'],
                'required'          => true,
            ],
            'UserId'        => [
                'calling_name'      => 'userId',
                'type'              => 'FieldText',
                'datafieldSettings' => ['inputType' => 'hidden'],
            ],
        ];
        $fieldIds = $this->createDatafields($fields, 'calling_name');

        $datagroup = $this->createDatagroup(
            'Shipto',
            'name.'.$this->configuration->getLanguageShort(),
            '',
            'user',
            $fieldIds
        );

        Setting::setFindValue('calling_name', 'SHOP_DATAGROUP_SHOPPERSHIPTO');
        if (Setting::count() === 0) :
            SettingFactory::create(
                'SHOP_DATAGROUP_SHOPPERSHIPTO',
                'SettingDatagroup',
                (string)$datagroup->getId(),
                'Webshop - Datagroup - ShopperShipto',
                true
            )->save();
        endif;
    }

    /**
     * createShopBlocks
     */
    protected function createShopBlocks(): void
    {
        $blocks = [
            'In/uitloggen'                   => [
                'block'         => BlockUserLogin::class,
                'template'      => 'template/core/views/blocks/UserLogin/core',
                'position'      => 'topbar',
                'datagroup'     => 'all',
                'blockSettings' => [
                    'class' => ['value' => 'container-login hidden-xs-down'],
                ],
            ],
            'Webshop - minicart'             => [
                'block'     => BlockShopCart::class,
                'template'  => 'template/core/views/blocks/ShopCart/mini',
                'position'  => 'topbar',
                'datagroup' => 'all',
            ],
            'Webshop - afreken stappen'      => [
                'block'         => BlockItemlist::class,
                'template'      => 'template/core/views/blocks/Itemlist/checkout_steps',
                'position'      => 'maincontent',
                'datagroup'     => (string)$this->checkoutDatagroup->getId(),
                'blockSettings' => [
                    'class'    => ['value' => 'checkout-steps'],
                    'listMode' => ['value' => 'datagroups'],
                    'items'    => ['value' => [(string)$this->checkoutDatagroup->getId()]],
                ],
            ],
            'Webshop - checkoutcart'         => [
                'block'     => BlockShopCart::class,
                'template'  => 'template/core/views/blocks/ShopCart/large',
                'position'  => 'maincontent',
                'datagroup' => ['page:'.$this->checkoutPages['pages']['Winkelwagen']],
            ],
            'Webshop - checkout information' => [
                'block'     => BlockShopCheckoutInformation::class,
                'template'  => 'template/core/views/blocks/ShopCheckoutInformation/core',
                'position'  => 'maincontent',
                'datagroup' => ['page:'.$this->checkoutPages['pages']['Je gegevens']],
            ],
            'Webshop - checkout summary'     => [
                'block'     => BlockShopCheckoutSummary::class,
                'template'  => 'template/core/views/blocks/ShopCheckoutSummary/core',
                'position'  => 'maincontent',
                'datagroup' => ['page:'.$this->checkoutPages['pages']['Overzicht']],
            ],
            'Webshop - payment result'       => [
                'block'     => BlockShopPaymentResult::class,
                'template'  => 'template/core/views/blocks/ShopPaymentResult/core',
                'position'  => 'maincontent',
                'datagroup' => ['page:'.$this->checkoutPages['pages']['Bedankt']],
            ],
            'Mijn bestellingen'              => [
                'block'     => BlockShopUserOrders::class,
                'template'  => 'template/core/views/blocks/ShopUserOrders/core',
                'position'  => 'myaccount',
                'datagroup' => [],
            ],
            'Wachtwoord aanpassen'           => [
                'block'     => BlockUserChangePassword::class,
                'template'  => 'template/core/views/blocks/UserChangePassword/core',
                'position'  => 'myaccount',
                'datagroup' => [],
            ],
        ];

        //myaccount
        $this->createBlocks(
            $blocks,
            'name.'.$this->configuration->getLanguageShort()
        );
    }
}
