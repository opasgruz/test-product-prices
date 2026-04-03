<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateReportRequest;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для управления отчетами через API.
 */
class ReportController extends Controller
{
    /**
     * Сервис для работы с логикой отчетов.
     *
     * @var ReportService
     */
    protected ReportService $reportService;

    /**
     * Конструктор
     *
     * @param ReportService $reportService
     */
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Возвращает список процессов с пагинацией.
     * По умолчанию выводит по 10 записей на страницу.
     *
     * @return JsonResponse
     */
    public function getProcesses(): JsonResponse
    {
        $processes = $this->reportService->getProcessesPaginated(10);

        return response()->json($processes);
    }

    /**
     * Инициирует генерацию нового отчета.
     *
     * @param GenerateReportRequest $request
     * @return JsonResponse
     */
    public function generate(GenerateReportRequest $request): JsonResponse
    {
        $result = $this->reportService->generate($request->category_id);

        return response()->json($result);
    }
}
