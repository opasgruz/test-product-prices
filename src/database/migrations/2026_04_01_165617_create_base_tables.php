<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Таблица статусов процессов
        Schema::create('process_status', function (Blueprint $table) {
            $table->integer('ps_id')->primary();
            $table->string('ps_name');
        });

        DB::table('process_status')->insert([
            ['ps_id' => 1, 'ps_name' => 'Запуск'],   //
            ['ps_id' => 2, 'ps_name' => 'Завершен'], //
            ['ps_id' => 3, 'ps_name' => 'Ошибка'],   //
        ]);

        // Таблица производителей
        Schema::create('manufacturer', function (Blueprint $table) {
            $table->id('manufacturer_id');
            $table->string('manufacturer_name');
        });

        // Таблица товаров
        Schema::create('product', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('product_name');
            $table->integer('category_id')->index();
            $table->foreignId('manufacturer_id')->constrained('manufacturer', 'manufacturer_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('product');
        Schema::dropIfExists('manufacturer');
        Schema::dropIfExists('process_status');
    }
};
