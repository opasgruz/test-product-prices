@extends('layouts.app')

@section('content')
    <div class="py-4">
        <h2 class="mb-4 fw-bold text-dark">Процессы формирования отчетов</h2>

        <div class="card report-card mb-4 p-4">
            <form id="reportForm">
                <div class="row align-items-end g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Категория товара:</label>
                        <select id="categorySelect" class="form-select">
                            @foreach($categories as $category)
                                <option value="{{ $category->value }}">{{ $category->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7 d-flex gap-2">
                        <button type="button" onclick="loadProcesses(1)" class="btn btn-outline-secondary w-100">
                            🔄 Обновить список
                        </button>
                        <button type="button" onclick="generateReport()" class="btn btn-primary w-100">
                            📊 Сформировать отчет
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover align-middle bg-white m-0">
                <thead class="table-light">
                <tr>
                    <th class="ps-3">ID</th>
                    <th>PID (UUID)</th>
                    <th>Дата начала</th>
                    <th>Выполнение</th>
                    <th>Статус</th>
                    <th class="text-center">Результат</th>
                </tr>
                </thead>
                <tbody id="processTableBody">
                </tbody>
            </table>
        </div>
        <div id="paginationLinks" class="mt-3"></div>
    </div>

    <script>
        /**
         * Загрузка списка процессов с поддержкой пагинации.
         */
        async function loadProcesses(page = 1) {
            try {
                // Если page не передан или null, выходим (для отключенных кнопок пагинации)
                if (!page) return;

                const response = await fetch(`/api/v1/reports/processes?page=${page}`);
                const data = await response.json();

                const tbody = document.getElementById('processTableBody');
                tbody.innerHTML = '';

                // 1. Отрисовка строк таблицы
                data.data.forEach(process => {
                    const isError = (process.ps_id === 3);
                    const isEmpty = (process.ps_id === 4); // Новый статус
                    const rowClass = isError ? 'table-danger-row' : (isEmpty ? 'table-warning-row' : '');

                    const statusColor = {
                        1: 'text-primary',   // Запуск
                        2: 'text-success',   // Завершен
                        3: 'text-danger',    // Ошибка
                        4: 'text-warning'    // Нет товаров
                    }[process.ps_id] || 'text-muted';

                    // Логика отображения колонки "Результат"
                    let resultHtml = '';
                    if (process.rp_file_save_path && process.ps_id === 2) {
                        resultHtml = `<a href="${process.rp_file_save_path}" class="btn btn-sm btn-success shadow-sm" download>
                        💾 Скачать CSV
                      </a>`;
                    } else if (isError) {
                        resultHtml = '❌ Ошибка';
                    } else if (isEmpty) {
                        resultHtml = '∅ Нет данных';
                    } else {
                        resultHtml = '<span class="spinner-border spinner-border-sm text-secondary"></span>';
                    }

                    tbody.innerHTML += `
                        <tr class="${rowClass}">
                            <td class="ps-3 fw-bold">${process.rp_id}</td>
                            <td class="small text-muted">${process.rp_pid}</td>
                            <td>${new Date(process.rp_start_datetime).toLocaleString()}</td>
                            <td>${process.rp_exec_time || '0'} сек.</td>
                            <td>
                                <span class="fw-bold ${statusColor}">
                                    ${process.status ? process.status.ps_name : (isEmpty ? 'Нет товаров' : 'Неизвестно')}
                                </span>
                            </td>
                            <td class="text-center">${resultHtml}</td>
                        </tr>
                    `;
                });

                // 2. Вызов функции отрисовки пагинации
                renderPagination(data);

            } catch (error) {
                console.error('Ошибка загрузки данных:', error);
            }
        }

        /**
         * Отрисовка кнопок пагинации на основе данных от API.
         */
        function renderPagination(data) {
            const paginationWrapper = document.getElementById('paginationLinks');

            // Если страниц меньше одной, скрываем пагинацию
            if (!data.links || data.last_page <= 1) {
                paginationWrapper.innerHTML = '';
                return;
            }

            let html = '<nav><ul class="pagination pagination-sm justify-content-center shadow-sm">';

            data.links.forEach(link => {
                const activeClass = link.active ? 'active' : '';
                const disabledClass = !link.url ? 'disabled' : '';

                // Используем поле "page" из вашего JSON для перехода
                const pageNum = link.page;

                html += `
                <li class="page-item ${activeClass} ${disabledClass}">
                    <button class="page-link"
                            onclick="loadProcesses(${pageNum})"
                            ${!pageNum ? 'disabled' : ''}>
                        ${link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                    </button>
                </li>
            `;
            });

            html += '</ul></nav>';
            paginationWrapper.innerHTML = html;
        }

        /**
         * Запуск генерации отчета.
         */
        async function generateReport() {
            const categoryId = document.getElementById('categorySelect').value;
            const button = document.querySelector('button[onclick="generateReport()"]');
            button.disabled = true;

            try {
                const response = await fetch('/api/v1/reports/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ category_id: categoryId })
                });

                const data = await response.json();

                // ВАЖНО: Выводим текстовое сообщение из ответа (например, "Отчёт уже генерируется")
                if (data.message) {
                    alert(data.message);
                }

                if (response.ok) {
                    // Обновляем список, чтобы увидеть новую запись
                    loadProcesses(1);
                } else if (data.errors) {
                    // Вывод ошибок валидации Laravel (422)
                    alert(Object.values(data.errors).flat().join('\n'));
                }
            } catch (error) {
                alert('Ошибка при выполнении запроса');
                console.error(error);
            } finally {
                button.disabled = false;
            }
        }

        // Первичная загрузка при открытии страницы
        document.addEventListener('DOMContentLoaded', () => loadProcesses(1));
    </script>
@endsection
