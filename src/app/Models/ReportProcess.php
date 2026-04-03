<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель процесса генерации отчета.
 *
 * @property int $rp_id
 * @property string $rp_pid UUID процесса
 * @property string $rp_start_datetime Время начала
 * @property float $rp_exec_time Время выполнения
 * @property int $ps_id ID статуса
 * @property string|null $rp_file_save_path Путь к файлу результата
 */
class ReportProcess extends Model
{
    /** @var string Название таблицы в БД */
    protected $table = 'report_process';

    /** @var string Первичный ключ */
    protected $primaryKey = 'rp_id';

    /** @var bool Отключение автоматических меток времени */
    public $timestamps = false;

    /** @var array<int, string> Разрешенные поля для массового заполнения */
    protected $fillable = [
        'rp_pid',
        'rp_start_datetime',
        'rp_exec_time',
        'ps_id',
        'rp_file_save_path',
    ];

    /**
     * Связь: процесс имеет текущий статус.
     *
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ProcessStatus::class, 'ps_id', 'ps_id');
    }
}
