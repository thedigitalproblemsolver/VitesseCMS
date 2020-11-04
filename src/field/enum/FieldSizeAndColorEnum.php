<?php declare(strict_types=1);

namespace VitesseCms\Field\Enums;

use VitesseCms\Core\AbstractEnum;

class FieldSizeAndColorEnum extends AbstractEnum
{
    public const SIZE_S = 'Small';
    public const SIZE_M = 'Medium';
    public const SIZE_L = 'Large';
    public const SIZE_XL = 'X-large';
    public const SIZE_XXL = 'XX-large';
    public const SIZE_3XL = '3X-Large';
    public const SIZE_4XL = '4X-large';
    public const SIZE_5XL = '5X-large';
    public const SIZE_ONE = 'One-size';

    public const sizes = [
        'S'   => self::SIZE_S,
        'M'   => self::SIZE_M,
        'L'   => self::SIZE_L,
        'XL'  => self::SIZE_XL,
        'XXL' => self::SIZE_XXL,
        '3XL' => self::SIZE_3XL,
        '4XL' => self::SIZE_4XL,
        '5XL' => self::SIZE_5XL,
        'One size' => self::SIZE_ONE,
        'One Size' => self::SIZE_ONE
    ];
}
