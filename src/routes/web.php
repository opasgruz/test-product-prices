<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/reports', [App\Http\Controllers\Web\ReportController::class, 'index'])->name('reports.index');
