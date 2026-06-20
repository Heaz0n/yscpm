@extends('layouts.app')

@section('title', 'Список студентов')

@section('content')
<style>
    /* Все стили из старого файла (можно вынести в отдельный CSS) */
    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .filter-group, .school-year-group {
        flex: 1;
        min-width: 200px;
    }
    .fio-search-container {
        position: relative;
        flex: 1;
        min-width: 200px;
    }
    .fio-search-icon {
        position: absolute;
        right: 10px;
        top: 40%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    .search-loading {
        position: absolute;
        right: 35px;
        top: 50%;
        transform: translateY(-50%);
        display: none;
    }
    .search-loading.show {
        display: block;
    }
    .budget-filter-btn {
        padding: 10px 14px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        background-color: #fff;
        cursor: pointer;
        transition: all 0.2s;
        margin-right: 10px;
        font-weight: 500;
    }
    .budget-filter-btn.active {
        background-color: #007bff;
        color: #fff;
        border-color: #007bff;
    }
    .budget-filter-btn:hover {
        background-color: #e6f3ff;
        border-color: #0056b3;
    }
    .current-filters {
        margin-bottom: 15px;
        font-size: 0.95rem;
        color: #495057;
        font-weight: 500;
    }
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .data-table {
        width: 100%;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .data-table th {
        position: sticky;
        top: 0;
        background-color: #ffffff;
        color: #000000;
        font-weight: 600;
        padding: 12px;
        border-right: 1px solid #e9ecef;
        text-align: center;
        z-index: 10;
    }
    .data-table td {
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
        border-right: 1px solid #e9ecef;
        vertical-align: middle;
        text-align: center;
        position: relative;
    }
    .data-table td:last-child {
        border-right: none;
    }
    .data-table tr:last-child td {
        border-bottom: none;
    }
    .data-table tr:hover {
        background-color: #f1f3f5;
    }
    .editable-cell {
        cursor: pointer;
        display: inline-block;
        width: 100%;
        height: 100%;
        padding: 5px;
        border-radius: 4px;
        transition: background-color 0.2s;
    }
    .editable-cell:hover {
        background-color: #e9ecef;
    }
    .editable-cell.editing {
        background-color: #fff;
        border: 1px solid #007bff;
    }
    .editable-cell input, .editable-cell select {
        width: 100%;
        border: none;
        outline: none;
        padding: 2px;
        font-size: 0.9rem;
    }
    .month-cell {
        cursor: pointer;
        transition: background-color 0.2s;
        text-align: center;
        vertical-align: middle;
        position: relative;
        min-height: 40px;
    }
    .month-cell:hover {
        background-color: #e9ecef !important;
    }
    .month-cell.has-reason {
        background-color: rgba(40, 167, 69, 0.1);
    }
    .month-cell.has-reason:hover {
        background-color: rgba(40, 167, 69, 0.2) !important;
    }
    .month-cell.no-reason {
        background-color: rgba(108, 117, 125, 0.05);
    }
    .month-cell:focus {
        outline: 2px solid #007bff;
        outline-offset: -2px;
    }
    .month-cell i {
        font-size: 1.2rem;
    }
    .autumn-month {
        background-color: rgba(255, 193, 7, 0.1);
    }
    .autumn-month:hover {
        background-color: rgba(255, 193, 7, 0.2) !important;
    }
    .student-row-selectable {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .student-row-selectable:hover {
        background-color: #f1f3f5 !important;
    }
    .student-row-added {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }
    .student-row-added .fio-selectable {
        font-weight: 500;
    }
    .student-row-selected {
        background-color: rgba(40, 167, 69, 0.1) !important;
        border-left: 3px solid #28a745;
    }
    .student-row-selected td:first-child {
        position: relative;
    }
    .student-row-selected td:first-child::before {
        content: "✓";
        position: absolute;
        left: 5px;
        top: 50%;
        transform: translateY(-50%);
        color: #28a745;
        font-weight: bold;
    }
    .table-responsive {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
    }
    #year-header th {
        text-align: center;
        font-weight: 600;
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
    .table-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 20;
    }
    .table-loading.show {
        display: flex;
    }
    .category-select-container {
        margin-bottom: 15px;
    }
    .autocomplete-suggestions {
        position: absolute;
        z-index: 1000;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: none;
    }
    .autocomplete-suggestion {
        padding: 8px 12px;
        cursor: pointer;
    }
    .autocomplete-suggestion:hover {
        background-color: #f1f3f5;
    }
    .payout-modal .modal-dialog {
        max-width: 350px;
    }
    .payout-modal .modal-body {
        padding: 1.5rem;
    }
    .payout-input {
        text-align: center;
        font-size: 2rem;
        font-weight: 600;
        color: #0d6efd;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 0.5rem;
        width: 100%;
        margin: 1rem 0;
    }
    .payout-input:focus {
        border-color: #0d6efd;
        outline: none;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.25);
    }
    .payout-description {
        font-size: 0.9rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }
    .payout-result {
        margin-top: 1rem;
        padding: 1rem;
        background-color: #e8f5e9;
        border-radius: 8px;
        text-align: center;
        display: none;
    }
    .payout-result.show {
        display: block;
    }
    .payout-result-text {
        font-size: 1.2rem;
        font-weight: 500;
        color: #2e7d32;
    }
    .btn-payout {
        background-color: #6f42c1;
        color: white;
        border: none;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-payout:hover {
        background-color: #5e35b1;
    }
    .delete-student-btn {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: 1.2rem;
        transition: color 0.2s;
        padding: 0 5px;
    }
    .delete-student-btn:hover {
        color: #a71d2a;
    }
</style>

<div class="container mt-4">
    <h2>Список студентов</h2>

    <!-- Панель фильтров -->
    <div class="filter-bar">
        <div class="school-year-group">
            <select id="school-filter" class="form-select" {{ $currentUserRole !== 'admin' && count($schools) <= 1 ? 'disabled' : '' }}>
                @if($currentUserRole === 'admin')
                    <option value="">Все школы</option>
                @endif
                @foreach($schools as $school)
                    <option value="{{ $school->code }}" {{ ($currentUserRole !== 'admin' && $school->code == $currentUserSchool) ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                @endforeach
            </select>
            <select id="year-filter" class="form-select mt-2">
                <option value="">Все годы</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ $year === $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
            @if(in_array($currentUserRole, ['admin', 'director', 'member']))
                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addAcademicYearModal">
                    <i class="bi bi-calendar"></i> Учебный год
                </button>
            @endif
        </div>
        <div class="filter-group">
            <label class="form-label">Сортировка:</label><br>
            <button class="budget-filter-btn" data-value="РФ">РФ</button>
            <button class="budget-filter-btn" data-value="ХМАО">ХМАО</button>
            @if($currentUserRole === 'admin')
                <button class="btn btn-outline-danger btn-sm mt-2" id="reset-filters">
                    <i class="bi bi-x-circle"></i> Сбросить все
                </button>
            @endif
        </div>
        <div class="fio-search-container">
            <input type="text" id="fio-search" class="form-control" placeholder="Поиск по ФИО студента">
            <i class="bi bi-search fio-search-icon"></i>
            <div class="spinner-border spinner-border-sm search-loading" id="search-loading" role="status">
                <span class="visually-hidden">Поиск...</span>
            </div>
        </div>
    </div>

    <div class="current-filters" id="current-filters">Фильтры: Нет активных фильтров</div>

    <div class="table-header">
        <span id="student-count">Студентов: 0</span>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                <i class="bi bi-person-plus"></i> Добавить студентов
            </button>
            <button class="btn btn-success ms-2" id="generateProtocolBtn">
                <i class="bi bi-file-earmark-word"></i> Сгенерировать протокол
            </button>
            <a href="{{ route('protocols.index') }}" class="btn btn-info ms-2">
                <i class="bi bi-files"></i> Сгенерированные протоколы
            </a>
            <button class="btn-payout ms-2" data-bs-toggle="modal" data-bs-target="#payoutModal">
                <i class="bi bi-calendar-check"></i> Выплат: <span id="payoutCountDisplay">{{ $payoutCount }}</span>
            </button>
        </div>
    </div>

    <!-- Таблица -->
    <div class="table-responsive position-relative">
        <table class="table data-table">
            <thead>
                <tr id="year-header"></tr>
                <tr id="month-header">
                    <th scope="col">#</th>
                    <th scope="col">ФИО</th>
                    <th scope="col">Группа</th>
                    <th scope="col">Бюджет</th>
                    <th scope="col">Сентябрь</th>
                    <th scope="col">Октябрь</th>
                    <th scope="col">Ноябрь</th>
                    <th scope="col">Декабрь</th>
                    <th scope="col">Январь</th>
                    <th scope="col">Февраль</th>
                    <th scope="col">Март</th>
                    <th scope="col">Апрель</th>
                    <th scope="col">Май</th>
                    <th scope="col">Июнь</th>
                    <th scope="col">Июль</th>
                    <th scope="col">Август</th>
                    <th scope="col">Действия</th>
                </tr>
            </thead>
            <tbody id="student-table-body"></tbody>
        </table>
        <div class="table-loading" id="table-loading">
            <div class="spinner-border" role="status"><span class="visually-hidden">Загрузка...</span></div>
        </div>
    </div>
</div>

<!-- ==================== МОДАЛЬНЫЕ ОКНА ==================== -->

<!-- Модальное окно выбора категории (выплата) -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выбрать основание</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="category-select-container position-relative">
                    <label for="categoryInput" class="form-label">Введите или выберите категорию:</label>
                    <input type="text" id="categoryInput" class="form-control" placeholder="Введите категорию">
                    <input type="hidden" id="categoryId" value="">
                    <div id="autocompleteSuggestions" class="autocomplete-suggestions"></div>
                </div>
                <div class="mb-3">
                    <label for="amountInput" class="form-label">Сумма (руб.):</label>
                    <input type="number" id="amountInput" class="form-control" step="0.01" min="0">
                    <small class="text-muted" id="maxAmountHint"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="clearCategoryBtn" style="display: none;">Очистить</button>
                <button type="button" class="btn btn-primary" id="saveCategoryBtn">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно добавления студентов -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить студентов</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Категория для добавляемых студентов:</label>
                    <select id="addCategorySelect" class="form-select">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->number }} - {{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 position-relative">
                    <label for="studentSearch" class="form-label">Поиск по ФИО:</label>
                    <input type="text" id="studentSearch" class="form-control" placeholder="Введите ФИО">
                    <i class="bi bi-search fio-search-icon"></i>
                    <div class="spinner-border spinner-border-sm search-loading" id="student-search-loading" role="status">
                        <span class="visually-hidden">Поиск...</span>
                    </div>
                </div>
                <div class="student-table-container hidden">
                    <table class="table table-hover" id="studentListTable">
                        <thead>
                            <tr>
                                <th style="width:40px;"><input type="checkbox" id="selectAllStudents" class="select-all-checkbox" title="Выбрать всех"></th>
                                <th>ФИО</th>
                                <th>Группа</th>
                                <th>Бюджет</th>
                            </tr>
                        </thead>
                        <tbody id="student-list-body"></tbody>
                    </table>
                    <div class="table-loading" id="student-list-loading">
                        <div class="spinner-border" role="status"><span class="visually-hidden">Загрузка...</span></div>
                    </div>
                </div>
                <button class="btn btn-success mt-3" id="addSelectedStudentsBtn"><i class="bi bi-plus-circle"></i> Добавить выбранных</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения удаления студента -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body" id="deleteModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Удалить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно сброса фильтров и данных -->
@if($currentUserRole === 'admin')
<div class="modal fade" id="resetConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение сброса</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">Вы уверены, что хотите сбросить все фильтры и очистить все данные об основаниях? Это действие нельзя отменить.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmResetBtn">Сбросить</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Модальное окно добавления учебного года -->
@if(in_array($currentUserRole, ['admin', 'director', 'member']))
<div class="modal fade" id="addAcademicYearModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить учебный год</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="academicYearInput" class="form-label">Учебный год (ГГГГ/ГГГГ):</label>
                    <input type="text" id="academicYearInput" class="form-control" placeholder="Например: 2024/2025">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveAcademicYearBtn">Сохранить</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Модальное окно генерации протокола -->
<div class="modal fade" id="generateProtocolModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Генерация протокола</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="protocolNumber" class="form-label">Номер протокола:</label>
                    <input type="text" id="protocolNumber" class="form-control" placeholder="Введите номер протокола">
                </div>
                <div class="mb-3">
                    <label for="protocolDay" class="form-label">День:</label>
                    <input type="number" id="protocolDay" class="form-control" min="1" max="31" value="1">
                </div>
                <div class="mb-3">
                    <label for="protocolMonth" class="form-label">Месяц:</label>
                    <select id="protocolMonth" class="form-select">
                        <option value="9">Сентябрь</option>
                        <option value="10">Октябрь</option>
                        <option value="11">Ноябрь</option>
                        <option value="12">Декабрь</option>
                        <option value="1">Январь</option>
                        <option value="2">Февраль</option>
                        <option value="3">Март</option>
                        <option value="4">Апрель</option>
                        <option value="5">Май</option>
                        <option value="6">Июнь</option>
                        <option value="7">Июль</option>
                        <option value="8">Август</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="generateProtocolConfirmBtn">Сформировать</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно уведомлений -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Уведомление</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body" id="notificationModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно настройки количества выплат -->
<div class="modal fade payout-modal" id="payoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Количество выплат за год</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <label for="payoutCount" class="form-label">Введите число от 2 до 4:</label>
                <input type="number" class="payout-input" id="payoutCount" min="2" max="4" step="1" value="{{ $payoutCount }}">
                <div class="text-danger small" id="payoutError" style="display: none;">Нужно целое число от 2 до 4</div>
                <div class="payout-description"><i class="bi bi-info-circle"></i> Сколько раз в году будут производиться выплаты</div>
                <button class="btn btn-primary w-100 mt-3" id="confirmPayoutBtn"><i class="bi bi-check-lg"></i> Утвердить</button>
                <div class="payout-result" id="payoutResult"><div class="payout-result-text" id="payoutResultText"></div></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ============================================================
    // ПЕРЕДАЁМ ДАННЫЕ ИЗ PHP В JAVASCRIPT
    // ============================================================
    const schools = @json($schools);
    const categories = @json($categories);
    const groups = @json($groups);
    const years = @json($years);
    const selectedYear = @json($selectedYear);
    const currentUserRole = @json($currentUserRole);
    const currentUserSchool = @json($currentUserSchool);
    const payoutCountDefault = @json($payoutCount);

    // CSRF-токен (для AJAX)
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ============================================================
    // ГЛОБАЛЬНЫЕ ПЕРЕМЕННЫЕ
    // ============================================================
    let studentCache = null;
    let lastFilterHash = '';
    let selectedSchool = currentUserRole !== 'admin' ? currentUserSchool : '';
    let selectedYearVal = selectedYear || years[0] || '';
    let payoutCount = payoutCountDefault;
    let activeBudgetFilters = [];
    let currentCell = null;
    let studentSearchCache = {};
    let addedStudentsInSession = new Set();
    let selectedCategoryForAdd = categories.length ? categories[0].id : null;
    let studentListData = [];
    let pendingDeleteStudentId = null;

    // ============================================================
    // ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
    // ============================================================
    function showNotificationModal(message) {
        document.getElementById('notificationModalBody').innerHTML = message;
        new bootstrap.Modal(document.getElementById('notificationModal')).show();
    }

    function getCategoryName(categoryId) {
        const category = categories.find(cat => cat.id == categoryId);
        if (!category) return '';
        const pattern = new RegExp(`^${category.number}\\s*[-–—:]\\s*`);
        return category.category_name.replace(pattern, '');
    }

    function displayCurrentFilters() {
        const schoolFilter = document.getElementById('school-filter').value;
        const yearFilter = document.getElementById('year-filter').value;
        const fio = document.getElementById('fio-search').value.trim();
        let filters = [];
        if (schoolFilter && currentUserRole === 'admin') {
            const school = schools.find(s => s.code === parseInt(schoolFilter));
            filters.push(`Школа: ${school ? school.name : schoolFilter}`);
        } else if (currentUserRole !== 'admin') {
            const school = schools.find(s => s.code === parseInt(currentUserSchool));
            if (school) filters.push(`Школа: ${school.name}`);
        }
        if (yearFilter) filters.push(`Год: ${yearFilter}`);
        if (activeBudgetFilters.length > 0) filters.push(`Бюджет: ${activeBudgetFilters.join(', ')}`);
        if (fio) filters.push(`ФИО: ${fio}`);
        document.getElementById('current-filters').textContent = 'Фильтры: ' + (filters.length ? filters.join(', ') : 'Нет активных фильтров');
    }

    // ============================================================
    // ОСНОВНЫЕ ФУНКЦИИ ЗАГРУЗКИ И ОТРИСОВКИ ТАБЛИЦЫ
    // ============================================================
    async function updateTable() {
        const tableLoading = document.getElementById('table-loading');
        tableLoading.classList.add('show');

        const school = selectedSchool;
        const year = selectedYearVal;
        const fio = document.getElementById('fio-search').value.trim();

        const filterHash = JSON.stringify({ year, school, regions: activeBudgetFilters, fio });
        if (studentCache && lastFilterHash === filterHash) {
            renderTable(studentCache);
            tableLoading.classList.remove('show');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'get_students');
        formData.append('schools', JSON.stringify(school ? [parseInt(school)] : []));
        formData.append('years', JSON.stringify(year ? [year] : []));
        formData.append('regions', JSON.stringify(activeBudgetFilters));
        formData.append('fio', fio);

        try {
            const response = await fetch('{{ route('students.action') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });
            const result = await response.json();
            studentCache = result;
            lastFilterHash = filterHash;
            renderTable(result);
        } catch (error) {
            showNotificationModal('Ошибка загрузки данных: ' + error.message);
            renderTable({ status: 'error', data: [], total: 0 });
        } finally {
            tableLoading.classList.remove('show');
        }
    }

    const debouncedUpdateTable = debounce(updateTable, 150);

    function renderTable(result) {
        const tbody = document.getElementById('student-table-body');
        const yearHeader = document.getElementById('year-header');
        tbody.innerHTML = '';
        yearHeader.innerHTML = '';

        const academicYear = selectedYearVal || years[0] || '';
        let startYear = '', endYear = '';
        if (academicYear && academicYear.includes('/')) {
            [startYear, endYear] = academicYear.split('/');
        } else {
            const cy = new Date().getFullYear();
            startYear = cy;
            endYear = cy + 1;
        }

        yearHeader.innerHTML = `<th colspan="4"></th><th colspan="4">${startYear}</th><th colspan="8">${endYear}</th><th></th>`;

        if (result.status !== 'success' || result.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="17" class="text-center">Студенты не найдены.</td></tr>`;
            document.getElementById('student-count').textContent = 'Студентов: 0';
        } else {
            result.data.forEach((student, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td><span class="editable-cell" data-student="${student.id}" data-field="full_name" data-value="${student.full_name.replace(/"/g, '&quot;')}" data-bs-toggle="tooltip" title="${student.full_name.replace(/"/g, '&quot;')}">${student.full_name}</span></td>
                    <td><span class="editable-cell" data-student="${student.id}" data-field="group_id" data-value="${student.group_id}" data-bs-toggle="tooltip" title="${student.group_name.replace(/"/g, '&quot;')}">${student.group_name}</span></td>
                    <td><span class="editable-cell" data-student="${student.id}" data-field="budget" data-value="${student.budget || ''}" data-bs-toggle="tooltip" title="${student.budget || 'Нет бюджета'}">${student.budget || '-'}</span></td>
                    ${[9,10,11,12,1,2,3,4,5,6,7,8].map(month => {
                        const reasonData = student.reasons[month] || {};
                        const reason = reasonData.reason || '';
                        const amount = reasonData.amount || '';
                        const categoryId = reasonData.category_id || '';
                        const category = categoryId ? categories.find(cat => cat.id == categoryId) : null;
                        const categoryNumber = category ? category.number : '';
                        const hasReason = !!reason;
                        const tooltipText = hasReason ? (categoryNumber ? `[${categoryNumber}] ` : '') + reason + (amount ? ` (${amount} руб.)` : '') : '';
                        const monthClasses = ['month-cell', hasReason ? 'has-reason' : 'no-reason'];
                        if (month >= 10 && month <= 12) monthClasses.push('autumn-month');
                        return `<td class="${monthClasses.join(' ')}" 
                            data-student="${student.id}" data-month="${month}" 
                            data-category="${categoryId}" data-category-number="${categoryNumber}"
                            data-amount="${amount}" data-reason="${reason.replace(/"/g, '&quot;')}" 
                            ${hasReason ? `data-bs-toggle="tooltip" title="${tooltipText.replace(/"/g, '&quot;')}"` : ''}
                            role="button" tabindex="0">
                            ${hasReason ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-secondary"></i>'}
                        </td>`;
                    }).join('')}
                    <td class="text-center">
                        <button class="delete-student-btn" data-student-id="${student.id}" data-student-name="${student.full_name.replace(/"/g, '&quot;')}" title="Удалить студента">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            document.getElementById('student-count').textContent = `Студентов: ${result.total}`;
        }

        // Tooltips
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

        // Редактируемые ячейки
        document.querySelectorAll('.editable-cell').forEach(cell => {
            cell.addEventListener('click', function() {
                if (this.classList.contains('editing')) return;
                const studentId = this.dataset.student, field = this.dataset.field, currentValue = this.dataset.value;
                this.classList.add('editing');
                if (field === 'group_id') {
                    this.innerHTML = `<select class="cell-input">${groups.map(g => `<option value="${g.id}" ${g.id == currentValue ? 'selected' : ''}>${g.group_name}</option>`).join('')}</select>`;
                } else {
                    this.innerHTML = `<input type="text" value="${currentValue}" class="cell-input">`;
                }
                const input = this.querySelector('.cell-input');
                input.focus();
                const saveCell = async () => {
                    const newValue = input.value.trim();
                    if (newValue === currentValue || (field === 'full_name' && !newValue)) {
                        this.classList.remove('editing');
                        this.textContent = currentValue || (field === 'budget' ? '-' : currentValue);
                        return;
                    }
                    try {
                        const formData = new FormData();
                        formData.append('action', 'update_student');
                        formData.append('student_id', studentId);
                        formData.append('field', field);
                        formData.append('value', newValue);
                        const response = await fetch('{{ route('students.action') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            body: formData
                        });
                        const result = await response.json();
                        if (result.status === 'success') {
                            if (field === 'group_id') {
                                const group = groups.find(g => g.id == newValue);
                                this.textContent = group ? group.group_name : newValue;
                            } else this.textContent = newValue || '-';
                            this.dataset.value = newValue;
                            this.setAttribute('title', newValue || 'Нет бюджета');
                            new bootstrap.Tooltip(this).dispose();
                            new bootstrap.Tooltip(this);
                            showNotificationModal('Данные обновлены');
                            studentCache = null;
                            debouncedUpdateTable();
                        } else showNotificationModal(`Ошибка: ${result.message}`);
                    } catch (error) {
                        showNotificationModal(`Ошибка сети: ${error.message}`);
                    }
                    this.classList.remove('editing');
                };
                input.addEventListener('blur', saveCell);
                input.addEventListener('keydown', e => {
                    if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
                    else if (e.key === 'Escape') {
                        this.classList.remove('editing');
                        this.textContent = currentValue || (field === 'budget' ? '-' : currentValue);
                    }
                });
            });
        });

        // Клик по ячейке месяца
        document.querySelectorAll('.month-cell').forEach(cell => {
            cell.addEventListener('click', function() {
                currentCell = this;
                const studentId = this.dataset.student, month = this.dataset.month;
                const currentCategoryId = this.dataset.category || '', currentReason = this.dataset.reason || '', currentAmount = this.dataset.amount || '';
                if (!selectedYearVal) { showNotificationModal('Учебный год не выбран'); return; }
                const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
                const categoryInput = document.getElementById('categoryInput');
                const categoryIdInput = document.getElementById('categoryId');
                const amountInput = document.getElementById('amountInput');
                const maxAmountHint = document.getElementById('maxAmountHint');
                if (currentCategoryId) {
                    categoryIdInput.value = currentCategoryId;
                    categoryInput.value = currentReason;
                    amountInput.value = currentAmount;
                    const cat = categories.find(c => c.id == currentCategoryId);
                    if (cat) maxAmountHint.textContent = `Максимум: ${cat.max_amount} руб.`;
                    else maxAmountHint.textContent = '';
                } else {
                    categoryIdInput.value = '';
                    categoryInput.value = '';
                    amountInput.value = '';
                    maxAmountHint.textContent = '';
                }
                document.getElementById('clearCategoryBtn').style.display = currentCategoryId ? 'block' : 'none';
                modal.show();
            });
            cell.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); cell.click(); }
            });
        });

        // Кнопки удаления студента
        document.querySelectorAll('.delete-student-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const studentId = parseInt(this.dataset.studentId);
                const studentName = this.dataset.studentName;
                let hasReasons = false;
                if (studentCache && studentCache.status === 'success') {
                    const studentData = studentCache.data.find(s => s.id == studentId);
                    if (studentData && studentData.reasons && Object.keys(studentData.reasons).length > 0) hasReasons = true;
                }
                const modalBody = document.getElementById('deleteModalBody');
                modalBody.innerHTML = `<p>Вы действительно хотите удалить студента <strong>${studentName}</strong>?</p>${hasReasons ? '<div class="alert alert-warning">⚠️ Внимание! У этого студента уже есть назначенные выплаты. При удалении все данные о выплатах будут потеряны.</div>' : ''}<p class="text-danger">Это действие необратимо.</p>`;
                pendingDeleteStudentId = studentId;
                new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
            });
        });
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
        if (pendingDeleteStudentId) {
            await deleteStudent(pendingDeleteStudentId);
            pendingDeleteStudentId = null;
            bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
        }
    });

    async function deleteStudent(studentId) {
        try {
            const formData = new FormData();
            formData.append('action', 'delete_student');
            formData.append('student_id', studentId);
            const response = await fetch('{{ route('students.action') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });
            const result = await response.json();
            if (result.status === 'success') {
                showNotificationModal('Студент успешно удалён');
                studentCache = null;
                lastFilterHash = '';
                debouncedUpdateTable();
                addedStudentsInSession.delete(parseInt(studentId));
            } else {
                showNotificationModal(result.message || 'Ошибка при удалении студента');
            }
        } catch (error) {
            showNotificationModal('Ошибка сети: ' + error.message);
        }
    }

    // ============================================================
    // ЗАГРУЗКА СТУДЕНТОВ ДЛЯ МОДАЛЬНОГО ОКНА ДОБАВЛЕНИЯ
    // ============================================================
    async function loadStudents(fio = '') {
        const tableLoading = document.getElementById('student-list-loading');
        const searchLoading = document.getElementById('student-search-loading');
        tableLoading.classList.add('show');
        searchLoading.classList.add('show');

        if (studentSearchCache[fio]) {
            renderStudentList(studentSearchCache[fio]);
            tableLoading.classList.remove('show');
            searchLoading.classList.remove('show');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'get_all_students');
        formData.append('fio', fio);
        try {
            const response = await fetch('{{ route('students.action') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });
            const result = await response.json();
            studentSearchCache[fio] = result;
            renderStudentList(result);
        } catch (error) {
            showNotificationModal('Ошибка загрузки студентов: ' + error.message);
            renderStudentList({ status: 'error', data: [], total: 0 });
        } finally {
            tableLoading.classList.remove('show');
            searchLoading.classList.remove('show');
        }
    }

    function renderStudentList(result) {
        studentListData = result;
        const tbody = document.getElementById('student-list-body');
        tbody.innerHTML = '';
        if (result.status !== 'success' || result.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Студенты не найдены.</td></tr>';
            document.getElementById('selectAllStudents').checked = false;
        } else {
            result.data.forEach(student => {
                const isAdded = addedStudentsInSession.has(student.id);
                const row = document.createElement('tr');
                row.className = `student-row-selectable ${isAdded ? 'student-row-added' : ''}`;
                row.setAttribute('data-student-id', student.id);
                row.innerHTML = `
                    <td><input type="checkbox" class="student-checkbox" value="${student.id}" ${isAdded ? 'disabled' : ''}></td>
                    <td><span class="fio-selectable" data-student-id="${student.id}" style="cursor:pointer;display:block;width:100%;padding:5px;">${student.full_name}${isAdded ? ' <i class="bi bi-check-circle-fill text-success ms-2"></i>' : ''}</span></td>
                    <td>${student.group_name}</td>
                    <td>${student.budget || '-'}</td>
                `;
                tbody.appendChild(row);
            });
            document.getElementById('selectAllStudents').onchange = function() {
                document.querySelectorAll('#student-list-body input.student-checkbox:not([disabled])').forEach(cb => cb.checked = this.checked);
            };
            document.querySelectorAll('.student-row-selectable').forEach(row => {
                row.addEventListener('click', function(event) {
                    if (event.target.tagName === 'INPUT' || event.target.closest('.bi-check-circle-fill')) return;
                    const studentId = parseInt(this.dataset.studentId);
                    if (addedStudentsInSession.has(studentId)) {
                        showNotificationModal('Студент уже добавлен');
                        return;
                    }
                    if (!selectedCategoryForAdd) {
                        showNotificationModal('Сначала выберите категорию');
                        return;
                    }
                    addSingleStudentWithCategory(studentId, this);
                });
            });
        }
    }

    async function addSingleStudentWithCategory(studentId, rowElement) {
        try {
            const formData = new FormData();
            formData.append('action', 'add_student_category');
            formData.append('student_id', studentId);
            formData.append('academic_year', selectedYearVal);
            formData.append('category_id', selectedCategoryForAdd);
            const response = await fetch('{{ route('students.action') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });
            const result = await response.json();
            if (result.status === 'success') {
                addedStudentsInSession.add(studentId);
                rowElement.classList.add('student-row-added');
                const fioSpan = rowElement.querySelector('.fio-selectable');
                if (fioSpan && !fioSpan.querySelector('.bi-check-circle-fill')) {
                    fioSpan.innerHTML += ' <i class="bi bi-check-circle-fill text-success ms-2"></i>';
                }
                const checkbox = rowElement.querySelector('.student-checkbox');
                if (checkbox) checkbox.disabled = true;
                showNotificationModal('Студент добавлен');
                studentCache = null;
                debouncedUpdateTable();
            } else {
                showNotificationModal(result.message || 'Ошибка добавления студента');
            }
        } catch (error) {
            showNotificationModal('Ошибка сети: ' + error.message);
        }
    }

    // ============================================================
    // ОБРАБОТКА СОБЫТИЙ
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {

        // Переключение учебного года
        document.getElementById('year-filter').addEventListener('change', function() {
            selectedYearVal = this.value;
            studentCache = null;
            debouncedUpdateTable();
        });

        // Фильтр по школе
        document.getElementById('school-filter').addEventListener('change', function() {
            selectedSchool = this.value;
            studentCache = null;
            debouncedUpdateTable();
        });

        // Фильтры по бюджету
        document.querySelectorAll('.budget-filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const value = this.dataset.value;
                if (activeBudgetFilters.includes(value)) {
                    activeBudgetFilters = activeBudgetFilters.filter(v => v !== value);
                    this.classList.remove('active');
                } else {
                    activeBudgetFilters.push(value);
                    this.classList.add('active');
                }
                studentCache = null;
                displayCurrentFilters();
                debouncedUpdateTable();
            });
        });

        // Поиск по ФИО
        document.getElementById('fio-search').addEventListener('input', function() {
            studentCache = null;
            debouncedUpdateTable();
        });

        // Сброс фильтров (админ)
        @if($currentUserRole === 'admin')
        document.getElementById('reset-filters').addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('resetConfirmModal'));
            modal.show();
            document.getElementById('confirmResetBtn').addEventListener('click', async function() {
                try {
                    const formData = new FormData();
                    formData.append('action', 'reset_reasons');
                    const response = await fetch('{{ route('students.action') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: formData
                    });
                    const result = await response.json();
                    if (result.status === 'success') {
                        selectedSchool = '';
                        selectedYearVal = years[0] || '';
                        document.getElementById('school-filter').value = '';
                        document.getElementById('year-filter').value = selectedYearVal;
                        activeBudgetFilters = [];
                        document.querySelectorAll('.budget-filter-btn').forEach(btn => btn.classList.remove('active'));
                        document.getElementById('fio-search').value = '';
                        studentCache = null;
                        lastFilterHash = '';
                        displayCurrentFilters();
                        debouncedUpdateTable();
                        showNotificationModal('Фильтры и данные об основаниях сброшены');
                    } else showNotificationModal(result.message || 'Ошибка при сбросе данных');
                } catch (error) {
                    showNotificationModal('Ошибка сети: ' + error.message);
                } finally {
                    modal.hide();
                }
            }, { once: true });
        });
        @endif

        // Модальное окно добавления студентов
        document.getElementById('addStudentModal').addEventListener('show.bs.modal', function() {
            selectedCategoryForAdd = document.getElementById('addCategorySelect').value;
            document.getElementById('addCategorySelect').addEventListener('change', function() {
                selectedCategoryForAdd = this.value;
            });
            document.querySelector('.student-table-container').classList.add('hidden');
            document.getElementById('studentSearch').value = '';
            document.getElementById('student-list-body').innerHTML = '';
        });

        document.getElementById('studentSearch').addEventListener('input', debounce(function() {
            const searchValue = this.value.trim();
            const container = document.querySelector('.student-table-container');
            if (searchValue !== '') {
                container.classList.remove('hidden');
                loadStudents(searchValue);
            } else {
                container.classList.add('hidden');
                document.getElementById('student-list-body').innerHTML = '';
            }
        }, 300));

        document.getElementById('addSelectedStudentsBtn').addEventListener('click', async function() {
            const checkboxes = document.querySelectorAll('#student-list-body input.student-checkbox:checked');
            if (checkboxes.length === 0) {
                showNotificationModal('Не выбрано ни одного студента');
                return;
            }
            if (!selectedCategoryForAdd) {
                showNotificationModal('Сначала выберите категорию');
                return;
            }
            const studentIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
            const formData = new FormData();
            formData.append('action', 'add_students_category');
            formData.append('student_ids', JSON.stringify(studentIds));
            formData.append('category_id', selectedCategoryForAdd);
            formData.append('academic_year', selectedYearVal);
            try {
                const response = await fetch('{{ route('students.action') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    studentIds.forEach(id => addedStudentsInSession.add(id));
                    showNotificationModal(result.message);
                    renderStudentList(studentListData);
                    studentCache = null;
                    debouncedUpdateTable();
                } else {
                    showNotificationModal(result.message || 'Ошибка при добавлении');
                }
            } catch (error) {
                showNotificationModal('Ошибка сети: ' + error.message);
            }
        });

        // Автодополнение для категорий
        function setupAutocomplete() {
            const categoryInput = document.getElementById('categoryInput');
            const suggestions = document.getElementById('autocompleteSuggestions');
            const categoryIdInput = document.getElementById('categoryId');
            const amountInput = document.getElementById('amountInput');
            const maxHint = document.getElementById('maxAmountHint');
            categoryInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                suggestions.innerHTML = '';
                suggestions.style.display = 'none';
                if (query.length < 1) return;
                const matches = categories.filter(c =>
                    (c.number + ' - ' + c.category_name).toLowerCase().includes(query) ||
                    c.category_short.toLowerCase().includes(query)
                );
                if (matches.length) {
                    matches.forEach(c => {
                        const div = document.createElement('div');
                        div.className = 'autocomplete-suggestion';
                        div.textContent = `${c.number} - ${c.category_name}`;
                        div.dataset.id = c.id;
                        div.dataset.maxAmount = c.max_amount;
                        div.addEventListener('click', () => {
                            categoryInput.value = getCategoryName(c.id);
                            categoryIdInput.value = c.id;
                            amountInput.value = c.max_amount;
                            maxHint.textContent = `Максимум: ${c.max_amount} руб.`;
                            suggestions.innerHTML = '';
                            suggestions.style.display = 'none';
                        });
                        suggestions.appendChild(div);
                    });
                    suggestions.style.display = 'block';
                }
            });
            categoryInput.addEventListener('blur', () => setTimeout(() => {
                suggestions.innerHTML = '';
                suggestions.style.display = 'none';
            }, 200));
            categoryInput.addEventListener('change', () => {
                if (!categoryIdInput.value && categoryInput.value) {
                    categoryIdInput.value = '';
                    amountInput.value = '';
                    maxHint.textContent = '';
                }
            });
        }
        setupAutocomplete();

        // Сохранение категории (выплаты)
        document.getElementById('saveCategoryBtn').addEventListener('click', async function() {
            if (!currentCell) { showNotificationModal('Ошибка: не выбрана ячейка'); return; }
            const studentId = currentCell.dataset.student;
            const month = currentCell.dataset.month;
            const categoryId = document.getElementById('categoryId').value;
            const amount = parseFloat(document.getElementById('amountInput').value);
            if (!categoryId) { showNotificationModal('Выберите категорию'); return; }
            if (isNaN(amount) || amount < 0) { showNotificationModal('Укажите корректную сумму'); return; }
            const category = categories.find(c => c.id == categoryId);
            if (category && category.max_amount > 0 && amount > parseFloat(category.max_amount)) {
                if (!confirm(`Сумма (${amount} руб.) превышает максимальную для категории (${category.max_amount} руб.). Всё равно сохранить?`)) return;
            }
            try {
                const formData = new FormData();
                formData.append('action', 'save_reason');
                formData.append('student_id', studentId);
                formData.append('month', month);
                formData.append('category_id', categoryId);
                formData.append('amount', amount);
                formData.append('academic_year', selectedYearVal);
                const response = await fetch('{{ route('students.action') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    showNotificationModal('Основание сохранено');
                    bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
                    studentCache = null;
                    debouncedUpdateTable();
                } else {
                    showNotificationModal(result.message || 'Ошибка сохранения');
                }
            } catch (error) {
                showNotificationModal('Ошибка сети: ' + error.message);
            }
        });

        document.getElementById('clearCategoryBtn').addEventListener('click', async function() {
            if (!currentCell) { showNotificationModal('Ошибка: не выбрана ячейка'); return; }
            const studentId = currentCell.dataset.student;
            const month = currentCell.dataset.month;
            if (!confirm('Удалить основание для этого месяца?')) return;
            try {
                const formData = new FormData();
                formData.append('action', 'remove_reason');
                formData.append('student_id', studentId);
                formData.append('month', month);
                formData.append('academic_year', selectedYearVal);
                const response = await fetch('{{ route('students.action') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    showNotificationModal('Основание удалено');
                    bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
                    studentCache = null;
                    debouncedUpdateTable();
                } else {
                    showNotificationModal(result.message || 'Ошибка удаления');
                }
            } catch (error) {
                showNotificationModal('Ошибка сети: ' + error.message);
            }
        });

        // Генерация протокола
        document.getElementById('generateProtocolBtn').addEventListener('click', function() {
            new bootstrap.Modal(document.getElementById('generateProtocolModal')).show();
        });

        document.getElementById('generateProtocolConfirmBtn').addEventListener('click', async function() {
            const protocolNumber = document.getElementById('protocolNumber').value.trim();
            const monthNum = parseInt(document.getElementById('protocolMonth').value, 10);
            const day = parseInt(document.getElementById('protocolDay').value, 10) || 1;
            if (!protocolNumber) { showNotificationModal('Введите номер протокола'); return; }
            const year = selectedYearVal;
            const school = selectedSchool || (currentUserRole === 'admin' ? '' : currentUserSchool);
            if (!year) { showNotificationModal('Учебный год не выбран'); return; }
            const formData = new FormData();
            formData.append('action', 'generate_protocol');
            formData.append('protocol_number', protocolNumber);
            formData.append('month', monthNum);
            formData.append('day', day);
            formData.append('academic_year', year);
            formData.append('school_code', school);
            try {
                const response = await fetch('{{ route('students.action') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success' && result.file) {
                    const link = document.createElement('a');
                    link.href = `data:${result.contentType};base64,${result.file}`;
                    link.download = result.filename;
                    link.click();
                    showNotificationModal('Протокол успешно сгенерирован');
                } else {
                    showNotificationModal(result.message || 'Ошибка при генерации протокола');
                }
            } catch (error) {
                showNotificationModal('Ошибка сети: ' + error.message);
            } finally {
                bootstrap.Modal.getInstance(document.getElementById('generateProtocolModal')).hide();
            }
        });

        // Настройка количества выплат
        const payoutModal = document.getElementById('payoutModal');
        const payoutInput = document.getElementById('payoutCount');
        const payoutError = document.getElementById('payoutError');
        const payoutResult = document.getElementById('payoutResult');
        const payoutResultText = document.getElementById('payoutResultText');
        const confirmPayoutBtn = document.getElementById('confirmPayoutBtn');

        payoutModal.addEventListener('show.bs.modal', function() {
            payoutInput.value = payoutCount;
            payoutError.style.display = 'none';
            payoutResult.classList.remove('show');
        });

        confirmPayoutBtn.addEventListener('click', async function() {
            const value = payoutInput.value.trim();
            if (value === '') { payoutError.style.display = 'block'; payoutResult.classList.remove('show'); return; }
            const num = Number(value);
            if (!Number.isInteger(num) || num < 2 || num > 4) { payoutError.style.display = 'block'; payoutResult.classList.remove('show'); return; }
            const formData = new FormData();
            formData.append('action', 'update_payout_count');
            formData.append('payout_count', num);
            try {
                const response = await fetch('{{ route('students.action') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    payoutCount = num;
                    document.getElementById('payoutCountDisplay').textContent = num;
                    payoutError.style.display = 'none';
                    const word = {2:'два раза в год',3:'три раза в год',4:'четыре раза в год'}[num];
                    payoutResultText.textContent = `✅ Утверждено: ${num} (${word})`;
                    payoutResult.classList.add('show');
                    showNotificationModal(`Количество выплат изменено на ${num}`);
                    bootstrap.Modal.getInstance(payoutModal).hide();
                } else {
                    showNotificationModal(result.message || 'Ошибка при сохранении');
                }
            } catch (error) {
                showNotificationModal('Ошибка сети: ' + error.message);
            }
        });

        payoutInput.addEventListener('input', function() {
            payoutError.style.display = 'none';
        });
        payoutInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') confirmPayoutBtn.click();
        });

        // Добавление учебного года
        @if(in_array($currentUserRole, ['admin', 'director', 'member']))
        document.getElementById('saveAcademicYearBtn').addEventListener('click', async function() {
            const yearInput = document.getElementById('academicYearInput').value.trim();
            const modal = bootstrap.Modal.getInstance(document.getElementById('addAcademicYearModal'));
            if (!yearInput || !/^\d{4}\/\d{4}$/.test(yearInput)) {
                showNotificationModal('Ошибка: Укажите учебный год в формате ГГГГ/ГГГГ');
                return;
            }
            const [start, end] = yearInput.split('/');
            if (parseInt(end) !== parseInt(start) + 1) {
                showNotificationModal('Ошибка: Годы должны быть последовательными');
                return;
            }
            try {
                const formData = new FormData();
                formData.append('action', 'add_academic_year');
                formData.append('year', yearInput);
                const response = await fetch('{{ route('students.action') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    if (!years.includes(yearInput)) {
                        years.push(yearInput);
                        years.sort((a,b) => b.localeCompare(a));
                        const yearSelect = document.getElementById('year-filter');
                        yearSelect.innerHTML = '<option value="">Все годы</option>' +
                            years.map(y => `<option value="${y}">${y}</option>`).join('');
                        yearSelect.value = yearInput;
                        selectedYearVal = yearInput;
                        studentCache = null;
                        debouncedUpdateTable();
                    }
                    showNotificationModal('Учебный год добавлен');
                    modal.hide();
                } else {
                    showNotificationModal(`Ошибка: ${result.message}`);
                }
            } catch (error) {
                showNotificationModal('Ошибка сети: ' + error.message);
            }
        });
        @endif

        // Первоначальная загрузка таблицы
        displayCurrentFilters();
        debouncedUpdateTable();
    });

    // ============================================================
    // УТИЛИТЫ
    // ============================================================
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    }
</script>
@endpush