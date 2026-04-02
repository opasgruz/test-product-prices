<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manufacturer extends Model
{
    use HasFactory;

    protected $table = 'manufacturer';
    protected $primaryKey = 'manufacturer_id'; // PK согласно ТЗ
    public $timestamps = false; // Поля времени в ТЗ не указаны

    protected $fillable = [
        'manufacturer_name',
    ];

    /**
     * Связь: один производитель имеет много товаров
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'manufacturer_id', 'manufacturer_id');
    }
}
