<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessStatus extends Model
{
    protected $table = 'process_status';
    protected $primaryKey = 'ps_id';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ['ps_id', 'ps_name'];

    // Константы статусов согласно ТЗ
    const STATUS_STARTED = 1;   // Запуск
    const STATUS_COMPLETED = 2; // Завершен
    const STATUS_ERROR = 3;     // Ошибка
}
