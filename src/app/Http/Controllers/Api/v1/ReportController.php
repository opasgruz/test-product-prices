<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateReportRequest;
use App\Models\ProcessStatus;
use App\Repositories\ProcessReportRepository;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function getProcesses(): JsonResponse
    {
        $processes = $this->reportService->getProcessesPaginated(10);
        return response()->json($processes);
    }

    public function generate(GenerateReportRequest $request): JsonResponse
    {
//        $fileName = "report_7_" . now()->format('Y-m-d-H-i-s') . ".csv";
//        $fullPath = storage_path("app/public/reports/" . $fileName);
//
//        if (!file_exists(dirname($fullPath))) {
//            mkdir(dirname($fullPath), 0755, true);
//        }
//
//        $repository = new \App\Repositories\ProcessReportRepository();
//
//        try {
//            // Обязательно открываем транзакцию здесь
//            DB::transaction(function () use ($repository, $fullPath) {
//                $repository->runComplexReport(7, $fullPath);
//            });
//        } catch (\Exception $e) {
//            return response()->json(['error' => $e->getMessage()]);
//        }
//        return response()->json([111]);


        $result = $this->reportService->generate($request->category_id);

        return response()->json($result);
    }
}
