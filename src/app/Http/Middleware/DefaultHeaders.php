<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Проставляем дефолтные заголовоки
 *
 * @package App\Http\Middleware
 */
class DefaultHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request = $this->setRequestAcceptHeader($request);
        $request = $this->setRequestXUuidHeader($request);

        /** @var Response $response */
        $response = $next($request);
        $response = $this->setResponseXUuidHeader($response, $request->header('X-UUID'));

        return $response;
    }

    /**
     * Устанавливаем Accept заголовок для запроса если не передан
     *
     * @param Request $request
     *
     * @return Request
     */
    public function setRequestAcceptHeader(Request $request): Request
    {
        $accept = $request->header('Accept');
        if ($accept === '*/*') {
            $request->headers->set('Accept', 'application/json');
        }

        return $request;
    }

    /**
     * Устанавливаем X-UUID заголовок для запроса если не передан
     *
     * @param Request $request
     *
     * @return Request
     */
    public function setRequestXUuidHeader(Request $request): Request
    {
        $xUuid = $request->header('X-UUID');
        if ($xUuid === null) {
            $request->headers->set('X-UUID', (string)Str::uuid());
        }

        return $request;
    }

    /**
     * Устанавливаем X-UUID заголовок для ответа
     *
     * @param Response $response
     * @param string $xUuid
     *
     * @return Response
     */
    public function setResponseXUuidHeader(Response $response, string $xUuid): Response
    {
        $response->header('X-UUID', $xUuid);

        return $response;
    }
}
