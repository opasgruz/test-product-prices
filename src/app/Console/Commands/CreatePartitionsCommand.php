<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Команда для автоматического управления партициями базы данных PostgreSQL.
 */
class CreatePartitionsCommand extends Command
{
    /**
     * Название и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'db:create-partitions';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Создание партиций на неделю вперед (запуск перед полуночью)';

    /**
     * Список таблиц, для которых необходимо создавать партиции.
     * Ключ — префикс имени партиции, значение — имя родительской таблицы.
     *
     * @var array<string, string>
     */
    protected array $partitionedTables = [
        'price' => 'price',
    ];

    /**
     * Выполнение консольной команды.
     * * Итерируется на 7 дней вперед, создавая отсутствующие разделы.
     * Добавление 15 минут позволяет безопасно запускать команду за несколько минут до полуночи.
     *
     * @return void
     */
    public function handle(): void
    {
        // Итерируемся на 7 дней вперед, учитывая сдвиг времени
        for ($i = 0; $i <= 7; $i++) {
            $date = Carbon::now()->addMinutes(15)->addDays($i);
            $this->createPartition($date);
        }

        $this->info('Партиции на будущий период созданы.');
    }

    /**
     * Создает партиции для всех зарегистрированных таблиц на указанную дату.
     *
     * @param Carbon $date Дата, для которой создается раздел.
     * @return void
     */
    private function createPartition(Carbon $date): void
    {
        $dateStr = $date->format('Y_m_d');
        $start = $date->format('Y-m-d');
        $end = $date->copy()->addDay()->format('Y-m-d');

        foreach ($this->partitionedTables as $prefix => $parentTable) {
            $partitionName = "{$prefix}_{$dateStr}";

            if (!$this->partitionExists($partitionName)) {
                $this->executePartitionCreation($partitionName, $parentTable, $start, $end);
            }
        }
    }

    /**
     * Проверяет существование таблицы в схеме PostgreSQL.
     *
     * @param string $partitionName Имя проверяемой таблицы.
     * @return bool True, если таблица существует.
     */
    private function partitionExists(string $partitionName): bool
    {
        $query = "
            SELECT 1
            FROM pg_class c
            JOIN pg_namespace n ON n.oid = c.relnamespace
            WHERE c.relname = ?
        ";

        return (bool) DB::selectOne($query, [$partitionName]);
    }

    /**
     * Выполняет SQL-запрос на создание партиции.
     *
     * @param string $name Имя новой партиции.
     * @param string $parent Имя родительской таблицы.
     * @param string $start Начальная дата (включительно).
     * @param string $end Конечная дата (исключая).
     * @return void
     */
    private function executePartitionCreation(string $name, string $parent, string $start, string $end): void
    {
        DB::statement("
            CREATE TABLE {$name}
            PARTITION OF {$parent}
            FOR VALUES FROM ('{$start}') TO ('{$end}')
        ");
    }
}
