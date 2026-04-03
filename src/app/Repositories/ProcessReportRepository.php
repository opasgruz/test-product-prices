<?php

namespace App\Repositories;

use App\Models\ProcessStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Репозиторий для управления данными процессов генерации отчетов.
 */
class ProcessReportRepository
{
    /**
     * Создает начальную запись о процессе.
     *
     * @param string $uuid
     * @return int ID созданной записи.
     */
    public function createInitialProcess(string $uuid): int
    {
        return DB::table('report_process')->insertGetId([
            'rp_pid' => $uuid,
            'rp_start_datetime' => Carbon::now(),
            'ps_id' => ProcessStatus::STATUS_STARTED,
            'rp_exec_time' => 0,
        ], 'rp_id');
    }

    /**
     * Поиск процесса по его PID (UUID).
     *
     * @param string $pid
     * @return object|null
     */
    public function findByPid(string $pid): ?object
    {
        return DB::table('report_process')
            ->join('process_status', 'report_process.ps_id', '=', 'process_status.ps_id')
            ->where('rp_pid', $pid)
            ->first();
    }

    /**
     * Обновление статуса и метаданных процесса.
     *
     * @param int $id
     * @param int $statusId
     * @param string|null $filePath
     * @param float $execTime
     * @return void
     */
    public function updateStatus(int $id, int $statusId, ?string $filePath = null, float $execTime = 0): void
    {
        $data = ['ps_id' => $statusId];

        if ($filePath) {
            $data['rp_file_save_path'] = $filePath;
        }

        if ($execTime > 0) {
            $data['rp_exec_time'] = $execTime;
        }

        DB::table('report_process')->where('rp_id', $id)->update($data);
    }

    /**
     * Формирование сложного ETL-отчета на уровне БД и сохранение в CSV.
     *
     * @param int $categoryId
     * @param string $filePath Абсолютный путь к файлу.
     * @return void
     * @throws RuntimeException
     */
    public function runComplexReport(int $categoryId, string $filePath): void
    {
        $startDate = Carbon::now()->subDays(8)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        // 1. Извлечение данных (Extract)
        DB::statement("
            CREATE TEMPORARY TABLE temp_price_slice ON COMMIT DROP AS
            SELECT p.product_id, p.price_date, p.price
            FROM price p
            JOIN product pr ON p.product_id = pr.product_id
            WHERE p.price_date >= :start_date
              AND p.price_date < :end_date
              AND pr.category_id = :category_id
        ", [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'category_id' => $categoryId,
        ]);

        DB::statement("CREATE INDEX idx_slice_lookup ON temp_price_slice (product_id, price_date)");
        DB::statement("ANALYZE temp_price_slice");

        // 2. Агрегация данных (Transform)
        DB::statement("
            CREATE TEMPORARY TABLE temp_report_agg ON COMMIT DROP AS
            SELECT DISTINCT ON (product_id)
                product_id,
                first_value(price) OVER w_min AS min_price,
                first_value(price_date) OVER w_min AS min_price_date,
                first_value(price) OVER w_max AS max_price,
                first_value(price_date) OVER w_max AS max_price_date
            FROM temp_price_slice
            WINDOW
                w_min AS (PARTITION BY product_id ORDER BY price ASC, price_date DESC),
                w_max AS (PARTITION BY product_id ORDER BY price DESC, price_date DESC)
        ");

        // 3. Подготовка финальной структуры
        DB::statement("
            CREATE TEMPORARY TABLE final_report_table ON COMMIT DROP AS
            SELECT
                m.manufacturer_name,
                p.product_name,
                ROUND(v.actual_price::numeric, 2) as price,
                v.actual_date as price_date
            FROM temp_report_agg t
            JOIN product p ON t.product_id = p.product_id
            JOIN manufacturer m ON p.manufacturer_id = m.manufacturer_id
            CROSS JOIN LATERAL (
                VALUES (t.min_price, t.min_price_date), (t.max_price, t.max_price_date)
            ) AS v(actual_price, actual_date)
        ");

        $this->writeToCsv($filePath);
    }

    /**
     * Потоковая запись данных из временной таблицы в CSV.
     *
     * @param string $filePath
     * @return void
     */
    private function writeToCsv(string $filePath): void
    {
        $handle = fopen($filePath, 'w');
        if (!$handle) {
            throw new RuntimeException("Не удалось открыть файл для записи: {$filePath}");
        }

        try {
            // Заголовки CSV
            fputcsv($handle, ['manufacturer_name', 'product_name', 'price', 'price_date']);

            // Использование курсора для экономии RAM
            $cursor = DB::table('final_report_table')->orderBy('manufacturer_name')->cursor();

            foreach ($cursor as $row) {
                fputcsv($handle, [
                    $row->manufacturer_name,
                    $row->product_name,
                    $row->price,
                    $row->price_date,
                ]);
            }
        } finally {
            fclose($handle);
        }
    }
}
