<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель товара.
 *
 * @property int $product_id
 * @property string $product_name
 * @property int $category_id
 * @property int $manufacturer_id
 */
class Product extends Model
{
    use HasFactory;

    /** @var string Название таблицы в БД */
    protected $table = 'product';

    /** @var string Первичный ключ */
    protected $primaryKey = 'product_id';

    /** @var bool Отключение автоматических меток времени */
    public $timestamps = false;

    /** @var array<int, string> Разрешенные поля для массового заполнения */
    protected $fillable = [
        'product_name',
        'category_id',
        'manufacturer_id',
    ];

    /**
     * Связь: товар принадлежит производителю.
     *
     * @return BelongsTo
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id', 'manufacturer_id');
    }

    /**
     * Связь: товар имеет историю цен.
     *
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class, 'product_id', 'product_id');
    }
}
