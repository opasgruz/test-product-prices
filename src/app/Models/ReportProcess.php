<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportProcess extends Model
{
    protected $table = 'report_process';
    protected $primaryKey = 'rp_id';
    public $timestamps = false;

    protected $fillable = [
        'rp_pid',
        'rp_start_datetime',
        'rp_exec_time',
        'ps_id',
        'rp_file_save_path'
    ];

    public function status(): BelongsTo
    {
        // Указываем внешний ключ и локальный ключ таблицы статусов
        return $this->belongsTo(ProcessStatus::class, 'ps_id', 'ps_id');
    }
}
