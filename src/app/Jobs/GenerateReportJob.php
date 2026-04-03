<?php

namespace App\Jobs;

use App\Models\ProcessStatus;
use App\Repositories\ProcessReportRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Задача на генерацию сложного отчета в фоновом режиме.
 */
class GenerateReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Создает новый экземпляр задачи.
     *
     * @param int $rpId ID процесса в базе данных.
     * @param int $categoryId ID категории для фильтрации.
     * @param string $uuid Уникальный идентификатор сессии генерации.
     */
    public function __construct(
        protected int $rpId,
        protected int $categoryId,
        protected string $uuid
    ) {
    }

    /**
     * Выполнение задачи.
     *
     * @param ProcessReportRepository $repository
     * @return void
     */
    public function handle(ProcessReportRepository $repository): void
    {
        $startTime = microtime(true);
        $fileName = "reports/report_{$this->categoryId}_" . now()->format('Y-m-d-H-i-s') . ".csv";
        $disk = Storage::disk('public');

        try {
            // Подготовка директории через Storage abstraction
            if (!$disk->exists('reports')) {
                $disk->makeDirectory('reports');
            }

            $absolutePath = $disk->path($fileName);

            DB::beginTransaction();

            // Генерация данных и запись в файл
            $repository->runComplexReport($this->categoryId, $absolutePath);

            DB::commit();

            $execTime = round(microtime(true) - $startTime, 2);
            $downloadUrl = $disk->url($fileName);

            $repository->updateStatus(
                $this->rpId,
                ProcessStatus::STATUS_COMPLETED,
                $downloadUrl,
                $execTime
            );
        } catch (Exception $e) {
            DB::rollBack();

            Log::error("Report Generation Error [UUID: {$this->uuid}]: " . $e->getMessage(), [
                'exception' => $e,
                'category_id' => $this->categoryId
            ]);

            $repository->updateStatus($this->rpId, ProcessStatus::STATUS_ERROR);
        }
    }
}
