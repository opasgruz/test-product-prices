<?php

namespace App\Enums;

/**
 * Перечисление категорий товаров.
 */
enum ProductCategory: int
{
    /** Фрукты */
    case FRUITS = 1;

    /** Овощи */
    case VEGETABLES = 2;

    /** Молочные продукты */
    case DAIRY = 3;

    /** Выпечка */
    case BAKERY = 4;

    /** Мясо */
    case MEAT = 5;

    /** Рыба */
    case FISH = 6;

    /** Напитки */
    case BEVERAGES = 7;

    /** Кондитерские изделия */
    case CONFECTIONERY = 8;

    /** Замороженные продукты */
    case FROZEN_FOOD = 9;

    /** Крупы */
    case CEREALS = 10;

    /**
     * Получить текстовое описание категории.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
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
