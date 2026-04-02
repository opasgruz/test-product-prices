<?php

namespace App\Jobs;

use App\Repositories\ProcessReportRepository;
use App\Models\ProcessStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $rpId,
        protected int $categoryId,
        protected string $uuid
    ) {}

    public function handle(ProcessReportRepository $repository)
    {
        $startTime = microtime(true);
        $fileName = "report_{$this->categoryId}_" . now()->format('Y-m-d-H-i-s') . ".csv";
        // Путь в хранилище (в папку public для доступа по ссылке)
        $fullPath = storage_path("app/public/reports/" . $fileName);

        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        try {
            DB::beginTransaction();

            $repository->runComplexReport($this->categoryId, $fullPath);

            DB::commit();

            if (file_exists($fullPath)) {
                // Даем права на чтение всем пользователям
                chmod($fullPath, 0664);
                // Если вы запускаете воркер от root, можно сменить владельца на www-data
                // chown($fullPath, 'www-data');
            }

            $execTime = round(microtime(true) - $startTime, 2);
            $downloadUrl = asset('storage/reports/' . $fileName);

            $repository->updateStatus($this->rpId, ProcessStatus::STATUS_COMPLETED, $downloadUrl, $execTime);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Report Generation Error: " . $e->getMessage());
            $repository->updateStatus($this->rpId, ProcessStatus::STATUS_ERROR);
        }
    }
}
