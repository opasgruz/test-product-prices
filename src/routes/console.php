<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('seed-test-data', function () {
    $this->info('Начало заполнения тестовыми данными...');

    // 2. Запуск основного тестового сидера (производители, товары, цены)
    $this->comment('Генерация производителей, товаров и цен...');
    Artisan::call('db:seed', ['--class' => 'TestDataSeeder']);

    $this->info('Тестовые данные успешно созданы.');
})->purpose('Заполнить базу данных всеми необходимыми тестовыми данными');
