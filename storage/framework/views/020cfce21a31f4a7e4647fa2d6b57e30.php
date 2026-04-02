<?php $__env->startSection('content'); ?>
    <div class="container">
        <h2>Процессы формирования отчетов</h2>

        <div class="card mb-4 p-3">
            <form id="reportForm">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label>Категория товара:</label>
                        <select id="categorySelect" class="form-control">
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->value); ?>"><?php echo e($category->label()); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="button" onclick="loadProcesses(1)" class="btn btn-secondary">Обновить страницу</button>
                        <button type="button" onclick="generateReport()" class="btn btn-primary">Сформировать отчет</button>
                    </div>
                </div>
            </form>
        </div>

        <div id="tableContainer">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>PID</th>
                    <th>Начало</th>
                    <th>Время вып. (сек)</th>
                    <th>Статус</th>
                    <th>Файл</th>
                </tr>
                </thead>
                <tbody id="processTableBody">
                </tbody>
            </table>
            <nav id="paginationLinks"></nav>
        </div>
    </div>

    <script>
        async function loadProcesses(page = 1) {
            try {
                const response = await fetch(`/api/v1/reports/processes?page=${page}`);
                const data = await response.json();

                const tbody = document.getElementById('processTableBody');
                tbody.innerHTML = '';

                data.data.forEach(process => {
                    // Определяем цвет текста в зависимости от ID статуса
                    const statusColor = {
                        1: 'text-primary',   // Запуск (Синий)
                        2: 'text-success',   // Завершен (Зеленый)
                        3: 'text-danger'     // Ошибка (Красный)
                    }[process.ps_id] || 'text-muted';

                    tbody.innerHTML += `
                    <tr>
                        <td>${process.rp_id}</td>
                        <td>${process.rp_pid}</td>
                        <td>${process.rp_start_datetime}</td>
                        <td>${process.rp_exec_time || '0'} сек.</td>
                        <td class="fw-bold ${statusColor}">
                            ${process.status ? process.status.ps_name : 'Неизвестно'}
                        </td>
                        <td>
                            ${process.rp_file_save_path
                        ? `<a href="${process.rp_file_save_path}" class="btn btn-sm btn-outline-info" download>
                                    💾 Скачать
                                   </a>`
                        : '<span class="text-muted">Ожидание...</span>'}
                        </td>
                    </tr>
                `;
                });
            } catch (error) {
                console.error('Ошибка загрузки данных:', error);
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof loadProcesses === "function") {
                loadProcesses(1);
            } else {
                console.error("Функция loadProcesses не найдена. Проверьте подключение внешних JS файлов.");
            }
        });

        // Функция для запуска генерации отчета
        async function generateReport() {
            const categoryId = document.getElementById('categorySelect').value;
            const button = document.querySelector('button[onclick="generateReport()"]');

            // Блокируем кнопку на время запроса
            button.disabled = true;

            try {
                const response = await fetch('/api/v1/reports/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({
                        category_id: categoryId
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    // Выводим сообщение из сервиса (Успешно стартовало / Уже генерируется / Готово)
                    alert(data.message);

                    // Обновляем таблицу процессов, чтобы увидеть новую запись
                    if (typeof loadProcesses === "function") {
                        loadProcesses(1);
                    }
                } else {
                    // Обработка ошибок валидации (из GenerateReportRequest)
                    if (data.errors) {
                        alert('Ошибка: ' + Object.values(data.errors).flat().join(', '));
                    } else {
                        alert('Произошла ошибка при запуске процесса.');
                    }
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Не удалось связаться с сервером.');
            } finally {
                button.disabled = false;
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/reports/index.blade.php ENDPATH**/ ?>