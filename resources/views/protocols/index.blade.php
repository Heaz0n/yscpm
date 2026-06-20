@extends('layouts.app')

@section('title', 'Генерация протокола заседания')

@section('content')
<div class="main-content" style="transition: margin-left 0.3s ease; padding: 1.5rem 2rem 1.5rem 0;">
    <div class="container-fluid" style="max-width: 1800px;">
        <h2 class="mb-4 fw-semibold" style="color: var(--primary);">Генерация протокола заседания</h2>

        <div class="row g-4">
            <div class="col-lg-6">
                <form method="POST" id="template-form" action="{{ route('protocols.save-template') }}">
                    @csrf
                    <div class="form-section">
                        <h5><i class="bi bi-building me-2"></i>Основные данные</h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="UNIVERSITY" class="form-label">ВУЗ</label>
                                <input type="text" class="form-control template-input" id="UNIVERSITY" name="template[UNIVERSITY]" data-placeholder="UNIVERSITY" value="{{ old('template.UNIVERSITY', $templateVars['UNIVERSITY'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="SCHOOL_CODE" class="form-label">Школа</label>
                                <select class="form-select template-input" id="SCHOOL_CODE" name="template[SCHOOL_CODE]" data-placeholder="SCHOOL_CODE" {{ $userRole !== 'admin' ? 'disabled' : '' }}>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->code }}" {{ ($templateVars['SCHOOL_CODE'] ?? $initialSchoolCode) == $school->code ? 'selected' : '' }}>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($userRole !== 'admin')
                                    <input type="hidden" name="template[SCHOOL_CODE]" value="{{ $userSchoolCode }}">
                                @endif
                                <input type="hidden" id="SCHOOL" name="template[SCHOOL]" data-placeholder="SCHOOL" value="{{ old('template.SCHOOL', $templateVars['SCHOOL'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="CITY" class="form-label">Город</label>
                                <input type="text" class="form-control template-input" id="CITY" name="template[CITY]" data-placeholder="CITY" value="{{ old('template.CITY', $templateVars['CITY'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="YEAR" class="form-label">Учебный год</label>
                                <select class="form-select" id="YEAR" name="template[YEAR]">
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year }}" {{ ($templateVars['YEAR'] ?? $defaultAcademicYear) == $year ? 'selected' : '' }}>{{ str_replace('/', '–', $year) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="AGENDA" class="form-label">Повестка дня</label>
                                <input type="text" class="form-control template-input" id="AGENDA" name="template[AGENDA]" data-placeholder="AGENDA" value="{{ old('template.AGENDA', $templateVars['AGENDA'] ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5><i class="bi bi-people me-2"></i>Состав комиссии</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="CHAIRPERSON" class="form-label">Председатель</label>
                                <input type="text" class="form-control template-input" id="CHAIRPERSON" name="template[CHAIRPERSON]" data-placeholder="CHAIRPERSON" value="{{ old('template.CHAIRPERSON', $templateVars['CHAIRPERSON'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="CHAIR_DEGREE" class="form-label">Учёная степень председателя</label>
                                <input type="text" class="form-control template-input" id="CHAIR_DEGREE" name="template[CHAIR_DEGREE]" data-placeholder="CHAIR_DEGREE" value="{{ old('template.CHAIR_DEGREE', $templateVars['CHAIR_DEGREE'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="SECRETARY_DEGREE" class="form-label">Учёная степень секретаря</label>
                                <input type="text" class="form-control template-input" id="SECRETARY_DEGREE" name="template[SECRETARY_DEGREE]" data-placeholder="SECRETARY_DEGREE" value="{{ old('template.SECRETARY_DEGREE', $templateVars['SECRETARY_DEGREE'] ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label for="MEMBERS" class="form-label">Члены комиссии</label>
                                <textarea class="form-control template-input" id="MEMBERS" name="template[MEMBERS]" data-placeholder="MEMBERS" rows="6">{{ old('template.MEMBERS', $templateVars['MEMBERS'] ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="SECRETARY" class="form-label">Секретарь</label>
                                <input type="text" class="form-control template-input" id="SECRETARY" name="template[SECRETARY]" data-placeholder="SECRETARY" value="{{ old('template.SECRETARY', $templateVars['SECRETARY'] ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5><i class="bi bi-pencil-square me-2"></i>Текст протокола</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="LISTENED" class="form-label">Слушали</label>
                                <textarea class="form-control template-input" id="LISTENED" name="template[LISTENED]" data-placeholder="LISTENED" rows="4">{{ old('template.LISTENED', $templateVars['LISTENED'] ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="DECISION" class="form-label">Решили</label>
                                <textarea class="form-control template-input" id="DECISION" name="template[DECISION]" data-placeholder="DECISION" rows="4">{{ old('template.DECISION', $templateVars['DECISION'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5><i class="bi bi-pen me-2"></i>Подписи</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="SIGN_CHAIR" class="form-label">Подпись председателя</label>
                                <input type="text" class="form-control template-input" id="SIGN_CHAIR" name="template[SIGN_CHAIR]" data-placeholder="SIGN_CHAIR" value="{{ old('template.SIGN_CHAIR', $templateVars['SIGN_CHAIR'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="SIGN_SECRETARY" class="form-label">Подпись секретаря</label>
                                <input type="text" class="form-control template-input" id="SIGN_SECRETARY" name="template[SIGN_SECRETARY]" data-placeholder="SIGN_SECRETARY" value="{{ old('template.SIGN_SECRETARY', $templateVars['SIGN_SECRETARY'] ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="action" value="save_template">
                    <div class="btn-group-action">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Сохранить шаблон</button>
                    </div>
                </form>

                <div class="form-section mt-4">
                    <h5><i class="bi bi-file-earmark-arrow-down me-2"></i>Генерация документа</h5>
                    <form method="POST" id="generate-protocol-form" action="{{ route('protocols.generate') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="format" class="form-label">Формат</label>
                                <select class="form-select" id="format" name="format">
                                    <option value="word">Word (.docx)</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="filename" class="form-label">Имя файла</label>
                                <input type="text" class="form-control" id="filename" name="filename" placeholder="protocol_3_2026">
                            </div>
                        </div>
                        <input type="hidden" name="day" id="form-day" value="{{ old('day', $templateVars['DAY'] ?? '-') }}">
                        <input type="hidden" name="month" id="form-month" value="{{ old('month', $templateVars['MONTH'] ?? '-') }}">
                        <input type="hidden" name="year" id="form-year" value="{{ old('year', $templateVars['YEAR'] ?? $defaultAcademicYear) }}">
                        <input type="hidden" name="school_code" value="{{ $initialSchoolCode }}">
                        <input type="hidden" name="action" value="generate_protocol">
                        <button type="submit" class="btn btn-success w-100 mt-3"><i class="bi bi-file-earmark-check me-2"></i>Сгенерировать протокол</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="preview-wrapper">
                    <h5 class="mb-3"><i class="bi bi-eye me-2"></i>Предпросмотр документа</h5>
                    <div class="preview-container preview">
                        <h1>ПРОТОКОЛ № <span data-placeholder="PROTOCOL_NUMBER">-</span></h1>
                        <h2>Заседания стипендиальной комиссии</h2>
                        <h2><span data-placeholder="SCHOOL">{{ old('template.SCHOOL', $templateVars['SCHOOL'] ?? '{SCHOOL}') }}</span></h2>
                        <h2><span data-placeholder="UNIVERSITY">{{ old('template.UNIVERSITY', $templateVars['UNIVERSITY'] ?? '{UNIVERSITY}') }}</span></h2>
                        <div class="date-city">
                            <span class="date-part">«– – {{ \App\Http\Controllers\ProtocolController::extractYearStatic($templateVars['YEAR'] ?? $defaultAcademicYear, 'second') }} г.»</span>
                            <span class="city-part">г. <span data-placeholder="CITY">{{ old('template.CITY', $templateVars['CITY'] ?? '{CITY}') }}</span></span>
                        </div>
                        <p class="section-title">Председатель комиссии:</p>
                        <p><span data-placeholder="CHAIRPERSON">{{ old('template.CHAIRPERSON', $templateVars['CHAIRPERSON'] ?? '{CHAIRPERSON}') }}</span></p>
                        <p class="section-title">Члены комиссии:</p>
                        <p class="members"><span data-placeholder="MEMBERS">{{ nl2br(old('template.MEMBERS', $templateVars['MEMBERS'] ?? '{MEMBERS}')) }}</span></p>
                        <p class="section-title">Секретарь комиссии:</p>
                        <p><span data-placeholder="SECRETARY">{{ old('template.SECRETARY', $templateVars['SECRETARY'] ?? '{SECRETARY}') }}</span></p>
                        <p class="section-title">Повестка дня:</p>
                        <p><span data-placeholder="AGENDA">{{ old('template.AGENDA', $templateVars['AGENDA'] ?? '{AGENDA}') }}</span></p>
                        <p class="section-title">Слушали:</p>
                        <p><span data-placeholder="LISTENED">{{ nl2br(old('template.LISTENED', $templateVars['LISTENED'] ?? '{LISTENED}')) }}</span></p>
                        <p class="section-title">Решили:</p>
                        <p><span data-placeholder="DECISION">{{ nl2br(old('template.DECISION', $templateVars['DECISION'] ?? '')) }}</span></p>

                        <p class="section-title">Студенты (РФ):</p>
                        <table class="rf-table">
                            <thead><tr><th>№</th><th>ФИО</th><th>Бюджет</th><th>Группа</th><th>Основание</th><th>Сумма</th></tr></thead>
                            <tbody>
                                @php
                                    $rfStudents = array_filter($studentCategories, function($s) {
                                        $budget = isset($s['budget']) ? strtoupper(trim($s['budget'])) : '';
                                        return $budget !== 'ХМАО' && $budget !== 'XMAO' && $budget !== 'HMAC' && $budget !== 'ХМАО-ЮГРА' && $budget !== 'ЮГРА';
                                    });
                                @endphp
                                @if(empty($rfStudents))
                                    <tr><td class="number">1</td><td>Нет данных</td><td class="budget">-</td><td class="group">-</td><td>Нет студентов</td><td class="amount">0</td></tr>
                                @else
                                    @foreach(array_slice($rfStudents, 0, 30) as $index => $student)
                                        @php
                                            $formattedAmount = (new \App\Http\Controllers\ProtocolController)->formatAmount($student['amount'], $student['amount_condition'], $student['max_amount']);
                                        @endphp
                                        <tr>
                                            <td class="number">{{ $index+1 }}</td>
                                            <td>{{ $student['full_name'] }}</td>
                                            <td class="budget">{{ $student['budget'] ?: 'РФ' }}</td>
                                            <td class="group">{{ $student['group_name'] }}</td>
                                            <td>{{ $student['category_short'] ?? 'Не указано' }}</td>
                                            <td class="amount">{{ $formattedAmount }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>

                        <p class="section-title">Студенты (ХМАО):</p>
                        <table class="hmao-table">
                            <thead><tr><th>№</th><th>ФИО</th><th>Бюджет</th><th>Группа</th><th>Основание</th><th>Сумма</th></tr></thead>
                            <tbody>
                                @php
                                    $hmaoStudents = array_filter($studentCategories, function($s) {
                                        $budget = isset($s['budget']) ? strtoupper(trim($s['budget'])) : '';
                                        return $budget === 'ХМАО' || $budget === 'XMAO' || $budget === 'HMAC' || $budget === 'ХМАО-ЮГРА' || $budget === 'ЮГРА';
                                    });
                                @endphp
                                @if(empty($hmaoStudents))
                                    <tr><td class="number">1</td><td>Нет данных</td><td class="budget">-</td><td class="group">-</td><td>Нет студентов</td><td class="amount">0</td></tr>
                                @else
                                    @foreach(array_slice($hmaoStudents, 0, 3) as $index => $student)
                                        @php
                                            $formattedAmount = (new \App\Http\Controllers\ProtocolController)->formatAmount($student['amount'], $student['amount_condition'], $student['max_amount']);
                                        @endphp
                                        <tr>
                                            <td class="number">{{ $index+1 }}</td>
                                            <td>{{ $student['full_name'] }}</td>
                                            <td class="budget">{{ $student['budget'] ?: 'ХМАО' }}</td>
                                            <td class="group">{{ $student['group_name'] }}</td>
                                            <td>{{ $student['category_short'] ?? 'Не указано' }}</td>
                                            <td class="amount">{{ $formattedAmount }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>

                        <div class="signatures">
                            <p>Руководитель инженерной школы цифровых технологий</p>
                            <div class="signature">
                                <span>{{ old('template.CHAIR_DEGREE', $templateVars['CHAIR_DEGREE'] ?? '{CHAIR_DEGREE}') }}</span>
                                <span class="signature-line"></span>
                                <span data-placeholder="SIGN_CHAIR">{{ old('template.SIGN_CHAIR', $templateVars['SIGN_CHAIR'] ?? '{SIGN_CHAIR}') }}</span>
                            </div>
                            <p>Заместитель руководителя инженерной школы цифровых технологий по воспитательной работе</p>
                            <div class="signature">
                                <span>{{ old('template.SECRETARY_DEGREE', $templateVars['SECRETARY_DEGREE'] ?? '{SECRETARY_DEGREE}') }}</span>
                                <span class="signature-line"></span>
                                <span data-placeholder="SIGN_SECRETARY">{{ old('template.SIGN_SECRETARY', $templateVars['SIGN_SECRETARY'] ?? '{SIGN_SECRETARY}') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary: #2c3e50;
        --secondary: #34495e;
        --accent: #3498db;
        --light-bg: #ecf0f1;
    }
    .form-section {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .form-section h5 {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 1.2rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    .preview-wrapper {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        padding: 1.5rem;
        position: sticky;
        top: 1rem;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
    }
    .preview-container {
        background: #ffffff;
        border: 1px solid #dee2e6;
        width: 100%;
        min-height: 297mm;
        padding: 20mm 15mm;
        box-shadow: inset 0 0 0 1px #e9ecef;
        font-family: 'Times New Roman', serif;
        font-size: 12pt;
        line-height: 1.2;
        text-align: justify;
        margin: 0 auto;
    }
    .preview h1, .preview h2 {
        font-size: 14pt;
        font-weight: bold;
        text-align: center;
        margin: 0 0 0.3em 0;
    }
    .preview .date-city {
        display: flex;
        justify-content: space-between;
        margin: 1em 0;
    }
    .preview .section-title {
        font-weight: bold;
        margin-top: 1em;
        margin-bottom: 0.2em;
    }
    .preview table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000;
        margin: 1em 0;
        font-size: 11pt;
    }
    .preview th, .preview td {
        border: 1px solid #000;
        padding: 6px;
        vertical-align: middle;
    }
    .preview th {
        background: #f1f3f5;
        font-weight: 600;
    }
    .preview .signature-line {
        display: inline-block;
        width: 60mm;
        border-bottom: 1px solid #000;
        margin: 0 5px;
    }
    .btn-group-action {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    .btn-group-action .btn {
        flex: 1;
    }
    @media (max-width: 992px) {
        .preview-wrapper {
            position: static;
            max-height: none;
            margin-top: 1.5rem;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 1rem !important;
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        const monthMap = @json($monthMap);
        let studentsData = @json($studentCategories);
        let selectedMonth = '{{ old('month', $initialMonthName) }}';
        let selectedYear = '{{ old('year', $initialYear) }}';
        let selectedDay = '{{ old('day', $initialDay) }}';
        let currentSchoolCode = $('#SCHOOL_CODE').val() || '{{ $initialSchoolCode }}';
        let userRole = '{{ $userRole }}';

        function updateSchoolInPreview(schoolName) {
            $(`.preview [data-placeholder="SCHOOL"]`).text(schoolName);
        }
        function updateInitialSchoolName() {
            const initialSelectedOption = $('#SCHOOL_CODE option:selected');
            const initialSchoolName = initialSelectedOption.text();
            $('#SCHOOL').val(initialSchoolName);
            updateSchoolInPreview(initialSchoolName);
        }

        function extractYear(academicYear, part = 'second') {
            if (academicYear.match(/^\d{4}\/\d{4}$/)) {
                const years = academicYear.split('/');
                return part === 'first' ? years[0] : years[1];
            }
            return academicYear;
        }

        function showNotification(message, type) {
            const $notification = $(`
                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0 position-fixed top-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1100;">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `);
            $('body').append($notification);
            const toast = new bootstrap.Toast($notification[0], { delay: 5000 });
            toast.show();
            $notification.on('hidden.bs.toast', function() { $(this).remove(); });
        }

        function updatePreview() {
            $('.template-input').each(function() {
                const placeholder = $(this).data('placeholder');
                const value = $(this).val();
                if (placeholder === 'MEMBERS' || placeholder === 'LISTENED' || placeholder === 'DECISION') {
                    $(`.preview [data-placeholder="${placeholder}"]`).html(value.replace(/\n/g, '<br>'));
                } else {
                    $(`.preview [data-placeholder="${placeholder}"]`).text(value);
                }
            });
            const year = selectedYear || '{{ $defaultAcademicYear }}';
            const filterYear = extractYear(year, 'second');
            const city = $('#CITY').val().trim() || '{{ old('template.CITY', $templateVars['CITY'] ?? '') }}';
            $('.preview .date-part').html(`«– – ${filterYear} г.»`);
            $('.preview .city-part').html(`г. <span data-placeholder="CITY">${city}</span>`);
            $('.preview [data-placeholder="PROTOCOL_NUMBER"]').text('-');

            $.ajax({
                url: '{{ route("protocols.get-students") }}',
                method: 'GET',
                data: {
                    search: '',
                    month: selectedMonth,
                    year: year,
                    school_code: currentSchoolCode,
                    _t: new Date().getTime()
                },
                dataType: 'json',
                success: function(data) {
                    studentsData = data || [];
                    updateStudentTables(studentsData);
                },
                error: function() {
                    updateStudentTables([]);
                }
            });
        }

        function syncTemplateVars(vars) {
            for (const [placeholder, value] of Object.entries(vars)) {
                const $input = $(`#${placeholder}`);
                if ($input.length) {
                    $input.val(value);
                }
                if (placeholder === 'MEMBERS' || placeholder === 'LISTENED' || placeholder === 'DECISION') {
                    $(`.preview [data-placeholder="${placeholder}"]`).html(value.replace(/\n/g, '<br>'));
                } else if (placeholder === 'PROTOCOL_NUMBER') {
                    $('.preview [data-placeholder="PROTOCOL_NUMBER"]').text('-');
                } else {
                    $(`.preview [data-placeholder="${placeholder}"]`).text(value);
                }
            }
            selectedDay = vars['DAY'] || '-';
            selectedMonth = vars['MONTH'] || Object.keys(monthMap)[0];
            selectedYear = vars['YEAR'] || '{{ $defaultAcademicYear }}';
            currentSchoolCode = vars['SCHOOL_CODE'] || '{{ $initialSchoolCode }}';
            $('#form-day').val(selectedDay);
            $('#form-month').val(selectedMonth);
            $('#form-year').val(selectedYear);
            updatePreview();
        }

        function loadTemplateVars(schoolCode, academicYear) {
            $.ajax({
                url: '{{ route("protocols.get-template-vars") }}',
                method: 'GET',
                data: {
                    school_code: schoolCode,
                    academic_year: academicYear
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.template_vars) {
                        syncTemplateVars(response.template_vars);
                        currentSchoolCode = schoolCode;
                        showNotification('Шаблон загружен', 'success');
                    } else {
                        showNotification('Ошибка загрузки шаблона', 'error');
                    }
                },
                error: function() {
                    showNotification('Ошибка загрузки шаблона', 'error');
                }
            });
        }

        function updateStudentTables(students) {
            const rfStudents = students.filter(function(s) {
                var budget = s.budget ? s.budget.toUpperCase().trim() : '';
                return budget !== 'ХМАО' && budget !== 'XMAO' && budget !== 'HMAC' && 
                       budget !== 'ХМАО-ЮГРА' && budget !== 'ЮГРА';
            });
            const hmaoStudents = students.filter(function(s) {
                var budget = s.budget ? s.budget.toUpperCase().trim() : '';
                return budget === 'ХМАО' || budget === 'XMAO' || budget === 'HMAC' || 
                       budget === 'ХМАО-ЮГРА' || budget === 'ЮГРА';
            });
            const $rfTableBody = $('.preview .rf-table tbody');
            $rfTableBody.empty();
            if (rfStudents.length === 0) {
                $rfTableBody.append('<tr><td class="number">1</td><td>Нет данных</td><td class="budget">-</td><td class="group">-</td><td>Нет студентов</td><td class="amount">0</td></tr>');
            } else {
                rfStudents.slice(0, 30).forEach(function(student, index) {
                    const reason = student.category_short || 'Не указано';
                    const budget = student.budget && student.budget.trim() ? student.budget : 'РФ';
                    let amountDisplay;
                    if (student.amount_condition === 'expense_limit') {
                        amountDisplay = `В объёме затрат, но не более ${new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2 }).format(student.max_amount)} руб.`;
                    } else {
                        amountDisplay = new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2 }).format(student.amount);
                    }
                    $rfTableBody.append(`
                        <tr>
                            <td class="number">${index+1}</td>
                            <td>${$('<div/>').text(student.full_name).html()}</td>
                            <td class="budget">${$('<div/>').text(budget).html()}</td>
                            <td class="group">${$('<div/>').text(student.group_name).html()}</td>
                            <td>${$('<div/>').text(reason).html()}</td>
                            <td class="amount">${$('<div/>').text(amountDisplay).html()}</td>
                        </tr>
                    `);
                });
            }
            const $hmaoTableBody = $('.preview .hmao-table tbody');
            $hmaoTableBody.empty();
            if (hmaoStudents.length === 0) {
                $hmaoTableBody.append('<tr><td class="number">1</td><td>Нет данных</td><td class="budget">-</td><td class="group">-</td><td>Нет студентов</td><td class="amount">0</td></tr>');
            } else {
                hmaoStudents.slice(0, 3).forEach(function(student, index) {
                    const reason = student.category_short || 'Не указано';
                    const budget = student.budget && student.budget.trim() ? student.budget : 'ХМАО';
                    let amountDisplay;
                    if (student.amount_condition === 'expense_limit') {
                        amountDisplay = `В объёме затрат, но не более ${new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2 }).format(student.max_amount)} руб.`;
                    } else {
                        amountDisplay = new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2 }).format(student.amount);
                    }
                    $hmaoTableBody.append(`
                        <tr>
                            <td class="number">${index+1}</td>
                            <td>${$('<div/>').text(student.full_name).html()}</td>
                            <td class="budget">${$('<div/>').text(budget).html()}</td>
                            <td class="group">${$('<div/>').text(student.group_name).html()}</td>
                            <td>${$('<div/>').text(reason).html()}</td>
                            <td class="amount">${$('<div/>').text(amountDisplay).html()}</td>
                        </tr>
                    `);
                });
            }
        }

        $('#SCHOOL_CODE').on('change', function() {
            if (userRole === 'admin') {
                const schoolCode = $(this).val();
                const schoolName = $(this).find('option:selected').text();
                $('#SCHOOL').val(schoolName);
                $(`.preview [data-placeholder="SCHOOL"]`).text(schoolName);
                currentSchoolCode = schoolCode;
                loadTemplateVars(schoolCode, selectedYear);
                showNotification('Школа изменена на: ' + schoolName, 'success');
            }
        });

        $('#YEAR').on('change', function() {
            const schoolCode = $('#SCHOOL_CODE').val();
            const academicYear = $(this).val();
            loadTemplateVars(schoolCode, academicYear);
        });

        $('#CITY').on('input change', function() { updatePreview(); });

        function updateFilenameExtension() {
            const format = $('#format').val();
            const $filenameInput = $('#filename');
            let filename = $filenameInput.val().trim();
            const year = extractYear(selectedYear, 'second');
            filename = filename.replace(/\.(docx|pdf)$/i, '');
            if (filename === '') filename = `protocol_3_${year}`;
            $filenameInput.val(filename);
            $filenameInput.attr('placeholder', `protocol_3_${year}`);
        }

        $('#format').on('change', function() { updateFilenameExtension(); updatePreview(); });
        $('#filename').on('input', function() {
            let value = $(this).val().trim();
            value = value.replace(/\.(docx|pdf)$/i, '');
            const year = extractYear(selectedYear, 'second');
            if (value === '') value = `protocol_3_${year}`;
            $(this).val(value);
        });

        $('#generate-protocol-form').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const format = $('#format').val();
            const extension = format === 'word' ? '.docx' : '.pdf';
            const $filenameInput = $('#filename');
            let filename = $filenameInput.val().trim();
            filename = filename.replace(/\.(docx|pdf)$/i, '');
            $filenameInput.val(filename + extension);
            const formData = $form.serializeArray();
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    showNotification(response.message, response.status);
                    if (response.file) {
                        const link = document.createElement('a');
                        link.href = `data:${response.contentType};base64,${response.file}`;
                        link.download = response.filename;
                        link.click();
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON || { message: 'Ошибка сервера', status: 'error' };
                    showNotification(response.message, response.status);
                },
                complete: function() {
                    $filenameInput.val(filename);
                }
            });
        });

        $('#template-form').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const formData = $form.serializeArray();
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        loadTemplateVars($('#SCHOOL_CODE').val(), $('#YEAR').val());
                    } else {
                        showNotification(response.message || 'Ошибка сохранения шаблона', 'error');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON || { message: 'Ошибка сервера', status: 'error' };
                    showNotification(response.message, response.status);
                }
            });
        });

        $('.template-input').on('change', function() { $('#template-form').submit(); });
        $('.template-input').on('input change', function() {
            const placeholder = $(this).data('placeholder');
            const value = $(this).val();
            const maxLength = (placeholder === 'MEMBERS') ? 16000 : 1000;
            if (value.length > maxLength) {
                showNotification(`Максимум ${maxLength} символов`, 'error');
                $(this).val(value.substring(0, maxLength));
            }
            if (placeholder === 'LISTENED') {
                const lines = value.split('\n');
                if (lines.length > 50) {
                    showNotification('Слишком много строк в поле "Слушали"', 'error');
                    $(this).val(lines.slice(0, 50).join('\n'));
                }
            }
            updatePreview();
        });

        function updateMainContentMargin() {
            const sidebar = document.getElementById('sidebarContainer');
            const mainContent = document.querySelector('.main-content');
            if (!sidebar || !mainContent) return;
            const isCollapsed = sidebar.classList.contains('collapsed');
            const rootStyle = getComputedStyle(document.documentElement);
            const sidebarWidth = isCollapsed 
                ? rootStyle.getPropertyValue('--sidebar-width-collapsed').trim() 
                : rootStyle.getPropertyValue('--sidebar-width').trim();
            mainContent.style.marginLeft = `calc(${sidebarWidth} + 2rem)`;
        }

        updateMainContentMargin();
        const sidebarContainer = document.getElementById('sidebarContainer');
        if (sidebarContainer) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        updateMainContentMargin();
                    }
                });
            });
            observer.observe(sidebarContainer, { attributes: true });
        }
        window.addEventListener('resize', function() { updateMainContentMargin(); });

        updateInitialSchoolName();
        updateFilenameExtension();
        updatePreview();
    });
</script>
@endsection