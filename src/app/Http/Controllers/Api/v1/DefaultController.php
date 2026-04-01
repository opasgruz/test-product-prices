<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @api {get} /v1 API HomePage
 * @apiName HomePage
 * @apiGroup Home
 * @apiDescription Домашня страница API v1
 * @apiVersion 1.0.0
 *
 * @apiSuccess {Object} data Данные ответа
 * @apiSuccess {String} data.name Имя сервиса
 * @apiSuccess {Object} data.version Текущая версия
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *      {
 *          "data": {
 *              "name": "laravel-basic",
 *              "version": "v1"
 *          }
 *      }
 */

/**
 * Дефолтный контроллер API V1
 *
 * @package App\Http\Controllers\Api
 */
class DefaultController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->sendResponse([
            'name' => config('app.name'),
            'version' => 'v1'
        ]);
    }
}
