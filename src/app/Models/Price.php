<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель цены товара.
 *
 * @property int $price_id
 * @property int $product_id
 * @property float $price
 * @property \Carbon\Carbon $price_date
 */
class Price extends Model
{
    use HasFactory;

    /** @var string Название таблицы в БД */
    protected $table = 'price';

    /** @var string Первичный ключ */
    protected $primaryKey = 'price_id';

    /** @var bool Использование автоинкремента */
    public $incrementing = true;

    /** @var bool Отключение автоматических меток времени */
    public $timestamps = false;

    /** @var array<int, string> Разрешенные поля для массового заполнения */
    protected $fillable = [
        'product_id',
        'price',
        'price_date',
    ];

    /** @var array<string, string> Преобразование типов атрибутов */
    protected $casts = [
        'price' => 'decimal:2',
        'price_date' => 'date',
    ];

    /**
     * Связь: цена относится к конкретному товару.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
