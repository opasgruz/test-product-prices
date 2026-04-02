<?php

namespace App\Services;

use App\Jobs\GenerateReportJob;
use App\Models\ProcessStatus;
use App\Models\ReportProcess;
use App\Repositories\ProcessReportRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ReportService
{
    public function __construct(protected ProcessReportRepository $repository) {}

    public function generate(int $categoryId)
    {
        $today = Carbon::now()->toDateString();
        $cacheKey = "report_cat_{$categoryId}_{$today}";

        if (Cache::has($cacheKey)) {
            $pid = Cache::get($cacheKey);
            $process = $this->repository->findByPid($pid);

            if ($process->ps_id == ProcessStatus::STATUS_STARTED) {
                return ['message' => 'Отчёт по данной категории уже генерируется.'];
            }
            if ($process->ps_id == ProcessStatus::STATUS_COMPLETED) {
                return ['message' => 'Отчёт успешно сгенерирован.', 'path' => $process->rp_file_save_path];
            }
        }

        // Если в кэше нет — создаем новый процесс
        $uuid = (string) Str::uuid();
        $rpId = $this->repository->createInitialProcess($uuid);

        Cache::put($cacheKey, $uuid, now()->addDay());

        // Запуск Job
        GenerateReportJob::dispatch($rpId, $categoryId, $uuid);

        return ['message' => 'Генерация отчёта успешно стартовала.'];
    }

    public function getProcessesPaginated(int $perPage = 10)
    {
        return ReportProcess::with('status') // Загружаем данные из process_status
        ->orderBy('rp_start_datetime', 'desc')
            ->paginate($perPage);
    }
}
