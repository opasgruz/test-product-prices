<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

/**
 * Trait ApiResponse
 *
 * @package App\Traits
 */
trait ApiResponse
{
    /**
     * Отправить ответ с указанием кода состояния
     *
     * @param mixed $data
     * @param int $statusCode
     *
     * @return JsonResponse
     */
    public function sendResponse($data, int $statusCode = 200): JsonResponse
    {
        return Response::json(
            [
                'data' => $data,
            ],
            $statusCode,
            [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Отправить ошибку
     *
     * @param string $message
     * @param int $statusCode
     * @param array $data
     *
     * @return mixed
     */
    public function sendError(string $message, int $statusCode = 400, $data = [])
    {
        $result = ['message' => $message];
        if (!empty($data)) {
            $result['data'] = $data;
        }

        return Response::json(
            $result,
            $statusCode,
            [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
