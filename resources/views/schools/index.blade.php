@extends('layouts.app')

@section('title', 'Высшие школы и направления')

@section('content')
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Roboto', sans-serif;
        background-color: #f5f6f5;
        color: #333;
        line-height: 1.6;
    }

    .container {
        max-width: 1280px;
        margin: 30px auto;
        padding: 20px;
    }

    h1 {
        font-size: 2.2em;
        font-weight: 700;
        color: #003087;
        text-align: center;
        margin-bottom: 30px;
    }

    h2 {
        font-size: 1.6em;
        font-weight: 500;
        color: #333;
        margin-bottom: 20px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 8px;
        border-bottom: 1px solid #ddd;
        flex-wrap: wrap;
        gap: 12px;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        margin-bottom: 30px;
        border-radius: 8px;
    }

    table {
        width: 100%;
        min-width: 600px;
        border-collapse: collapse;
        background-color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    th {
        background-color: #f9f9f9;
        font-weight: 500;
        color: #333;
        text-transform: uppercase;
        font-size: 13px;
    }

    tr:hover {
        background-color: #f1f5f9;
    }

    button {
        padding: 8px 12px;
        border: none;
        cursor: pointer;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s;
        white-space: nowrap;
    }

    button:hover {
        transform: translateY(-1px);
    }

    button.add-button {
        background-color: #2e7d32;
        color: white;
    }

    button.edit-button {
        background-color: #fbc02d;
        color: white;
        padding: 6px 10px;
    }

    button.edit-button:hover {
        background-color: #f9a825;
    }

    button.delete-button {
        background-color: #d32f2f;
        color: white;
        padding: 6px 10px;
    }

    .actions {
        display: flex;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal.show {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        border: 1px solid #ddd;
        position: relative;
        animation: slideIn 0.3s ease;
        max-height: 90vh;
        overflow-y: auto;
    }

    @keyframes slideIn {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-content h3 {
        font-size: 1.4em;
        margin-bottom: 15px;
        color: #333;
    }

    .close {
        color: #666;
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 20px;
        cursor: pointer;
        transition: color 0.3s;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .close:hover {
        color: #d32f2f;
        background-color: #f5f5f5;
    }

    form label {
        display: block;
        margin: 10px 0 5px;
        font-weight: 500;
        color: #333;
        font-size: 14px;
    }

    form input, form textarea, form select {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        background-color: #f9f9f9;
        transition: border-color 0.3s;
    }

    form input:focus, form textarea:focus, form select:focus {
        border-color: #0288d1;
        outline: none;
    }

    form textarea {
        resize: vertical;
        min-height: 100px;
    }

    .filter-select {
        padding: 8px;
        font-size: 14px;
        border-radius: 4px;
        border: 1px solid #ccc;
        background-color: #f9f9f9;
        cursor: pointer;
        min-width: 200px;
    }

    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1100;
        max-width: 350px;
    }

    .notification {
        padding: 12px 15px;
        margin-bottom: 10px;
        border-radius: 4px;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        animation: fadeIn 0.3s ease;
    }

    .notification.success {
        background-color: #2e7d32;
    }

    .notification.error {
        background-color: #d32f2f;
    }

    .notification .close-btn {
        cursor: pointer;
        font-size: 16px;
        margin-left: auto;
    }

    .clickable-name {
        cursor: pointer;
        color: #003087;
        font-weight: 500;
        transition: color 0.3s;
    }

    .clickable-name:hover {
        color: #001f5c;
        text-decoration: underline;
    }

    .school-code-column,
    .direction-code-column {
        display: none;
    }

    .no-access {
        text-align: center;
        padding: 40px;
        color: #666;
    }

    .no-access i {
        font-size: 48px;
        margin-bottom: 20px;
        color: #ccc;
    }

    @media (max-width: 768px) {
        .container { padding: 15px; }
        table { font-size: 13px; min-width: 550px; }
        th, td { padding: 8px; }
        .modal-content { width: 95%; padding: 15px; margin: 5% auto; }
        button { padding: 6px 10px; font-size: 13px; }
        .section-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="notification-container">
    @if(session('notification'))
        <div class="notification {{ session('notification')['type'] }}">
            <i class="fas {{ session('notification')['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
            <span>{{ session('notification')['message'] }}</span>
            <span class="close-btn" onclick="this.parentElement.remove()">×</span>
        </div>
    @endif
</div>

<div class="container">
    <h1>Высшие школы и направления</h1>

    <!-- Секция школ -->
    <div class="section-header">
        <h2>Высшие школы</h2>
        @if($isAdmin)
            <button class="add-button" id="addSchoolBtn"><i class="fas fa-circle-plus"></i> Добавить школу</button>
        @elseif($userSchool && in_array($userRole, ['director', 'deputy_director']))
            <button class="edit-button" id="editSchoolBtn"><i class="fas fa-pen"></i> Редактировать школу</button>
        @endif
    </div>

    @if($schools->isEmpty() && !$isAdmin)
        <div class="no-access">
            <i class="fas fa-university"></i>
            <p>У вас нет доступа к просмотру школ</p>
        </div>
    @else
        <div class="table-wrapper">
            <table id="schools-table">
                <thead>
                    <tr>
                        <th class="school-code-column">Код</th>
                        <th>Наименование</th>
                        <th>Сокращение</th>
                        <th>Руководитель</th>
                        <th>Заместитель</th>
                        <th>Примечание</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schools as $school)
                        <tr data-school-id="{{ $school->code }}">
                            <td class="school-code-column">{{ $school->code }}</td>
                            <td>
                                <span class="clickable-name school-name" data-school-id="{{ $school->code }}">
                                    {{ $school->name }}
                                </span>
                            </td>
                            <td>{{ $school->abbreviation }}</td>
                            <td>{{ $school->director }}</td>
                            <td>{{ $school->deputy_director }}</td>
                            <td>{{ $school->notes }}</td>
                            <td>
                                <div class="actions">
                                    @if($isAdmin || ($userSchool && $school->code == $userSchoolCode && in_array($userRole, ['director', 'deputy_director'])))
                                        <button class="edit-button edit-school-btn"
                                                data-code="{{ $school->code }}"
                                                data-name="{{ $school->name }}"
                                                data-abbreviation="{{ $school->abbreviation }}"
                                                data-director="{{ $school->director }}"
                                                data-deputy-director="{{ $school->deputy_director }}"
                                                data-notes="{{ $school->notes }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    @endif

                                    @if($isAdmin || ($userRole === 'director' && $school->code == $userSchoolCode))
                                        <button class="delete-button delete-school-btn"
                                                data-code="{{ $school->code }}"
                                                data-name="{{ $school->name }}">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Секция направлений -->
    <div class="section-header">
        <h2>Направления</h2>
        @if($isAdmin || ($userSchool && in_array($userRole, ['director', 'deputy_director', 'secretary'])))
            <div>
                @if($isAdmin)
                    <select id="school-filter" class="filter-select">
                        <option value="">Все школы</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->code }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                @endif
                <button class="add-button" id="addDirectionBtn"><i class="fas fa-circle-plus"></i> Добавить направление</button>
            </div>
        @endif
    </div>

    @if($directions->isEmpty() && !$isAdmin && !$userSchool)
        <div class="no-access">
            <i class="fas fa-graduation-cap"></i>
            <p>У вас нет доступа к просмотру направлений</p>
        </div>
    @else
        <div class="table-wrapper">
            <table id="directions-table">
                <thead>
                    <tr>
                        <th class="direction-code-column">Код</th>
                        <th>Наименование</th>
                        @if($isAdmin)<th>Школа</th>@endif
                        <th>Уровень</th>
                        <th>Примечание</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($directions as $direction)
                        <tr data-school-id="{{ $direction->vsh_code }}">
                            <td class="direction-code-column">{{ $direction->code }}</td>
                            <td>
                                <span class="clickable-name direction-name">
                                    {{ $direction->direction_name }}
                                </span>
                            </td>
                            @if($isAdmin)
                                <td>{{ $direction->school->name ?? '' }}</td>
                            @endif
                            <td>{{ $direction->level }}</td>
                            <td>{{ $direction->notes }}</td>
                            <td>
                                <div class="actions">
                                    @if($isAdmin || ($userSchool && $direction->vsh_code == $userSchoolCode && in_array($userRole, ['director', 'deputy_director', 'secretary'])))
                                        <button class="edit-button edit-direction-btn"
                                                data-code="{{ $direction->code }}"
                                                data-vsh-code="{{ $direction->vsh_code }}"
                                                data-direction-name="{{ $direction->direction_name }}"
                                                data-level="{{ $direction->level }}"
                                                data-notes="{{ $direction->notes }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    @endif

                                    @if($isAdmin || ($userSchool && $direction->vsh_code == $userSchoolCode && in_array($userRole, ['director', 'deputy_director'])))
                                        <button class="delete-button delete-direction-btn"
                                                data-code="{{ $direction->code }}"
                                                data-direction-name="{{ $direction->direction_name }}">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Модальное окно для школы -->
@if($showSchoolModal)
<div id="schoolModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="modalTitle">{{ $isAdmin ? 'Добавить новую школу' : 'Редактировать школу' }}</h3>
        <form id="schoolForm" method="POST" action="{{ $isAdmin ? route('schools.storeSchool') : route('schools.updateSchool') }}">
            @csrf
            <input type="hidden" name="code" id="schoolCode">
            <label for="schoolName">Наименование:</label>
            <input type="text" id="schoolName" name="name" required>
            <label for="schoolAbbreviation">Сокращение:</label>
            <input type="text" id="schoolAbbreviation" name="abbreviation">
            <label for="schoolDirector">Руководитель:</label>
            <input type="text" id="schoolDirector" name="director">
            <label for="schoolDeputyDirector">Заместитель:</label>
            <input type="text" id="schoolDeputyDirector" name="deputy_director">
            <label for="schoolNotes">Примечание:</label>
            <textarea id="schoolNotes" name="notes"></textarea>
            <button type="submit" class="add-button">
                <i class="fas {{ $isAdmin ? 'fa-circle-plus' : 'fa-floppy-disk' }}"></i>
                {{ $isAdmin ? 'Добавить' : 'Сохранить' }}
            </button>
        </form>
    </div>
</div>
@endif

<!-- Модальное окно для направления -->
@if($showDirectionModal)
<div id="directionModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="directionModalTitle">Добавить новое направление</h3>
        <form id="directionForm" method="POST" action="{{ route('schools.storeDirection') }}">
            @csrf
            <input type="hidden" name="code" id="directionCode">
            <label for="directionSchool">Школа:</label>
            <select id="directionSchool" name="vsh_code" required {{ !$isAdmin && $userSchool ? 'disabled' : '' }}>
                <option value="" disabled selected>Выберите школу</option>
                @foreach($schools as $school)
                    <option value="{{ $school->code }}">{{ $school->name }}</option>
                @endforeach
            </select>
            @if(!$isAdmin && $userSchool)
                <input type="hidden" name="vsh_code" value="{{ $userSchool->code }}">
            @endif
            <label for="directionName">Наименование:</label>
            <input type="text" id="directionName" name="direction_name" required>
            <label for="directionLevel">Уровень:</label>
            <select id="directionLevel" name="level">
                <option value="">Выберите уровень</option>
                <option value="Бакалавриат">Бакалавриат</option>
                <option value="Магистратура">Магистратура</option>
                <option value="Аспирантура">Аспирантура</option>
            </select>
            <label for="directionNotes">Примечание:</label>
            <textarea id="directionNotes" name="notes"></textarea>
            <button type="submit" class="add-button"><i class="fas fa-circle-plus"></i> Добавить</button>
        </form>
    </div>
</div>
@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Модальные окна
        const schoolModal = document.getElementById('schoolModal');
        const directionModal = document.getElementById('directionModal');

        function showModal(modal) {
            if (modal) modal.classList.add('show');
        }

        function hideModal(modal) {
            if (modal) modal.classList.remove('show');
        }

        // Добавление школы
        document.getElementById('addSchoolBtn')?.addEventListener('click', function() {
            document.getElementById('schoolForm').reset();
            document.getElementById('schoolCode').value = '';
            document.getElementById('schoolForm').action = '{{ route("schools.storeSchool") }}';
            document.getElementById('modalTitle').textContent = 'Добавить новую школу';
            showModal(schoolModal);
        });

        // Редактирование школы
        document.querySelectorAll('.edit-school-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('schoolCode').value = this.dataset.code;
                document.getElementById('schoolName').value = this.dataset.name;
                document.getElementById('schoolAbbreviation').value = this.dataset.abbreviation;
                document.getElementById('schoolDirector').value = this.dataset.director;
                document.getElementById('schoolDeputyDirector').value = this.dataset.deputyDirector;
                document.getElementById('schoolNotes').value = this.dataset.notes;
                document.getElementById('schoolForm').action = '{{ route("schools.updateSchool") }}';
                document.getElementById('modalTitle').textContent = 'Редактировать школу';
                showModal(schoolModal);
            });
        });

        // Удаление школы
        document.querySelectorAll('.delete-school-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (confirm('Вы уверены, что хотите удалить школу "' + this.dataset.name + '"?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("schools.deleteSchool") }}';
                    form.innerHTML = '@csrf<input type="hidden" name="code" value="' + this.dataset.code + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Добавление направления
        document.getElementById('addDirectionBtn')?.addEventListener('click', function() {
            document.getElementById('directionForm').reset();
            document.getElementById('directionCode').value = '';
            document.getElementById('directionForm').action = '{{ route("schools.storeDirection") }}';
            document.getElementById('directionModalTitle').textContent = 'Добавить новое направление';
            const filter = document.getElementById('school-filter');
            if (filter && filter.value) {
                const schoolSelect = document.getElementById('directionSchool');
                if (schoolSelect) schoolSelect.value = filter.value;
            }
            showModal(directionModal);
        });

        // Редактирование направления
        document.querySelectorAll('.edit-direction-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('directionCode').value = this.dataset.code;
                document.getElementById('directionSchool').value = this.dataset.vshCode;
                document.getElementById('directionName').value = this.dataset.directionName;
                document.getElementById('directionLevel').value = this.dataset.level;
                document.getElementById('directionNotes').value = this.dataset.notes;
                document.getElementById('directionForm').action = '{{ route("schools.updateDirection") }}';
                document.getElementById('directionModalTitle').textContent = 'Редактировать направление';
                showModal(directionModal);
            });
        });

        // Удаление направления
        document.querySelectorAll('.delete-direction-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (confirm('Вы уверены, что хотите удалить направление "' + this.dataset.directionName + '"?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("schools.deleteDirection") }}';
                    form.innerHTML = '@csrf<input type="hidden" name="code" value="' + this.dataset.code + '">';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Фильтрация
        const schoolFilter = document.getElementById('school-filter');
        if (schoolFilter) {
            schoolFilter.addEventListener('change', function() {
                const rows = document.querySelectorAll('#directions-table tbody tr');
                rows.forEach(function(row) {
                    if (!this.value || row.dataset.schoolId === this.value) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }, this);
            });
        }

        // Клик по названию школы
        document.querySelectorAll('.school-name').forEach(function(el) {
            el.addEventListener('click', function() {
                const filter = document.getElementById('school-filter');
                if (filter) {
                    filter.value = this.dataset.schoolId;
                    filter.dispatchEvent(new Event('change'));
                }
            });
        });

        // Закрытие модальных окон
        document.querySelectorAll('.close').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) hideModal(modal);
            });
        });

        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) hideModal(e.target);
        });

        // Автоскрытие уведомлений
        setTimeout(function() {
            document.querySelectorAll('.notification').forEach(function(n) {
                n.style.opacity = '0';
                setTimeout(function() { n.remove(); }, 500);
            });
        }, 5000);
    });
</script>
@endsection