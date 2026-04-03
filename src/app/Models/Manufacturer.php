<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель производителя.
 *
 * @property int $manufacturer_id
 * @property string $manufacturer_name
 */
class Manufacturer extends Model
{
    use HasFactory;

    /** @var string Название таблицы в БД */
    protected $table = 'manufacturer';

    /** @var string Первичный ключ */
    protected $primaryKey = 'manufacturer_id';

    /** @var bool Отключение автоматических меток времени (created_at, updated_at) */
    public $timestamps = false;

    /** @var array<int, string> Разрешенные поля для массового заполнения */
    protected $fillable = [
        'manufacturer_name',
    ];

    /**
     * Связь: один производитель имеет много товаров.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'manufacturer_id', 'manufacturer_id');
    }
}
