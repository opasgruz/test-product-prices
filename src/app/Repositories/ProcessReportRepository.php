<?php

namespace App\Repositories;

use App\Models\ProcessStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProcessReportRepository
{
    public function createInitialProcess(string $uuid): int
    {
        return DB::table('report_process')->insertGetId([
            'rp_pid' => $uuid,
            'rp_start_datetime' => Carbon::now(),
            'ps_id' => ProcessStatus::STATUS_STARTED,
            'rp_exec_time' => 0
        ], 'rp_id');
    }

    public function findByPid(string $pid)
    {
        return DB::table('report_process')
            ->join('process_status', 'report_process.ps_id', '=', 'process_status.ps_id')
            ->where('rp_pid', $pid)
            ->first();
    }

    public function updateStatus(int $id, int $statusId, ?string $filePath = null, float $execTime = 0)
    {
        $data = ['ps_id' => $statusId];
        if ($filePath) $data['rp_file_save_path'] = $filePath;
        if ($execTime > 0) $data['rp_exec_time'] = $execTime;

        DB::table('report_process')->where('rp_id', $id)->update($data);
    }

    public function runComplexReport(int $categoryId, string $filePath): void
    {
        $startDate = Carbon::now()->subDays(8)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        // Шаг 1: Extract во временную таблицу
        DB::statement("
            CREATE TEMPORARY TABLE temp_price_slice ON COMMIT DROP AS
            SELECT p.product_id, p.price_date, p.price
            FROM price p
            JOIN product pr ON p.product_id = pr.product_id
            WHERE p.price_date >= :start_date and p.price_date < :end_date
            AND pr.category_id = :category_id
        ", ['start_date' => $startDate, 'end_date' => $endDate, 'category_id' => $categoryId]);

        DB::statement("CREATE INDEX idx_slice_lookup ON temp_price_slice (product_id, price_date, price)");
        DB::statement("ANALYZE temp_price_slice");

        // Шаг 2: Transform (Агрегация)
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

        // Шаг 3: Разворот в финальную таблицу
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
//        $count = DB::selectOne("SELECT count(*) AS total FROM final_report_table")->total;
//
//        // Выводим результат и прерываем выполнение для теста
//        dd([
//            'total_rows' => $count,
//            'category' => $categoryId,
//            'period' => "from $startDate to $endDate"
//        ]);
        // Шаг 4: Выгрузка через COPY
        // Примечание: Для работы COPY у пользователя БД должны быть права superuser или используйте \copy в psql.
        // В Laravel часто используется поток (Stream) или формирование строки.
        //DB::statement("COPY (SELECT * FROM final_report_table) TO '$filePath' WITH (FORMAT CSV, HEADER, DELIMITER ',')");

        $file = fopen($filePath, 'w');

        // Добавляем заголовки (BOM для корректного открытия в Excel, если нужно)
        fputcsv($file, ['manufacturer_name', 'product_name', 'price', 'price_date']);

        // Используем курсор, чтобы не потреблять много памяти при больших отчетах
        $query = "SELECT * FROM final_report_table";
        $results = DB::cursor($query);

        foreach ($results as $row) {
            fputcsv($file, [
                $row->manufacturer_name,
                $row->product_name,
                $row->price,
                $row->price_date
            ]);
        }

        fclose($file);
        if (file_exists($filePath)) {
            // 0644 позволяет владельцу писать/читать, а остальным (веб-серверу) — только читать
            chmod($filePath, 0644);
        }
    }
}
