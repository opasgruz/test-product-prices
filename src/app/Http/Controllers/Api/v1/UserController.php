<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreRequest;
use App\Http\Resources\Users\UserCollection;
use App\Models\Users\User;
use App\Http\Resources\Users\User as UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @api {get} /v1/users Список пользователей
 * @apiName UsersList
 * @apiGroup Users
 * @apiVersion 1.0.0
 *
 * @apiUse HeaderRequest
 *
 * @apiUse IndexActionParams
 *
 * @apiSuccess {Object[]} data Результат.
 * @apiSuccess {Integer} data.id ID пользователя
 * @apiSuccess {String} data.name Имя
 * @apiSuccess {String} data.email E-mail
 * @apiSuccess {Date} data.email_verified_at Дата подтверждения в формате Y-m-d H:i:s
 * @apiSuccess {Date} data.created_at Дата создания в формате Y-m-d H:i:s
 * @apiSuccess {Date} data.updated_at Дата обновления в формате Y-m-d H:i:s
 * @apiUse IndexResponseLinks
 * @apiUse IndexResponseMeta
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *      {
 *          "data": [
 *              {
 *                  "id": 14,
 *                  "name": "Сергей4",
 *                  "email": "foobar@yandex.ru",
 *                  "email_verified_at": null,
 *                  "created_at": "2020-02-26 09:09:25",
 *                  "updated_at": "2020-02-26 09:09:25"
 *              },
 *              ...
 *          ],
 *          "links": {
 *              "first": "http://api.laravel-basic.test/v1/users?page=1",
 *              "last": "http://api.laravel-basic.test/v1/users?page=3",
 *              "prev": "http://api.laravel-basic.test/v1/users?page=1",
 *              "next": "http://api.laravel-basic.test/v1/users?page=3"
 *          },
 *          "meta": {
 *              "current_page": 2,
 *              "from": 4,
 *              "last_page": 3,
 *              "path": "http://api.laravel-basic.test/v1/users",
 *              "per_page": "3",
 *              "to": 6,
 *              "total": 7
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
 * @api {get} /v1/users/current Возвращает текущего авторизованного пользователя
 * @apiName UsersCurrent
 * @apiGroup Users
 * @apiVersion 1.0.0
 *
 * @apiUse HeaderRequest
 *
 * @apiSuccess {Object} data Результат.
 * @apiSuccess {Integer} data.id ID пользователя
 * @apiSuccess {String} data.name Имя
 * @apiSuccess {String} data.email E-mail
 * @apiSuccess {Date} data.email_verified_at Дата подтверждения в формате Y-m-d H:i:s
 * @apiSuccess {Date} data.created_at Дата создания в формате Y-m-d H:i:s
 * @apiSuccess {Date} data.updated_at Дата обновления в формате Y-m-d H:i:s
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *      {
 *          "data": {
 *              "id": 14,
 *              "name": "Сергей4",
 *              "email": "foobar@yandex.ru",
 *              "email_verified_at": null,
 *              "created_at": "2020-02-26 09:09:25",
 *              "updated_at": "2020-02-26 09:09:25"
 *          },
 *      }
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 401 Error
 *      {
 *          "message": "Unauthenticated",
 *      }
 */
/**
 * @api {post} /v1/users Создание пользователя
 * @apiName UsersCreate
 * @apiGroup Users
 * @apiVersion 1.0.0
 *
 * @apiUse HeaderRequest
 *
 * @apiParam {String} name Имя
 * @apiParam {String} email E-mail
 * @apiParam {String} password Пароль
 *
 * @apiSuccess {Object} data Результат.
 * @apiSuccess {Integer} data.id ID пользователя
 * @apiSuccess {String} data.name Имя
 * @apiSuccess {String} data.email E-mail
 * @apiSuccess {Date} data.email_verified_at Дата подтверждения в формате Y-m-d H:i:s
 * @apiSuccess {Date} data.created_at Дата создания в формате Y-m-d H:i:s
 * @apiSuccess {Date} data.updated_at Дата обновления в формате Y-m-d H:i:s
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *      {
 *          "data": {
 *              "id": 14,
 *              "name": "Сергей4",
 *              "email": "foobar@yandex.ru",
 *              "email_verified_at": null,
 *              "created_at": "2020-02-26 09:09:25",
 *              "updated_at": "2020-02-26 09:09:25"
 *          },
 *      }
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 422 Error
 *      {
 *          "message": "The given data was invalid.",
 *          "errors": {
 *              "name": [
 *                  "The name field is required."
 *              ],
 *              "email": [
 *                  "The email must be a valid email address."
 *              ],
 *              "password": [
 *                  "The password must be at least 6 characters."
 *              ]
 *          }
 *      }
 */

/**
 * Контроллер для работы с пользователями
 *
 * @package App\Http\Controllers\Api\v1
 */
class UserController extends Controller
{
    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Список пользователей
     *
     * @param Request $request
     *
     * @return UserCollection
     */
    public function index(Request $request): UserCollection
    {
        $query = User::query();

        // Здесь типо фильтруем и прочее (запросы строим в репозитории)

        return new UserCollection($query->paginate($request->get('per_page')));
    }

    /**
     * Добавляем нового пользователя в хранилище
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Возвращает текущего авторизованного пользователя
     *
     * @return UserResource
     */
    public function current(): UserResource
    {
        return new UserResource(auth()->user());
    }
}
