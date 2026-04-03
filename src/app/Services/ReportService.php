<?php

namespace App\Services;

use App\Jobs\GenerateReportJob;
use App\Models\ProcessStatus;
use App\Models\ReportProcess;
use App\Repositories\ProcessReportRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Сервис для управления процессами генерации отчетов.
 * Включает проверку статусов через кэш и взаимодействие с очередями.
 */
class ReportService
{
    /**
     * Создает новый экземпляр сервиса.
     *
     * @param ProcessReportRepository $repository Репозиторий для работы с данными процессов.
     */
    public function __construct(
        protected ProcessReportRepository $repository
    ) {
    }

    /**
     * Запускает процесс генерации отчета или возвращает статус текущего.
     *
     * @param int $categoryId Идентификатор категории товаров.
     * @return array<string, mixed> Массив с сообщением и, при наличии, путем к файлу.
     */
    public function generate(int $categoryId): array
    {
        $today = Carbon::now()->toDateString();
        $cacheKey = "report_cat_{$categoryId}_{$today}";

        // Проверяем наличие запущенного или завершенного процесса в кэше
        if (Cache::has($cacheKey)) {
            $pid = Cache::get($cacheKey);
            $process = $this->repository->findByPid($pid);

            if ($process->ps_id == ProcessStatus::STATUS_STARTED) {
                return ['message' => 'Отчёт по данной категории уже генерируется.'];
            }

            if ($process->ps_id == ProcessStatus::STATUS_COMPLETED) {
                return [
                    'message' => 'Отчёт успешно сгенерирован.',
                    'path' => $process->rp_file_save_path,
                ];
            }
        }

        // Если активных процессов нет — создаем новый UUID и запись в БД
        $uuid = (string) Str::uuid();
        $rpId = $this->repository->createInitialProcess($uuid);

        // Сохраняем UUID в кэш на сутки
        Cache::put($cacheKey, $uuid, now()->addDay());

        // Отправка задачи в очередь
        GenerateReportJob::dispatch($rpId, $categoryId, $uuid);

        return ['message' => 'Генерация отчёта успешно стартовала.'];
    }

    /**
     * Получает список всех процессов генерации с пагинацией.
     *
     * @param int $perPage Количество записей на страницу.
     * @return LengthAwarePaginator Пагинированный список моделей ReportProcess.
     */
    public function getProcessesPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return ReportProcess::with('status')
            ->orderBy('rp_id', 'desc')
            ->paginate($perPage);
    }
}
