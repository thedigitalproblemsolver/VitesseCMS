<?php declare(strict_types=1);

namespace VitesseCms\Field\Models;

use VitesseCms\Core\Models\Datafield;
use VitesseCms\Database\AbstractCollection;
use VitesseCms\Field\AbstractField;
use VitesseCms\Field\Enums\FieldSizeAndColorEnum;
use VitesseCms\Field\Factories\FieldSizeAndColorFactory;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Interfaces\AbstractFormInterface;
use VitesseCms\Media\Enums\AssetsEnum;
use Phalcon\Http\Request;
use Phalcon\Tag;

class FieldSizeAndColor extends AbstractField
{
    public function buildItemFormElement(
        AbstractForm $form,
        Datafield $datafield,
        AbstractCollection $data = null
    ) {
        if ($data !== null) {
            $form->assets->load(AssetsEnum::COLORPICKER);
            $form->assets->load(AssetsEnum::FILEMANAGER);
            $fieldName = $datafield->getCallingName();

            $dataVariations = $data->_($fieldName);
            $images = $variations = $colorParsed = [];
            if (\is_array($dataVariations)) :
                ksort($dataVariations);
                foreach ($dataVariations as $key => $variation) :
                    $variations[] = FieldSizeAndColorFactory::createVariationForm($variation, $fieldName);
                    if (!isset($colorParsed[$variation['color']])) :
                        if (!\is_array($variation['image'])) :
                            $variation['image'] = [$variation['image']];
                        endif;
                        foreach ($variation['image'] as $imageKey => $image) :
                            $images[] = [
                                'id'    => str_replace('#', '', strtolower($variation['color'])).$imageKey,
                                'name'  => $fieldName.'_images['.strtolower($variation['color']).'][]',
                                'color' => strtolower($variation['color']),
                                'image' => $image,
                            ];
                        endforeach;
                        $images[] = [
                            'id'    => str_replace('#', '', strtolower($variation['color'])),
                            'name'  => $fieldName.'_images['.strtolower($variation['color']).'][]',
                            'color' => strtolower($variation['color']),
                            'image' => '',
                        ];
                        $colorParsed[$variation['color']] = $variation['color'];
                    endif;
                endforeach;
            endif;

            $params = [
                'sizeAndColorLabel'       => $datafield->getNameField(),
                'sizeAndColorId'          => uniqid('', false),
                'sizeAndColorVariations'  => $variations,
                'sizeAndColorImages'      => $images,
                'skuBaseElement'          => Tag::textField(['name' => $fieldName.'[__key__][sku]', 'id' => null]),
                //TODO : zonder @-teken komt er een foutmelding, waarschijnlijk is dit een bug in het Phalcon framework
                'sizeBaseElement'         => @Tag::select(['name' => $fieldName.'[__key__][size]', 'id' => null],
                    FieldSizeAndColorEnum::sizes),
                'colorBaseElement'        => Tag::textField(['name' => $fieldName.'[__key__][color]', 'id' => null]),
                'stockBaseElement'        => Tag::numericField(['name' => $fieldName.'[__key__][stock]', 'id' => null]),
                'stockMinimalBaseElement' => Tag::numericField(['name' => $fieldName.'[__key__][stock]', 'id' => null]),
                'eanBaseElement'          => Tag::numericField([
                    'name'      => $fieldName.'[__key__][ean]',
                    'maxlength' => 13,
                    'id'        => null,
                ]),
            ];

            $form->addHtml($form->view->renderTemplate(
                'adminItemFormSizeAndColor',
                $form->configuration->getRootDir().'src/field/resources/views/',
                $params
            ));
        }
    }

    public static function beforeSave(AbstractCollection $item, Datafield $datafield)
    {
        $request = new Request();
        if ($request->get($datafield->getCallingName())) :
            $variations = [];
            $images = $request->get($datafield->getCallingName().'_images');
            foreach ((array)$request->get($datafield->getCallingName()) as $key => $variation) :
                if ($key !== '__key__') :
                    $variation['image'] = (array)$images[strtolower($variation['color'])];
                    foreach ($variation['image'] as $imageKey => $value) :
                        if (empty($value)) :
                            unset($variation['image'][$imageKey]);
                        endif;
                    endforeach;
                    $variations[$variation['sku']] = $variation;
                endif;
            endforeach;
            $item->set($datafield->getCallingName(), $variations);
        endif;

        $sizes = $colors = [];
        $inStock = 0;
        $firstImage = $item->_('image');
        $firstImageSet = false;
        if (is_array($item->_($datafield->getCallingName()))) :
            foreach ($item->_($datafield->getCallingName()) as $variation) :
                if ((int)$variation['stock'] > 0) :
                    if (!isset($colors[$variation['color']])) :
                        $colors[$variation['color']] = ['sku' => []];
                    endif;

                    if (!isset($sizes[$variation['size']])) :
                        $sizes[$variation['size']] = ['sku' => []];
                    endif;
                    $sizes[$variation['size']]['sku'][] = $variation['sku'];
                    $colors[strtolower($variation['color'])]['sku'][] = $variation['sku'];
                    $colors[strtolower($variation['color'])]['image'] = $variation['image'];

                    $inStock += (int)$variation['stock'];
                    if (!$firstImageSet && !empty($variation['image'][0])) :
                        $firstImage = $variation['image'][0];
                        $firstImageSet = true;
                    endif;
                endif;
            endforeach;

            $aColors = [];
            foreach ($colors as $s => $sku) :
                $aColors[] = [
                    'color'      => $s,
                    'sku'        => implode(',', $sku['sku']),
                    'image'      => implode(',', $sku['image']),
                    'colorClass' => str_replace('#', '', $s),
                ];
            endforeach;

            $aSizes = [];
            foreach (FieldSizeAndColorEnum::sizes as $size => $sizeName) :
                if (isset($sizes[$size])) :
                    $aSizes[] = [
                        'size' => $size,
                        'sku'  => implode(',', $sizes[$size]['sku']),
                    ];
                endif;
            endforeach;

            $item->set($datafield->getCallingName().'Template', [
                'colors' => $aColors,
                'sizes'  => $aSizes,
            ]);

            if ($inStock === 0) :
                $item->set('outOfStock', true);
            endif;

            if ($item->_('outOfStock')) :
                $item->set('isFilterable', false);
            endif;

            $item->set('firstImage', $firstImage);
        endif;
    }

    public function renderFilter(
        AbstractFormInterface $filter,
        Datafield $datafield,
        AbstractCollection $data = null
    ): void {
        $options = [];
        foreach (FieldSizeAndColorEnum::sizes as $key => $label) :
            $options[] = [
                'value'    => $key,
                'label'    => $label,
                'selected' => false,
            ];
        endforeach;

        $this->setOption('options', $options);
        $this->setOption('multiple', true);
        $this->setOption('noEmptyText', true);
        $this->setOption('inputClass', 'select2');

        $filter->_(
            'select',
            '%SHOP_SIZE%',
            'filter[variations][]',
            $this->getOptions()
        );
    }

    public function getSearchValue(AbstractCollection $item, string $languageShort, Datafield $datafield)
    {
        $searchValues = [];
        if (is_array($item->_($datafield->getCallingName()))) :
            foreach ($item->_($datafield->getCallingName()) as $variation) :
                if ($variation['stock'] > 0 && !\in_array($variation['size'], $searchValues, true)) :
                    $searchValues[] = $variation['size'];
                endif;
            endforeach;
        endif;

        return $searchValues;
    }
}
