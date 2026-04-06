<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель справочника статусов процессов.
 *
 * @property int $ps_id
 * @property string $ps_name
 */
class ProcessStatus extends Model
{
    /** Статус: Запуск процесса */
    public const int STATUS_STARTED = 1;

    /** Статус: Процесс успешно завершен */
    public const int STATUS_COMPLETED = 2;

    /** Статус: Произошла ошибка */
    public const int STATUS_ERROR = 3;

    /** Статус: Нето товаров по категории */
    public const int STATUS_NO_PRODUCTS = 4;

    /** @var string Название таблицы в БД */
    protected $table = 'process_status';

    /** @var string Первичный ключ */
    protected $primaryKey = 'ps_id';

    /** @var bool Отключение автоматических меток времени */
    public $timestamps = false;

    /** @var bool Отключение автоинкремента для использования ручного ввода ID */
    public $incrementing = false;

    /** @var array<int, string> Разрешенные поля для массового заполнения */
    protected $fillable = [
        'ps_id',
        'ps_name',
    ];
}
