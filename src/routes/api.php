<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\DefaultController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', fn() => response()->json(
    [
        'data' => [
            'name' => config('app.name'),
            'available_versions' => [
                'v1' => route('api.v1.home')
            ],
        ],
    ],
    200,
    [],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
));

Route::prefix('/v1')->group(function () {
    Route::get('/', [DefaultController::class, 'index'])->name('api.v1.home');

    Route::prefix('auth')->group(function () {
        Route::post('/', [AuthController::class, 'auth'])->name('api.v1.auth.login');
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('current', [UserController::class, 'current']);
    });
});

Route::fallback(fn() => response()->json(
    [
        'data' => [],
    ],
    404,
    [],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
));
