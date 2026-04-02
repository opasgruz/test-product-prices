<?php

namespace App\Enums;

enum ProductCategory: int
{
    case FRUITS = 1;
    case VEGETABLES = 2;
    case DAIRY = 3;
    case BAKERY = 4;
    case MEAT = 5;
    case FISH = 6;
    case BEVERAGES = 7;
    case CONFECTIONERY = 8;
    case FROZEN_FOOD = 9;
    case CEREALS = 10;

    public function label(): string
    {
        return match($this) {
            self::FRUITS => 'Фрукты',
            self::VEGETABLES => 'Овощи',
            self::DAIRY => 'Молочные продукты',
            self::BAKERY => 'Выпечка',
            self::MEAT => 'Мясо',
            self::FISH => 'Рыба',
            self::BEVERAGES => 'Напитки',
            self::CONFECTIONERY => 'Кондитерские изделия',
            self::FROZEN_FOOD => 'Заморозка',
            self::CEREALS => 'Крупы',
        };
    }
}
