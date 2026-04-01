<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @apiDefine HeaderRequest
 * @apiHeader {String} Authorization Токен авторизации "Bearer [auth-token]".
 */
/**
 * @apiDefine IndexActionParams
 * @apiParam {Integer} [page] Номер текущей страницы.
 * @apiParam {Integer} [per_page] Кол-во элементов на страницу.
 */
/**
 * @apiDefine IndexResponseLinks
 * @apiSuccess {Object} links Набор ссылок, позволяющий клиенту пройти все страницы ресурсов
 * @apiSuccess {String} links.first Ссылка на первую страницу
 * @apiSuccess {String} links.prev Ссылка на предыдущую страницу
 * @apiSuccess {String} links.next Ссылка на следующую страницу
 * @apiSuccess {String} links.last Ссылка на последнюю страницу
 */
/**
 * @apiDefine IndexResponseMeta
 * @apiSuccess {Object} meta Мета данные
 * @apiSuccess {Integer} meta.current_page Номер текущей страницы (начиная с 1)
 * @apiSuccess {Integer} meta.from Начиная с какого элемента возвращаются
 * @apiSuccess {Integer} meta.last_page Номер последней страницы
 * @apiSuccess {String} meta.path Основной URL текущей страницы
 * @apiSuccess {Integer} meta.per_page Кол-во элементов на странице
 * @apiSuccess {Integer} meta.to По какой элемент возвращаются
 * @apiSuccess {Integer} meta.total Общее кол-во элементов
 */

/**
 * @api {post} /v1/auth Авторизация
 * @apiName Auth
 * @apiGroup Auth
 * @apiDescription Возвращает jwt.
 * @apiVersion 1.0.0
 *
 * @apiParam {String} email E-mail пользователя
 * @apiParam {String} password Пароль
 *
 * @apiSuccess {Object} data Результат.
 * @apiSuccess {String} data.token JWT
 * @apiSuccess {String} data.token_type Тип авторизации для токена
 * @apiSuccess {Integer} data.expires_in Кол-во секунд, которые действителен токен
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *      {
 *          "data": {
 *              "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hcGkubG9jYXRpb24ubG9jYWw6OTNcL3YxXC9hdXRoIiwiaWF0IjoxNTgyNzE3ODcyLCJleHAiOjE1ODI4MDQyNzIsIm5iZiI6MTU4MjcxNzg3MiwianRpIjoiclVZUnlOeU41VjJhMGh1VSIsInN1YiI6NywicHJ2IjoiNGFjMDVjMGY4YWMwOGYzNjRjYjRkMDNmYjhlMWY2MzFmZWMzMjJlOCJ9.5rzzos7UwTnpXtQmhpEuF0I8F0NR2fuy2Qk6u2gF4hM",
 *              "token_type": "bearer",
 *              "expires_in": 86400
 *          }
 *      }
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 422 Error
 *      {
 *          "message": "The given data was invalid.",
 *          "errors": {
 *              "email": [
 *                  "The email must be a valid email address."
 *              ],
 *              "password": [
 *                  "The password field is required."
 *              ]
 *          }
 *      }
 */

/**
 * @api {post} /v1/auth/refresh Обновление токена
 * @apiName Refresh
 * @apiGroup Auth
 * @apiDescription Генерирует и возвращает новый JWT на основе старого
 * @apiVersion 1.0.0
 *
 * @apiUse HeaderRequest
 *
 * @apiSuccess {Object} data Результат.
 * @apiSuccess {String} data.token JWT
 * @apiSuccess {String} data.token_type Тип авторизации для токена
 * @apiSuccess {Integer} data.expires_in Кол-во секунд, которые действителен токен
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *      {
 *          "data": {
 *              "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hcGkubG9jYXRpb24ubG9jYWw6OTNcL3YxXC9hdXRoIiwiaWF0IjoxNTgyNzE3ODcyLCJleHAiOjE1ODI4MDQyNzIsIm5iZiI6MTU4MjcxNzg3MiwianRpIjoiclVZUnlOeU41VjJhMGh1VSIsInN1YiI6NywicHJ2IjoiNGFjMDVjMGY4YWMwOGYzNjRjYjRkMDNmYjhlMWY2MzFmZWMzMjJlOCJ9.5rzzos7UwTnpXtQmhpEuF0I8F0NR2fuy2Qk6u2gF4hM",
 *              "token_type": "bearer",
 *              "expires_in": 86400
 *          }
 *      }
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 401 Error
 *      {
 *          "message": "Unauthenticated",
 *      }
 */

/**
 * Контроллер авторизации
 *
 * @package App\Http\Controllers\Api\v1
 */
class AuthController extends Controller
{
    use ApiResponse;

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['auth']]);
    }

    /**
     * Авторизация. Генерирует и возвращает JWT
     *
     * @param AuthRequest $request
     *
     * @return JsonResponse
     */
    public function auth(AuthRequest $request): JsonResponse
    {
        if (!$token = auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->sendError('Unauthorized', 401);
        }

        return $this->sendResponse($this->getDataToken($token));
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->sendResponse($this->getDataToken(auth()->refresh()));
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return array
     */
    protected function getDataToken(string $token): array
    {
        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }
}
