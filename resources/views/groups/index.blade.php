@extends('layouts.app')

@section('title', 'Группы и студенты')

@section('content')
<style>
    /* Переопределяем отступ основного контента, чтобы прижать всё к левому краю */
    .main-content {
        padding-left: 0 !important;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Roboto', sans-serif; color: #333; line-height: 1.6; background-color: #f5f6f5; }
    .container { margin: 20px 0; padding: 20px 20px 20px 0; }
    .content-wrapper { display: flex; gap: 20px; margin: 0; }
    .left-column { flex: 1; max-width: 700px; }
    .right-column { width: 600px; }
    h1 { font-size: 2.2em; font-weight: 700; color: #003087; margin-bottom: 30px; }
    .filter-form { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; flex-direction: column; gap: 15px; width: 100%; }
    .filter-row { display: flex; flex-direction: column; gap: 10px; width: 100%; }
    .filter-select { padding: 8px; font-size: 14px; border-radius: 4px; border: 1px solid #ccc; background: #f9f9f9; cursor: pointer; min-width: 200px; flex: 1; }
    .school-info { padding: 10px; background: #f0f0f0; border-radius: 4px; font-size: 14px; width: 100%; }
    .add-button { background: #2e7d32; color: white; padding: 8px 12px; border: none; border-radius: 4px; font-size: 14px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
    .add-button:hover { background: #1b5e20; transform: translateY(-1px); }
    .groups-list { margin-top: 10px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .students-list { background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-top: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .groups-list table, .students-list table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .groups-list th, .groups-list td, .students-list th, .students-list td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
    .groups-list th, .students-list th { background: #f9f9f9; font-weight: 500; text-transform: uppercase; }
    .groups-list tr:hover, .students-list tr:hover { background: #f1f5f9; }
    .edit-button { background: #fbc02d; color: white; padding: 6px; border: none; border-radius: 4px; cursor: pointer; }
    .delete-button { background: #d32f2f; color: white; padding: 6px; border: none; border-radius: 4px; cursor: pointer; }
    .delete-all-button { background: #d32f2f; color: white; flex: 1; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; }
    .upload-button { background: #0288d1; color: white; flex: 1; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; }
    .actions { display: flex; gap: 8px; }
    .hidden { display: none; }
    .modal { display: none; position: fixed; z-index: 1000; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
    .modal-content { background: white; margin: 10% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; position: relative; }
    .close { color: #666; position: absolute; top: 10px; right: 15px; font-size: 24px; cursor: pointer; }
    .close:hover { color: #d32f2f; }
    form label { display: block; margin: 10px 0 5px; font-weight: 500; }
    form input, form textarea, form select { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; background: #f9f9f9; }
    .notification-container { position: fixed; top: 20px; right: 20px; z-index: 1100; max-width: 380px; }
    .notification { padding: 12px 15px; margin-bottom: 10px; border-radius: 4px; color: white; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
    .notification.success { background: #2e7d32; }
    .notification.error { background: #d32f2f; }
    .clickable-name { cursor: pointer; color: #0288d1; font-weight: 500; }
    .clickable-name:hover { color: #005b9f; text-decoration: underline; }
    .loading-spinner { display: none; margin: 20px; }
    .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .student-checkbox { cursor: pointer; transform: scale(1.2); }
    #select-all-checkbox { cursor: pointer; transform: scale(1.2); }
    .save-button { background: #0288d1; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; }
    .view-button { background: #4CAF50; color: white; padding: 6px; border: none; border-radius: 4px; cursor: pointer; }
    .download-button { background: #2196F3; color: white; padding: 6px; border: none; border-radius: 4px; cursor: pointer; }
    .clickable-student-name { cursor: pointer; color: #0288d1; font-weight: 500; }
    .clickable-student-name:hover { color: #005b9f; text-decoration: underline; }
    @media (max-width: 1024px) { .content-wrapper { flex-direction: column; } .left-column { max-width: none; } .right-column { width: 100%; margin-top: 20px; } }
    @media (max-width: 768px) { .main-content { padding-left: 10px !important; } .container { padding: 15px; } .filter-row { flex-direction: column; } .filter-select { min-width: 100%; } .groups-list table, .students-list table { font-size: 12px; } }
</style>

<div class="notification-container">
    @if(session('notification'))
        <div class="notification {{ session('notification')['type'] }}">
            <i class="fas {{ session('notification')['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
            <span>{{ session('notification')['message'] }}</span>
            <span class="close-btn" onclick="this.parentElement.remove()">×</span>
        </div>
        @php session()->forget('notification') @endphp
    @endif
</div>

<div class="main-content">
    <div class="container">
        <h1>Группы и студенты</h1>
    </div>

    <div class="content-wrapper">
        <div class="left-column">
            <form class="filter-form" id="filterForm">
                <div class="filter-section">
                    <div class="filter-row">
                        @if($is_admin)
                            <select id="school-filter" class="filter-select">
                                <option value="">Выберите школу</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->code }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                            <select id="direction-filter" class="filter-select hidden">
                                <option value="">Выберите направление</option>
                            </select>
                        @else
                            <div class="school-info">
                                <strong>Школа:</strong> {{ $user_school_info->name ?? $user_school_code }}
                            </div>
                            <select id="direction-filter" class="filter-select">
                                <option value="">Выберите направление</option>
                            </select>
                            <input type="hidden" id="school-filter" value="{{ $user_school_code }}">
                        @endif
                        <button id="add-group-button" class="add-button hidden" data-modal="add-group-modal">
                            <i class="fas fa-circle-plus"></i> Добавить группу
                        </button>
                    </div>
                </div>
            </form>

            <div id="loading-spinner" class="loading-spinner"><div class="spinner"></div></div>

            <div id="groups-list" class="groups-list">
                <table>
                    <thead>
                        <tr><th>Наименование группы</th><th>Примечания</th><th>Действия</th></tr>
                    </thead>
                    <tbody id="groups-table-body">
                        @foreach($groups as $group)
                            <tr data-group-id="{{ $group['id'] }}" data-direction-id="{{ $group['direction_id'] }}" data-vsh-code="{{ $group['vsh_code'] }}">
                                <td><span class="clickable-name" data-group='@json($group)'>{{ $group['group_name'] ?? 'Не указано' }}</span></td>
                                <td>{{ $group['notes'] ?? '' }}</td>
                                <td>
                                    <div class="actions">
                                        <button class="edit-button" data-action="edit-group" data-id="{{ $group['id'] }}" data-direction="{{ $group['direction_id'] }}" data-name="{{ $group['group_name'] }}" data-notes="{{ $group['notes'] ?? '' }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button class="delete-button" data-action="delete-group" data-id="{{ $group['id'] }}">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="right-column">
            <div id="students-list" class="students-list">
                <h3>Список студентов <span id="selected-group-name"></span></h3>
                <div class="button-container">
                    <button id="add-student-button" class="add-button hidden" data-modal="add-student-modal">
                        <i class="fas fa-circle-plus"></i> Добавить студента
                    </button>
                    <button id="delete-all-students-button" class="delete-all-button hidden" data-action="delete-all-students">
                        <i class="fas fa-trash-can"></i> Удалить всех
                    </button>
                    <button id="upload-students-button" class="upload-button hidden" data-modal="upload-students-modal">
                        <i class="fas fa-upload"></i> Загрузить из Excel
                    </button>
                    <button id="mass-budget-button" class="upload-button hidden" data-action="mass-budget">
                        <i class="fas fa-edit"></i> Изменить бюджет
                    </button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width:30px;"><input type="checkbox" id="select-all-checkbox"></th>
                            <th>№</th>
                            <th>ФИО</th>
                            <th>Бюджет</th>
                            <th>Телефон</th>
                            <th>MAX</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody id="students-table-body">
                        <tr><td colspan="7">Выберите группу для отображения студентов</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Модальные окна -->
<div id="add-group-modal" class="modal">
    <div class="modal-content">
        <span class="close" data-modal="add-group-modal">×</span>
        <h3>Добавить новую группу</h3>
        <form id="add-group-form">
            <input type="hidden" name="direction_id" id="direction_id" value="">
            <label for="group_name">Наименование:</label>
            <input type="text" id="group_name" name="group_name" placeholder="Введите наименование группы" required>
            <label for="notes">Примечание:</label>
            <textarea id="notes" name="notes" placeholder="Введите дополнительную информацию"></textarea>
            <button type="submit" class="add-button"><i class="fas fa-circle-plus"></i> Добавить</button>
        </form>
    </div>
</div>

<div id="edit-group-modal" class="modal">
    <div class="modal-content">
        <span class="close" data-modal="edit-group-modal">×</span>
        <h3>Редактировать группу</h3>
        <form id="edit-group-form">
            <input type="hidden" name="id" id="edit-group-id">
            <label for="edit-group-name">Наименование:</label>
            <input type="text" id="edit-group-name" name="group_name" required>
            <label for="edit-group-notes">Примечание:</label>
            <textarea id="edit-group-notes" name="notes"></textarea>
            <button type="submit" class="save-button"><i class="fas fa-floppy-disk"></i> Сохранить</button>
        </form>
    </div>
</div>

<div id="add-student-modal" class="modal">
    <div class="modal-content">
        <span class="close" data-modal="add-student-modal">×</span>
        <h3>Добавить нового студента</h3>
        <form id="add-student-form">
            <input type="hidden" name="group_id" id="student-group-id">
            <label for="full_name">ФИО:</label>
            <input type="text" id="full_name" name="full_name" placeholder="Введите ФИО студента">
            <label for="budget">Бюджет:</label>
            <select id="budget" name="budget">
                <option value="">Выберите бюджет</option>
                <option value="РФ">РФ</option>
                <option value="ХМАО">ХМАО</option>
            </select>
            <label for="phone">Телефон:</label>
            <input type="text" id="phone" name="phone" placeholder="Введите номер телефона">
            <label for="telegram">MAX:</label>
            <input type="text" id="telegram" name="telegram" placeholder="Введите MAX">
            <button type="submit" class="add-button"><i class="fas fa-circle-plus"></i> Добавить</button>
        </form>
    </div>
</div>

<div id="edit-student-modal" class="modal">
    <div class="modal-content">
        <span class="close" data-modal="edit-student-modal">×</span>
        <h3>Редактировать студента</h3>
        <form id="edit-student-form">
            <input type="hidden" name="id" id="edit-student-id">
            <label for="edit-full-name">ФИО:</label>
            <input type="text" id="edit-full-name" name="full_name">
            <label for="edit-budget">Бюджет:</label>
            <select id="edit-budget" name="budget">
                <option value="">Выберите бюджет</option>
                <option value="РФ">РФ</option>
                <option value="ХМАО">ХМАО</option>
            </select>
            <label for="edit-phone">Телефон:</label>
            <input type="text" id="edit-phone" name="phone">
            <label for="edit-telegram">MAX:</label>
            <input type="text" id="edit-telegram" name="telegram">
            <button type="submit" class="save-button"><i class="fas fa-floppy-disk"></i> Сохранить</button>
        </form>
    </div>
</div>

<div id="upload-students-modal" class="modal">
    <div class="modal-content">
        <span class="close" data-modal="upload-students-modal">×</span>
        <h3>Загрузить студентов из Excel</h3>
        <form id="upload-students-form" enctype="multipart/form-data">
            <input type="hidden" name="group_id" id="upload-group-id">
            <label for="excel_file">Выберите Excel-файл (.xls, .xlsx):</label>
            <input type="file" id="excel_file" name="excel_file" accept=".xls,.xlsx" required>
            <div style="margin:10px 0; padding:10px; background:#f0f0f0; border-radius:5px;">
                <strong>Требования к файлу:</strong>
                <ul style="margin:5px 0; padding-left:20px;">
                    <li>Формат: .xls или .xlsx</li>
                    <li>Первая строка должна содержать заголовки</li>
                    <li><strong>Обязательная колонка:</strong> "ФИО"</li>
                </ul>
            </div>
            <button type="submit" class="upload-button"><i class="fas fa-upload"></i> Загрузить</button>
        </form>
    </div>
</div>

<div id="mass-budget-modal" class="modal">
    <div class="modal-content">
        <span class="close" data-modal="mass-budget-modal">×</span>
        <h3>Изменить бюджет для выбранных студентов</h3>
        <form id="mass-budget-form">
            <label for="new-budget">Новый бюджет:</label>
            <select id="new-budget" name="budget" required>
                <option value="">Выберите бюджет</option>
                <option value="РФ">РФ</option>
                <option value="ХМАО">ХМАО</option>
            </select>
            <button type="submit" class="save-button"><i class="fas fa-floppy-disk"></i> Применить</button>
        </form>
    </div>
</div>

<div id="student-files-modal" class="modal">
    <div class="modal-content" style="max-width:800px;">
        <span class="close" data-modal="student-files-modal">×</span>
        <h3 id="student-files-title">Файлы студента</h3>
        <div style="margin-bottom:20px; padding:15px; background:#f9f9f9; border-radius:5px;">
            <h4>Добавить файл</h4>
            <form id="upload-file-form" enctype="multipart/form-data">
                <input type="hidden" name="student_id" id="upload-student-id">
                <div style="display:flex; gap:10px; align-items:flex-end;">
                    <div style="flex:1;">
                        <input type="file" id="uploaded_file" name="uploaded_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif" required>
                        <small style="display:block; margin-top:5px; color:#666;">Разрешены: PDF, Word, изображения. Максимальный размер: 10MB</small>
                    </div>
                    <button type="submit" class="add-button"><i class="fas fa-upload"></i> Загрузить</button>
                </div>
            </form>
        </div>
        <div id="files-list-container">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th>Имя файла</th>
                        <th>Тип</th>
                        <th>Размер</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody id="files-table-body"></tbody>
            </table>
            <div id="no-files-message" style="text-align:center; padding:20px; color:#666; display:none;">Нет загруженных файлов</div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
<script>
    let selectedSchoolCode = '';
    let selectedDirectionId = '';
    let selectedGroupId = '{{ $selected_group_id ?? '' }}';
    let selectedGroupName = '';
    let selectedSchoolName = '';
    let selectedDirectionName = '';
    let isAdmin = {{ $is_admin ? 'true' : 'false' }};
    let userSchoolCode = @json($user_school_code);

    function showLoadingSpinner() { document.getElementById('loading-spinner').style.display = 'block'; }
    function hideLoadingSpinner() { document.getElementById('loading-spinner').style.display = 'none'; }

    function showNotification(message, type) {
        type = type || 'success';
        const container = document.querySelector('.notification-container');
        const notification = document.createElement('div');
        notification.className = 'notification ' + type;
        var iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        notification.innerHTML = '<i class="fas ' + iconClass + '"></i><span>' + message + '</span><span class="close-btn" onclick="this.parentElement.remove()">×</span>';
        container.appendChild(notification);
        setTimeout(function() { notification.remove(); }, 5000);
    }

    function openModal(modalId) {
        var modal = document.getElementById(modalId);
        if (!modal) { showNotification('Ошибка: модальное окно не найдено', 'error'); return; }
        modal.style.display = 'block';
        if (modalId === 'add-group-modal') {
            document.getElementById('direction_id').value = selectedDirectionId;
        } else if (modalId === 'add-student-modal') {
            document.getElementById('student-group-id').value = selectedGroupId;
        } else if (modalId === 'upload-students-modal') {
            document.getElementById('upload-group-id').value = selectedGroupId;
        }
    }

    function closeModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
    }

    function onSchoolChange() {
        var schoolSelect = document.getElementById('school-filter');
        var directionSelect = document.getElementById('direction-filter');
        if (schoolSelect.value) {
            selectedSchoolCode = schoolSelect.value;
            selectedSchoolName = schoolSelect.options[schoolSelect.selectedIndex].text;
            fetchDirections(selectedSchoolCode).then(function() {
                directionSelect.classList.remove('hidden');
                directionSelect.value = '';
                selectedDirectionId = '';
                document.getElementById('add-group-button').classList.add('hidden');
                document.getElementById('groups-table-body').innerHTML = '';
                document.getElementById('students-table-body').innerHTML = '<tr><td colspan="7">Выберите группу для отображения студентов</td></tr>';
            });
        } else {
            directionSelect.classList.add('hidden');
            document.getElementById('add-group-button').classList.add('hidden');
            selectedSchoolCode = '';
            loadAllGroups();
        }
    }

    function onDirectionChange() {
        var directionSelect = document.getElementById('direction-filter');
        if (directionSelect.value) {
            selectedDirectionId = directionSelect.value;
            selectedDirectionName = directionSelect.options[directionSelect.selectedIndex].text;
            document.getElementById('add-group-button').classList.remove('hidden');
            loadGroupsByDirection(selectedDirectionId);
        } else {
            selectedDirectionId = '';
            document.getElementById('add-group-button').classList.add('hidden');
            document.getElementById('groups-table-body').innerHTML = '';
            document.getElementById('students-table-body').innerHTML = '<tr><td colspan="7">Выберите группу для отображения студентов</td></tr>';
        }
    }

    function loadGroupsByDirection(directionId) {
        showLoadingSpinner();
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: 'action=get_groups_by_direction&direction_id=' + encodeURIComponent(directionId)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            hideLoadingSpinner();
            if (data.type === 'success') {
                updateGroupsTable(data.groups, true);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(function(error) {
            hideLoadingSpinner();
            showNotification('Ошибка загрузки групп: ' + error.message, 'error');
        });
    }

    function loadAllGroups() {
        showLoadingSpinner();
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: 'action=get_all_groups'
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            hideLoadingSpinner();
            if (data.type === 'success') {
                updateGroupsTable(data.groups);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(function(error) {
            hideLoadingSpinner();
            showNotification('Ошибка загрузки групп: ' + error.message, 'error');
        });
    }

    function fetchDirections(vshCode) {
        showLoadingSpinner();
        var body = 'action=get_directions';
        if (!isAdmin && userSchoolCode) body += '&vsh_code=' + encodeURIComponent(userSchoolCode);
        else if (vshCode) body += '&vsh_code=' + encodeURIComponent(vshCode);

        return fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: body
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            hideLoadingSpinner();
            var directionSelect = document.getElementById('direction-filter');
            directionSelect.innerHTML = '<option value="">Выберите направление</option>';
            if (data && data.length) {
                data.forEach(function(direction) {
                    var option = document.createElement('option');
                    option.value = direction.code;
                    option.text = direction.direction_name;
                    directionSelect.appendChild(option);
                });
            }
        })
        .catch(function(error) {
            hideLoadingSpinner();
            showNotification('Ошибка загрузки направлений: ' + error.message, 'error');
        });
    }

    function fetchStudents(groupId) {
        if (!groupId) {
            document.getElementById('students-table-body').innerHTML = '<tr><td colspan="7">Выберите группу для отображения студентов</td></tr>';
            document.getElementById('selected-group-name').textContent = '';
            return;
        }

        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: 'action=get_students&group_id=' + encodeURIComponent(groupId)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'error') {
                showNotification(data.message, 'error');
            } else {
                updateStudentsTable(data);
            }
        })
        .catch(function(error) { showNotification('Ошибка загрузки студентов: ' + error.message, 'error'); });
    }

    function updateStudentsTable(students) {
        var tbody = document.getElementById('students-table-body');
        tbody.innerHTML = '';
        if (!students || students.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7">Студенты не найдены</td></tr>';
            return;
        }
        students.forEach(function(student, index) {
            var row = tbody.insertRow();
            row.setAttribute('data-student-id', student.id);
            var fullName = student.full_name || 'Не указано';
            var budget = student.budget || 'Не указано';
            var phone = student.phone || 'Не указано';
            var telegram = student.telegram || 'Не указано';
            row.innerHTML = `
                <td style="text-align:center"><input type="checkbox" class="student-checkbox" value="${student.id}"></td>
                <td>${index + 1}</td>
                <td><span class="clickable-student-name" data-student-id="${student.id}" data-student-name="${fullName}">${fullName}</span></td>
                <td>${budget}</td>
                <td>${phone}</td>
                <td>${telegram}</td>
                <td>
                    <div class="actions">
                        <button class="edit-button" data-action="edit-student" data-id="${student.id}" data-name="${fullName}" data-budget="${student.budget || ''}" data-phone="${student.phone || ''}" data-telegram="${student.telegram || ''}">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="delete-button" data-action="delete-student" data-id="${student.id}" data-group-id="${student.group_id}">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            `;
        });
    }

    function updateGroupsTable(groups, skipFilter) {
        skipFilter = skipFilter || false;
        var tbody = document.getElementById('groups-table-body');
        tbody.innerHTML = '';
        if (!groups || groups.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3">Группы не найдены</td></tr>';
            return;
        }
        groups.forEach(function(group) {
            var row = tbody.insertRow();
            row.setAttribute('data-group-id', group.id);
            row.setAttribute('data-direction-id', group.direction_id);
            row.setAttribute('data-vsh-code', group.vsh_code);
            var groupName = group.group_name || 'Не указано';
            var notes = group.notes || '';
            row.innerHTML = `
                <td><span class="clickable-name" data-group='${JSON.stringify(group)}'>${groupName}</span></td>
                <td>${notes}</td>
                <td>
                    <div class="actions">
                        <button class="edit-button" data-action="edit-group" data-id="${group.id}" data-direction="${group.direction_id}" data-name="${groupName}" data-notes="${notes}">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button class="delete-button" data-action="delete-group" data-id="${group.id}">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            `;
        });
        highlightSelectedGroup();
    }

    function handleGroupClick(event) {
        var groupData = JSON.parse(event.target.getAttribute('data-group'));
        selectedGroupId = groupData.id;
        selectedGroupName = groupData.group_name;
        selectedSchoolCode = groupData.vsh_code;
        selectedDirectionId = groupData.direction_id;
        selectedSchoolName = groupData.school_name;
        selectedDirectionName = groupData.direction_name;

        document.getElementById('selected-group-name').textContent = '(' + selectedGroupName + ')';

        if (!isAdmin) {
            var infoDiv = document.querySelector('.school-info');
            if (infoDiv) selectedSchoolName = infoDiv.innerText.replace('Школа:', '').trim();
        } else {
            var schoolSel = document.getElementById('school-filter');
            if (schoolSel) schoolSel.value = selectedSchoolCode;
        }

        var directionSelect = document.getElementById('direction-filter');
        directionSelect.classList.remove('hidden');
        directionSelect.value = selectedDirectionId;

        var addBtn = document.getElementById('add-group-button');
        if (addBtn && isAdmin) addBtn.classList.remove('hidden');

        document.getElementById('add-student-button').classList.remove('hidden');
        document.getElementById('delete-all-students-button').classList.remove('hidden');
        document.getElementById('upload-students-button').classList.remove('hidden');
        document.getElementById('mass-budget-button').classList.remove('hidden');

        fetchStudents(selectedGroupId);
        highlightSelectedGroup();

        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: 'action=select_group&group_id=' + encodeURIComponent(selectedGroupId)
        });
    }

    function highlightSelectedGroup() {
        var rows = document.querySelectorAll('#groups-table-body tr');
        rows.forEach(function(row) {
            var gid = row.getAttribute('data-group-id');
            row.style.backgroundColor = (gid == selectedGroupId) ? '#e3f2fd' : '';
        });
    }

    function submitGroupForm(e) {
        e.preventDefault();
        var form = e.target;
        var data = new FormData(form);
        data.append('action', 'add_group');
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: new URLSearchParams(data)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'error') showNotification(data.message, 'error');
            else { showNotification(data.message, 'success'); closeModal('add-group-modal'); checkForUpdates(); }
        })
        .catch(function(err) { showNotification('Ошибка: ' + err.message, 'error'); });
    }

    function submitEditGroupForm(e) {
        e.preventDefault();
        var form = e.target;
        var data = new FormData(form);
        data.append('action', 'edit_group');
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: new URLSearchParams(data)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'error') showNotification(data.message, 'error');
            else { showNotification(data.message, 'success'); closeModal('edit-group-modal'); checkForUpdates(); }
        })
        .catch(function(err) { showNotification('Ошибка: ' + err.message, 'error'); });
    }

    function submitStudentForm(e) {
        e.preventDefault();
        var form = e.target;
        var data = new FormData(form);
        data.append('action', 'add_student');
        data.append('group_id', selectedGroupId);
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: new URLSearchParams(data)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'error') showNotification(data.message, 'error');
            else { showNotification(data.message, 'success'); closeModal('add-student-modal'); checkForUpdates(); }
        })
        .catch(function(err) { showNotification('Ошибка: ' + err.message, 'error'); });
    }

    function submitEditStudentForm(e) {
        e.preventDefault();
        var form = e.target;
        var data = new FormData(form);
        data.append('action', 'edit_student');
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: new URLSearchParams(data)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'error') showNotification(data.message, 'error');
            else { showNotification(data.message, 'success'); closeModal('edit-student-modal'); checkForUpdates(); }
        })
        .catch(function(err) { showNotification('Ошибка: ' + err.message, 'error'); });
    }

    function submitUploadStudentsForm(e) {
        e.preventDefault();
        if (!selectedGroupId) { showNotification('Сначала выберите группу', 'error'); return; }
        var form = e.target;
        var formData = new FormData(form);
        formData.append('action', 'upload_students');
        formData.append('group_id', selectedGroupId);
        var btn = form.querySelector('button[type="submit"]');
        var origText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка...';
        btn.disabled = true;
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.innerHTML = origText;
            btn.disabled = false;
            if (data.type === 'error') showNotification(data.message, 'error');
            else { showNotification(data.message, 'success'); closeModal('upload-students-modal'); checkForUpdates(); form.reset(); }
        })
        .catch(function(err) { btn.innerHTML = origText; btn.disabled = false; showNotification('Ошибка: ' + err.message, 'error'); });
    }

    function submitMassBudgetUpdate(e) {
        e.preventDefault();
        var studentIds = getSelectedStudentIds();
        if (studentIds.length === 0) { showNotification('Нет выбранных студентов', 'error'); closeModal('mass-budget-modal'); return; }
        var newBudget = document.getElementById('new-budget').value;
        if (!newBudget) { showNotification('Выберите бюджет', 'error'); return; }
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: 'action=mass_update_budget&student_ids=' + encodeURIComponent(JSON.stringify(studentIds)) + '&budget=' + encodeURIComponent(newBudget) + '&group_id=' + encodeURIComponent(selectedGroupId)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'success') {
                showNotification(data.message, 'success');
                closeModal('mass-budget-modal');
                document.getElementById('select-all-checkbox').checked = false;
                fetchStudents(selectedGroupId);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(function(err) { showNotification('Ошибка: ' + err.message, 'error'); });
    }

    function uploadStudentFile(e) {
        e.preventDefault();
        var form = e.target;
        var formData = new FormData(form);
        var btn = form.querySelector('button[type="submit"]');
        var origText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка...';
        btn.disabled = true;
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.innerHTML = origText;
            btn.disabled = false;
            if (data.type === 'success') {
                showNotification(data.message, 'success');
                form.reset();
                var sid = document.getElementById('upload-student-id').value;
                loadStudentFiles(sid);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(function(err) { btn.innerHTML = origText; btn.disabled = false; showNotification('Ошибка: ' + err.message, 'error'); });
    }

    function loadStudentFiles(studentId) {
        var tbody = document.getElementById('files-table-body');
        tbody.innerHTML = '';
        var noMsg = document.getElementById('no-files-message');
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: 'action=get_student_files&student_id=' + encodeURIComponent(studentId)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'success') {
                if (data.files && data.files.length) {
                    noMsg.style.display = 'none';
                    updateFilesTable(data.files);
                } else {
                    noMsg.style.display = 'block';
                }
            } else showNotification(data.message, 'error');
        })
        .catch(function(err) { showNotification('Ошибка загрузки файлов: ' + err.message, 'error'); });
    }

    function updateFilesTable(files) {
        var tbody = document.getElementById('files-table-body');
        tbody.innerHTML = '';
        files.forEach(function(file) {
            var fileSize = formatFileSize(file.file_size);
            var uploadDate = new Date(file.uploaded_at * 1000).toLocaleDateString('ru-RU');
            var row = tbody.insertRow();
            var filePath = '{{ asset("storage") }}/' + file.file_path;
            row.innerHTML = `
                <td>${file.file_name}</td>
                <td>${file.file_type.toUpperCase()}</td>
                <td>${fileSize}</td>
                <td>${uploadDate}</td>
                <td>
                    <div class="actions">
                        <button class="view-button" data-action="view-file" data-path="${filePath}"><i class="fas fa-eye"></i></button>
                        <button class="download-button" data-action="download-file" data-path="${filePath}" data-name="${file.file_name}"><i class="fas fa-download"></i></button>
                        <button class="delete-button" data-action="delete-file" data-id="${file.id}" data-student-id="${file.student_id}"><i class="fas fa-trash-can"></i></button>
                    </div>
                </td>
            `;
        });
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function getSelectedStudentIds() {
        var checkboxes = document.querySelectorAll('.student-checkbox:checked');
        return Array.from(checkboxes).map(function(cb) { return parseInt(cb.value); });
    }

    function toggleSelectAll() {
        var selectAll = document.getElementById('select-all-checkbox');
        var checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
    }

    function checkForUpdates() {
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: 'action=check_updates&group_id=' + (selectedGroupId || '')
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'error') showNotification(data.message, 'error');
            else {
                updateGroupsTable(data.groups, false);
                if (selectedGroupId) fetchStudents(selectedGroupId);
            }
        })
        .catch(function(err) { showNotification('Ошибка: ' + err.message, 'error'); });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Select all checkbox
        document.getElementById('select-all-checkbox').addEventListener('change', toggleSelectAll);

        // School filter change
        document.getElementById('school-filter')?.addEventListener('change', onSchoolChange);

        // Direction filter change
        document.getElementById('direction-filter')?.addEventListener('change', onDirectionChange);

        // Modal open buttons
        document.querySelectorAll('[data-modal]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openModal(this.getAttribute('data-modal'));
            });
        });

        // Modal close buttons
        document.querySelectorAll('.close[data-modal]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                closeModal(this.getAttribute('data-modal'));
            });
        });

        // Edit group buttons
        document.querySelectorAll('[data-action="edit-group"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('edit-group-id').value = this.getAttribute('data-id');
                document.getElementById('edit-group-name').value = this.getAttribute('data-name');
                document.getElementById('edit-group-notes').value = this.getAttribute('data-notes') || '';
                openModal('edit-group-modal');
            });
        });

        // Delete group buttons
        document.querySelectorAll('[data-action="delete-group"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                confirmDeleteGroup(this.getAttribute('data-id'));
            });
        });

        // Edit student buttons
        document.querySelectorAll('[data-action="edit-student"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('edit-student-id').value = this.getAttribute('data-id');
                document.getElementById('edit-full-name').value = this.getAttribute('data-name') || '';
                document.getElementById('edit-budget').value = this.getAttribute('data-budget') || '';
                document.getElementById('edit-phone').value = this.getAttribute('data-phone') || '';
                document.getElementById('edit-telegram').value = this.getAttribute('data-telegram') || '';
                openModal('edit-student-modal');
            });
        });

        // Delete student buttons
        document.querySelectorAll('[data-action="delete-student"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                confirmDeleteStudent(this.getAttribute('data-id'), this.getAttribute('data-group-id'));
            });
        });

        // Delete all students
        document.querySelector('[data-action="delete-all-students"]')?.addEventListener('click', confirmDeleteAllStudents);

        // Mass budget
        document.querySelector('[data-action="mass-budget"]')?.addEventListener('click', openMassBudgetModal);

        // View file
        document.querySelectorAll('[data-action="view-file"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                window.open(this.getAttribute('data-path'), '_blank');
            });
        });

        // Download file
        document.querySelectorAll('[data-action="download-file"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var a = document.createElement('a');
                a.href = this.getAttribute('data-path');
                a.download = this.getAttribute('data-name');
                a.click();
                a.remove();
            });
        });

        // Delete file
        document.querySelectorAll('[data-action="delete-file"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                confirmDeleteFile(this.getAttribute('data-id'), this.getAttribute('data-student-id'));
            });
        });

        // Group name click
        document.querySelectorAll('.clickable-name').forEach(function(el) {
            el.addEventListener('click', handleGroupClick);
        });

        // Student name click
        document.querySelectorAll('.clickable-student-name').forEach(function(el) {
            el.addEventListener('click', function() {
                openStudentFilesModal(this.getAttribute('data-student-id'), this.getAttribute('data-student-name'));
            });
        });

        // Forms submit
        document.getElementById('add-group-form')?.addEventListener('submit', submitGroupForm);
        document.getElementById('edit-group-form')?.addEventListener('submit', submitEditGroupForm);
        document.getElementById('add-student-form')?.addEventListener('submit', submitStudentForm);
        document.getElementById('edit-student-form')?.addEventListener('submit', submitEditStudentForm);
        document.getElementById('upload-students-form')?.addEventListener('submit', submitUploadStudentsForm);
        document.getElementById('upload-file-form')?.addEventListener('submit', uploadStudentFile);
        document.getElementById('mass-budget-form')?.addEventListener('submit', submitMassBudgetUpdate);

        // Modal close on background click
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this.id);
            });
        });

        // Initial load
        if (!isAdmin && userSchoolCode) {
            selectedSchoolCode = userSchoolCode;
            var infoDiv = document.querySelector('.school-info');
            if (infoDiv) selectedSchoolName = infoDiv.innerText.replace('Школа:', '').trim();
            fetchDirections(userSchoolCode).then(function() {
                if (selectedGroupId) {
                    var row = document.querySelector('#groups-table-body tr[data-group-id="' + selectedGroupId + '"]');
                    if (row) {
                        var nameSpan = row.querySelector('.clickable-name');
                        if (nameSpan) handleGroupClick({ target: nameSpan });
                    }
                }
            });
        } else if (isAdmin) {
            if (selectedGroupId) {
                var row = document.querySelector('#groups-table-body tr[data-group-id="' + selectedGroupId + '"]');
                if (row) {
                    var nameSpan = row.querySelector('.clickable-name');
                    if (nameSpan) handleGroupClick({ target: nameSpan });
                }
            }
        }
    });

    function confirmDeleteGroup(id) {
        if (!confirm('Удалить группу?')) return;
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: 'action=delete_group&id=' + encodeURIComponent(id)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'error') showNotification(data.message, 'error');
            else { showNotification(data.message, 'success'); checkForUpdates(); if (selectedGroupId == id) { selectedGroupId = ''; selectedGroupName = ''; fetchStudents(null); } }
        })
        .catch(function(err) { showNotification('Ошибка: ' + err.message, 'error'); });
    }

    function confirmDeleteStudent(id, group_id) {
        if (!confirm('Удалить студента?')) return;
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: 'action=delete_student&id=' + encodeURIComponent(id) + '&group_id=' + encodeURIComponent(group_id)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'error') showNotification(data.message, 'error');
            else { showNotification(data.message, 'success'); checkForUpdates(); }
        })
        .catch(function(err) { showNotification('Ошибка: ' + err.message, 'error'); });
    }

    function confirmDeleteAllStudents() {
        if (!selectedGroupId) { showNotification('Выберите группу', 'error'); return; }
        if (!confirm('Удалить всех студентов?')) return;
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: 'action=delete_all_students&group_id=' + encodeURIComponent(selectedGroupId)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'error') showNotification(data.message, 'error');
            else { showNotification(data.message, 'success'); checkForUpdates(); }
        })
        .catch(function(err) { showNotification('Ошибка: ' + err.message, 'error'); });
    }

    function confirmDeleteFile(fileId, studentId) {
        if (!confirm('Удалить файл?')) return;
        fetch('{{ route("groups.data") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: 'action=delete_student_file&file_id=' + encodeURIComponent(fileId)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.type === 'success') {
                showNotification(data.message, 'success');
                loadStudentFiles(studentId);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(function(err) { showNotification('Ошибка: ' + err.message, 'error'); });
    }

    function openStudentFilesModal(studentId, studentName) {
        document.getElementById('student-files-title').textContent = 'Файлы студента: ' + studentName;
        document.getElementById('upload-student-id').value = studentId;
        openModal('student-files-modal');
        loadStudentFiles(studentId);
    }

    function openMassBudgetModal() {
        var selected = getSelectedStudentIds();
        if (selected.length === 0) { showNotification('Выберите студентов', 'error'); return; }
        openModal('mass-budget-modal');
    }
</script>
@endsection