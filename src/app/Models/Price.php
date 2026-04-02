<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    use HasFactory;

    protected $table = 'price';
    protected $primaryKey = 'price_id'; // Основной идентификатор
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'price',
        'price_date',
    ];

    protected $casts = [
        'price' => 'decimal:2', // Округление до 2 знаков согласно ТЗ
        'price_date' => 'date',
    ];

    /**
     * Связь: цена относится к конкретному товару
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
