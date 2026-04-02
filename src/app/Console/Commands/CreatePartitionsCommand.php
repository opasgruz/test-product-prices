<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreatePartitionsCommand extends Command
{
    protected $signature = 'db:create-partitions';
    protected $description = 'Создание партиций на неделю вперед (запуск перед полуночью)';

    protected array $partitionedTables = ['price' => 'price'];

    public function handle()
    {
        // Итерируемся на 7 дней вперед, учитывая сдвиг времени
        for ($i = 0; $i <= 7; $i++) {
            $date = Carbon::now()->addMinutes(15)->addDays($i);
            $this->createPartition($date);
        }
        $this->info('Партиции на будущий период созданы.');
    }

    private function createPartition(Carbon $date)
    {
        $dateStr = $date->format('Y_m_d');
        $start   = $date->format('Y-m-d');
        $end     = $date->copy()->addDay()->format('Y-m-d');

        foreach ($this->partitionedTables as $prefix => $parentTable) {
            $partitionName = "{$prefix}_{$dateStr}";
            $exists = DB::selectOne("SELECT 1 FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace WHERE c.relname = ?", [$partitionName]);

            if (!$exists) {
                // Создаем таблицу с учетом PK (id, date) и авто-наследованием индексов
                DB::statement("CREATE TABLE {$partitionName} PARTITION OF {$parentTable} FOR VALUES FROM ('{$start}') TO ('{$end}')");
            }
        }
    }
}
