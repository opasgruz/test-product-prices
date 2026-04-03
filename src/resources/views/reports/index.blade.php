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
        async function loadProcesses(page = 1) {
            try {
                const response = await fetch(`/api/v1/reports/processes?page=${page}`);
                const data = await response.json();
                const tbody = document.getElementById('processTableBody');
                tbody.innerHTML = '';

                data.data.forEach(process => {
                    // Логика выделения строки цветом
                    const isError = (process.ps_id === 3);
                    const rowClass = isError ? 'table-danger-row' : '';

                    const statusColor = {
                        1: 'text-primary',   // Запуск
                        2: 'text-success',   // Завершен
                        3: 'text-danger'     // Ошибка
                    }[process.ps_id] || 'text-muted';

                    tbody.innerHTML += `
                    <tr class="${rowClass}">
                        <td class="ps-3 fw-bold">${process.rp_id}</td>
                        <td class="small text-muted">${process.rp_pid}</td>
                        <td>${new Date(process.rp_start_datetime).toLocaleString()}</td>
                        <td>${process.rp_exec_time || '0'} сек.</td>
                        <td>
                            <span class="fw-bold ${statusColor}">
                                ${process.status ? process.status.ps_name : 'Неизвестно'}
                            </span>
                        </td>
                        <td class="text-center">
                            ${process.rp_file_save_path
                        ? `<a href="${process.rp_file_save_path}" class="btn btn-sm btn-success shadow-sm" download>
                                    💾 Скачать CSV
                                   </a>`
                        : (isError ? '❌ Отменен' : '<span class="spinner-border spinner-border-sm text-secondary"></span>')}
                        </td>
                    </tr>
                    `;
                });
            } catch (error) {
                console.error('Ошибка загрузки данных:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', () => loadProcesses(1));

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
                if (response.ok) {
                    loadProcesses(1);
                } else {
                    alert(data.errors ? Object.values(data.errors).flat().join('\n') : 'Ошибка запуска');
                }
            } catch (error) {
                alert('Ошибка сети');
            } finally {
                button.disabled = false;
            }
        }
    </script>
@endsection
