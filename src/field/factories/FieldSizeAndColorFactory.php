<?php

namespace VitesseCms\Field\Factories;

use VitesseCms\Field\Enums\FieldSizeAndColorEnum;
use Phalcon\Tag;

/**
 * Class FieldSizeAndColorFactory
 */
class FieldSizeAndColorFactory
{
    /**
     * @param array $variation
     * @param string $fieldName
     *
     * @return array
     */
    public static function createVariationForm(array $variation, string $fieldName) : array {
        $key = $variation['sku'];
        $elements = ['id' => 'SizeAndColor' . $key];
        $elements['skuElement'] = Tag::textField([
            'required' => 'required',
            'name'     => $fieldName . '[' . $key . '][sku]',
            'id'       => 'SizeAndColor_sku_' . $key,
            'value'    => $variation['sku'],
        ]);

        //zonder @-teken komt er een foutmelding, waarschijnlijk is dit een bug in het Phalcon framework
        $elements['sizeElement'] = @Tag::select([
            'required' => 'required',
            'name'     => $fieldName . '[' . $key . '][size]',
            'id'       => 'SizeAndColor_size_' . $key,
            'value'    => $variation['size'],
        ],
            FieldSizeAndColorEnum::sizes);
        $elements['colorElement'] = Tag::textField([
            'required' => 'required',
            'name'     => $fieldName . '[' . $key . '][color]',
            'id'       => 'SizeAndColor_color_' . $key,
            'value'    => $variation['color'],
        ]);
        $elements['stockElement'] = Tag::numericField([
            'required' => 'required',
            'name'     => $fieldName . '[' . $key . '][stock]',
            'id'       => 'SizeAndColor_stock_' . $key,
            'value'    => $variation['stock'],
        ]);
        $elements['stockMinimalElement'] = Tag::numericField([
            'required' => 'required',
            'name'     => $fieldName . '[' . $key . '][stockMinimal]',
            'id'       => 'SizeAndColor_stockMinimal_' . $key,
            'value'    => $variation['stockMinimal'] ?? 0,
        ]);
        $elements['eanElement'] = Tag::numericField([
            'required'  => 'required',
            'name'      => $fieldName . '[' . $key . '][ean]',
            'id'        => 'SizeAndColor_ean_' . $key,
            'maxlength' => 13,
            'value'     => $variation['ean'],
        ]);

        return $elements;
    }
}
