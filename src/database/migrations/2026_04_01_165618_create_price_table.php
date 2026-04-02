<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration {
    public function up(): void {
        // 1. Создаем родительскую таблицу
        DB::statement("
            CREATE TABLE price (
                price_id BIGSERIAL,
                product_id INTEGER NOT NULL,
                price NUMERIC(15, 2) NOT NULL,
                price_date DATE NOT NULL,
                PRIMARY KEY (price_id, price_date)
            ) PARTITION BY RANGE (price_date);
        ");

        DB::statement("CREATE INDEX idx_price_lookup ON price (product_id, price_date, price);");

        for ($i = 8; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->format('Y_m_d');
            $start = $date->format('Y-m-d');
            $end = $date->copy()->addDay()->format('Y-m-d');

            DB::statement("
                CREATE TABLE price_{$dateStr}
                PARTITION OF price
                FOR VALUES FROM ('{$start}') TO ('{$end}')
            ");
        }
    }

    public function down(): void {
        DB::statement("DROP TABLE IF EXISTS price CASCADE;");
    }
};
