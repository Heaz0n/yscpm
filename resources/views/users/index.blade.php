@extends('layouts.app')

@section('title', 'Управление пользователями')

@section('content')
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f4f9; color: #1e293b; line-height: 1.5; min-height: 100vh; }
    .main-content { padding: 2rem 1.5rem; min-height: calc(100vh - 64px); }
    .container { max-width: 1400px; margin: 0 auto; }
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
    .header-left { display: flex; align-items: center; gap: 1rem; }
    .header-icon { width: 3.5rem; height: 3.5rem; background: linear-gradient(135deg, #2563eb, #1e40af); border-radius: 1rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    .header-title h1 { font-size: 1.8rem; font-weight: 700; color: #0f172a; letter-spacing: -0.02em; }
    .header-subtitle { font-size: 0.875rem; color: #475569; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { background: white; border-radius: 1rem; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: all 0.2s; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
    .stat-content { display: flex; justify-content: space-between; align-items: center; }
    .stat-info h3 { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #5b6e8c; }
    .stat-number { font-size: 2rem; font-weight: 800; color: #0f172a; }
    .stat-icon { width: 2.8rem; height: 2.8rem; background: rgba(37,99,235,0.1); border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #2563eb; }
    .main-card { background: white; border-radius: 1.5rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; overflow: hidden; }
    .card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #eef2f6; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: #ffffff; }
    .search-wrapper { position: relative; flex: 1; min-width: 280px; max-width: 400px; }
    .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .search-input { width: 100%; padding: 0.65rem 1rem 0.65rem 2.5rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 2rem; font-size: 0.875rem; transition: all 0.2s; }
    .search-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); background: white; }
    .btn { padding: 0.5rem 1.25rem; border: none; font-size: 0.875rem; font-weight: 500; border-radius: 2rem; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; }
    .btn-primary { background: #2563eb; color: white; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); }
    .btn-secondary { background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0; }
    .btn-secondary:hover { background: #e2e8f0; }
    .btn-danger { background: #ef4444; color: white; }
    .btn-danger:hover { background: #dc2626; }
    .table-wrapper { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th { text-align: left; padding: 1rem 1.25rem; font-size: 0.75rem; font-weight: 600; color: #5b6e8c; text-transform: uppercase; background: #fcfdfe; border-bottom: 1px solid #eef2f6; }
    .data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid #f0f4f9; vertical-align: middle; }
    .data-table tbody tr:hover { background: #fafcff; }
    .user-cell { display: flex; align-items: center; gap: 0.8rem; }
    .user-avatar { width: 2.5rem; height: 2.5rem; background: linear-gradient(135deg, #2563eb, #1e40af); border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; flex-shrink: 0; }
    .user-info { display: flex; flex-direction: column; }
    .user-name { font-weight: 600; color: #000000 !important; }
    .user-email { font-size: 0.75rem; color: #5b6e8c; }
    .role-badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.75rem; border-radius: 2rem; font-size: 0.7rem; font-weight: 600; }
    .role-badge.admin { background: #fef2f2; color: #b91c1c; }
    .role-badge.director { background: #ecfdf5; color: #047857; }
    .role-badge.deputy_director { background: #fffbeb; color: #b45309; }
    .role-badge.secretary { background: #e0f2fe; color: #0369a1; }
    .role-badge.member { background: #f1f5f9; color: #334155; }
    .table-actions { display: flex; gap: 0.5rem; justify-content: flex-end; }
    .action-btn { width: 2rem; height: 2rem; border: none; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s; background: transparent; }
    .action-btn.edit { color: #2563eb; }
    .action-btn.edit:hover { background: #dbeafe; transform: scale(1.05); }
    .action-btn.delete { color: #ef4444; }
    .action-btn.delete:hover { background: #fee2e2; transform: scale(1.05); }
    .empty-state, .loading { text-align: center; padding: 3rem; }
    .empty-icon { font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem; }
    .loading-spinner { width: 2rem; height: 2rem; border: 3px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); align-items: center; justify-content: center; z-index: 1000; }
    .modal-overlay.active { display: flex; }
    .modal-container { background: white; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 40px -12px rgba(0,0,0,0.25); border: 1px solid #e2e8f0; border-radius: 0; }
    @keyframes modalFadeIn { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }
    .modal-header { padding: 1.25rem 1.8rem; border-bottom: 1px solid #eef2f6; display: flex; justify-content: space-between; align-items: center; background: #ffffff; }
    .modal-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 0.6rem; }
    .modal-close { background: #f1f5f9; border: none; width: 2rem; height: 2rem; border-radius: 0; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .modal-close:hover { background: #e2e8f0; }
    .modal-body { padding: 1.5rem 1.8rem; }
    .form-group { margin-bottom: 1.2rem; }
    .form-label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.3rem; color: #334155; }
    .required { color: #ef4444; }
    .form-control { width: 100%; padding: 0.7rem 1rem; border: 1px solid #cbd5e1; background: white; font-size: 0.9rem; border-radius: 0; transition: 0.2s; }
    .form-control:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.1); }
    .password-wrapper { position: relative; }
    .password-toggle { position: absolute; right: 0.5rem; top: 0.7rem; background: none; border: none; cursor: pointer; }
    .form-hint { display: block; margin-top: 0.25rem; font-size: 0.7rem; color: #64748b; }
    .modal-footer { padding: 1rem 1.8rem 1.8rem; border-top: 1px solid #eef2f6; display: flex; justify-content: flex-end; gap: 0.8rem; }
    .toast { position: fixed; bottom: 2rem; right: 2rem; background: white; border-left: 4px solid; padding: 0.75rem 1.25rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 0.75rem; z-index: 1100; font-size: 0.85rem; }
    .toast.success { border-left-color: #10b981; background: #ecfdf5; color: #065f46; }
    .toast.error { border-left-color: #ef4444; background: #fef2f2; color: #991b1b; }
    @media (max-width: 640px) { .main-content { padding: 1rem; } .card-header { flex-direction: column; align-items: stretch; } .modal-container { width: 95%; } }
</style>

<div class="main-content">
    <div class="container">
        <div class="page-header">
            <div class="header-left">
                <div class="header-icon"><i class="fas fa-users-cog"></i></div>
                <div class="header-title">
                    <h1>Управление пользователями</h1>
                    <div class="header-subtitle">Администрирование учетных записей и прав доступа</div>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-content">
                    <div class="stat-info"><h3>Всего пользователей</h3><div class="stat-number" id="totalUsers">0</div></div>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="stat-card admins">
                <div class="stat-content">
                    <div class="stat-info"><h3>Администраторов</h3><div class="stat-number" id="adminCount">0</div></div>
                    <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
                </div>
            </div>
            <div class="stat-card active">
                <div class="stat-content">
                    <div class="stat-info"><h3>Активных</h3><div class="stat-number" id="activeCount">0</div></div>
                    <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                </div>
            </div>
        </div>

        <div class="main-card">
            <div class="card-header">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" id="searchInput" placeholder="Поиск по имени, email или роли...">
                </div>
                <div class="card-actions">
                    <button class="btn btn-secondary" id="refreshBtn"><i class="fas fa-sync-alt"></i> Обновить</button>
                    <button class="btn btn-primary" id="addUserBtn"><i class="fas fa-user-plus"></i> Добавить</button>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr><th>Пользователь</th><th>Роль</th><th>Организация</th><th style="text-align: right;">Действия</th></tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr><td colspan="4"><div class="loading"><div class="loading-spinner"></div><p>Загрузка...</p></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="userModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title" id="modalTitle"><i class="fas fa-user-plus"></i> Новый пользователь</div>
            <button class="modal-close" id="closeModalBtn">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modalLoading" style="display: none; text-align: center; padding: 2rem;">
                <div class="loading-spinner"></div>
                <p>Загрузка...</p>
            </div>
            <form id="userForm" style="display: block;">
                <input type="hidden" id="userId">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> ФИО</label>
                    <input type="text" class="form-control" id="full_name" placeholder="Иванов Иван Иванович">
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-envelope"></i> Email (логин) <span class="required">*</span></label>
                    <input type="email" class="form-control" id="login" placeholder="user@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user-tag"></i> Роль <span class="required">*</span></label>
                    <select class="form-control" id="role" required>
                        <option value="">Выберите роль</option>
                        <option value="admin">Администратор системы</option>
                        <option value="director">Руководитель высшей школы</option>
                        <option value="deputy_director">Заместитель руководителя</option>
                        <option value="secretary">Секретарь комиссии</option>
                        <option value="member">Член комиссии</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock"></i> Пароль <span class="required" id="passwordRequired">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" id="password" placeholder="Введите пароль">
                        <button type="button" class="password-toggle" id="togglePassword"><i class="fas fa-eye"></i></button>
                    </div>
                    <span class="form-hint" id="passwordHint">При редактировании оставьте пустым, чтобы не менять пароль</span>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-university"></i> Высшая школа</label>
                    <input type="text" class="form-control" id="school" placeholder="Полное название организации">
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-building"></i> Краткое название</label>
                    <input type="text" class="form-control" id="school_short" placeholder="Сокращенное название">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelBtn">Отмена</button>
            <button class="btn btn-primary" id="saveBtn">Сохранить</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="confirmModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-exclamation-triangle"></i> Подтверждение удаления</div>
            <button class="modal-close" id="closeConfirmBtn">&times;</button>
        </div>
        <div class="modal-body">
            <p>Вы действительно хотите удалить пользователя <strong><span id="deleteUserName"></span></strong>?</p>
            <div style="background: #fef2f2; padding: 0.8rem; margin-top: 1rem; border-left: 4px solid #ef4444;">
                <i class="fas fa-exclamation-circle" style="color:#ef4444;"></i> Это действие необратимо.
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelDeleteBtn">Отмена</button>
            <button class="btn btn-danger" id="confirmDeleteBtn">Удалить</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
    let currentUserId = null, isEditMode = false, userToDelete = null, allUsers = [];
    var usersTableBody = document.getElementById('usersTableBody');
    var searchInput = document.getElementById('searchInput');
    var refreshBtn = document.getElementById('refreshBtn');
    var addUserBtn = document.getElementById('addUserBtn');
    var userModal = document.getElementById('userModal');
    var confirmModal = document.getElementById('confirmModal');
    var closeModalBtn = document.getElementById('closeModalBtn');
    var closeConfirmBtn = document.getElementById('closeConfirmBtn');
    var cancelBtn = document.getElementById('cancelBtn');
    var cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    var saveBtn = document.getElementById('saveBtn');
    var confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    var modalTitle = document.getElementById('modalTitle');
    var userForm = document.getElementById('userForm');
    var deleteUserName = document.getElementById('deleteUserName');
    var togglePassword = document.getElementById('togglePassword');
    var passwordInput = document.getElementById('password');
    var passwordRequired = document.getElementById('passwordRequired');
    var passwordHint = document.getElementById('passwordHint');
    var modalLoading = document.getElementById('modalLoading');
    var totalUsersEl = document.getElementById('totalUsers');
    var adminCountEl = document.getElementById('adminCount');
    var activeCountEl = document.getElementById('activeCount');

    function getUserAvatar(name) {
        if (!name || !name.trim()) return '?';
        var parts = name.split(' ');
        if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
        return name[0].toUpperCase();
    }

    function getRoleLabel(role) {
        var roles = { admin:'Администратор', director:'Руководитель', deputy_director:'Заместитель', secretary:'Секретарь', member:'Член комиссии' };
        return roles[role] || role;
    }

    function openModal(modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModalWindow(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadUsers();
        refreshBtn.addEventListener('click', function() { loadUsers(); showNotification('Данные обновлены', 'success'); });
        addUserBtn.addEventListener('click', openAddModal);
        closeModalBtn.addEventListener('click', function() { closeModalWindow(userModal); });
        cancelBtn.addEventListener('click', function() { closeModalWindow(userModal); });
        closeConfirmBtn.addEventListener('click', function() { closeModalWindow(confirmModal); });
        cancelDeleteBtn.addEventListener('click', function() { closeModalWindow(confirmModal); });
        saveBtn.addEventListener('click', saveUser);
        confirmDeleteBtn.addEventListener('click', deleteUser);
        searchInput.addEventListener('input', filterUsers);
        togglePassword.addEventListener('click', function() {
            var icon = togglePassword.querySelector('i');
            var type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        });
        window.addEventListener('click', function(e) {
            if (e.target === userModal) closeModalWindow(userModal);
            if (e.target === confirmModal) closeModalWindow(confirmModal);
        });
    });

    function loadUsers() {
        var url = '{{ route("users.data") }}';
        fetch(url)
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.success) {
                allUsers = result.data;
                renderTable(allUsers);
                updateStats(allUsers);
            } else {
                showNotification('Ошибка загрузки', 'error');
                showEmptyState('Ошибка загрузки данных');
            }
        })
        .catch(function(e) {
            showNotification('Ошибка подключения', 'error');
            showEmptyState('Нет подключения к серверу');
        });
    }

    function showLoading() {
        usersTableBody.innerHTML = '<tr><td colspan="4"><div class="loading"><div class="loading-spinner"></div><p>Загрузка данных...</p></div></td></tr>';
    }

    function showEmptyState(msg) {
        msg = msg || 'Пользователи не найдены';
        usersTableBody.innerHTML = '<tr><td colspan="4"><div class="empty-state"><div class="empty-icon"><i class="fas fa-users-slash"></i></div><div class="empty-title">' + msg + '</div><button class="btn btn-primary" id="addFirstUserBtn"><i class="fas fa-user-plus"></i> Добавить</button></div></td></tr>';
        var firstBtn = document.getElementById('addFirstUserBtn');
        if (firstBtn) firstBtn.addEventListener('click', openAddModal);
    }

    function renderTable(users) {
        users = users || [];
        if (!users.length) { showEmptyState(); return; }
        var html = '';
        users.forEach(function(user) {
            var userName = user.full_name || 'Не указано';
            var userLogin = user.login || '';
            var roleLabel = getRoleLabel(user.role);
            var schoolHtml = user.school ? '<div><strong>' + escapeHtml(user.school) + '</strong></div>' + (user.school_short ? '<div style="font-size:0.7rem;color:#475569;">' + escapeHtml(user.school_short) + '</div>' : '') : '<span style="color:#94a3b8;">—</span>';
            html += '<tr>' +
                '<td><div class="user-cell"><div class="user-avatar">' + getUserAvatar(user.full_name) + '</div><div class="user-info"><div class="user-name">' + escapeHtml(userName) + '</div><div class="user-email">' + escapeHtml(userLogin) + '</div></div></div></td>' +
                '<td><span class="role-badge ' + user.role + '"><i class="fas fa-tag"></i> ' + roleLabel + '</span></td>' +
                '<td>' + schoolHtml + '</td>' +
                '<td><div class="table-actions"><button class="action-btn edit" data-id="' + user.id + '"><i class="fas fa-edit"></i></button><button class="action-btn delete" data-id="' + user.id + '"><i class="fas fa-trash"></i></button></div></td>' +
            '</tr>';
        });
        usersTableBody.innerHTML = html;
        document.querySelectorAll('.action-btn.edit').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openEditModal(parseInt(this.getAttribute('data-id')));
            });
        });
        document.querySelectorAll('.action-btn.delete').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openDeleteModal(parseInt(this.getAttribute('data-id')));
            });
        });
    }

    function escapeHtml(str) {
        if(!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if(m === '&') return '&amp;';
            if(m === '<') return '&lt;';
            if(m === '>') return '&gt;';
            return m;
        });
    }

    function filterUsers() {
        var term = searchInput.value.toLowerCase().trim();
        if (!term) { renderTable(allUsers); return; }
        var filtered = allUsers.filter(function(u) {
            return u.login.toLowerCase().includes(term) || (u.full_name && u.full_name.toLowerCase().includes(term)) || u.role.toLowerCase().includes(term) || (u.school && u.school.toLowerCase().includes(term)) || (u.school_short && u.school_short.toLowerCase().includes(term));
        });
        renderTable(filtered);
    }

    function openAddModal() {
        isEditMode = false;
        currentUserId = null;
        modalTitle.innerHTML = '<i class="fas fa-user-plus"></i> Новый пользователь';
        userForm.reset();
        passwordInput.required = true;
        passwordRequired.style.display = 'inline';
        passwordHint.style.display = 'none';
        userForm.style.display = 'block';
        modalLoading.style.display = 'none';
        openModal(userModal);
        document.getElementById('full_name').focus();
    }

    function openEditModal(id) {
        userForm.style.display = 'none';
        modalLoading.style.display = 'block';
        openModal(userModal);
        modalTitle.innerHTML = '<i class="fas fa-user-edit"></i> Редактирование пользователя';
        var url = '{{ route("users.data") }}?id=' + id;
        fetch(url)
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.success && result.data && result.data.length) {
                var user = result.data[0];
                isEditMode = true;
                currentUserId = id;
                document.getElementById('userId').value = user.id;
                document.getElementById('full_name').value = user.full_name || '';
                document.getElementById('login').value = user.login;
                document.getElementById('role').value = user.role;
                document.getElementById('school').value = user.school || '';
                document.getElementById('school_short').value = user.school_short || '';
                passwordInput.value = '';
                passwordInput.required = false;
                passwordRequired.style.display = 'none';
                passwordHint.style.display = 'block';
                userForm.style.display = 'block';
                modalLoading.style.display = 'none';
            } else {
                showNotification('Пользователь не найден', 'error');
                closeModalWindow(userModal);
            }
        })
        .catch(function(error) {
            showNotification('Ошибка загрузки данных', 'error');
            closeModalWindow(userModal);
        });
    }

    function saveUser() {
        var login = document.getElementById('login').value.trim();
        var full_name = document.getElementById('full_name').value.trim();
        var password = passwordInput.value.trim();
        var role = document.getElementById('role').value;
        var school = document.getElementById('school').value.trim();
        var school_short = document.getElementById('school_short').value.trim();
        if (!login) { showNotification('Введите email', 'error'); return; }
        if (!role) { showNotification('Выберите роль', 'error'); return; }
        if (!isEditMode && !password) { showNotification('Пароль обязателен', 'error'); return; }

        var url = isEditMode ? '{{ url("admin/users") }}/' + currentUserId : '{{ route("users.store") }}';
        var method = isEditMode ? 'PUT' : 'POST';
        var data = JSON.stringify({ login: login, full_name: full_name, password: password, role: role, school: school, school_short: school_short });

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';
        fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: data
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.success) {
                showNotification(result.message, 'success');
                closeModalWindow(userModal);
                loadUsers();
            } else {
                showNotification('Ошибка: ' + (result.message || 'Неизвестная ошибка'), 'error');
            }
        })
        .catch(function(e) {
            showNotification('Ошибка сохранения', 'error');
        })
        .finally(function() {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Сохранить';
        });
    }

    function openDeleteModal(id) {
        var user = allUsers.find(function(u) { return u.id == id; });
        if (!user) { showNotification('Пользователь не найден', 'error'); return; }
        userToDelete = id;
        deleteUserName.textContent = user.full_name || user.login;
        openModal(confirmModal);
    }

    function deleteUser() {
        if (!userToDelete) return;
        confirmDeleteBtn.disabled = true;
        confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Удаление...';
        fetch('{{ url("admin/users") }}/' + userToDelete, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.success) {
                showNotification(result.message, 'success');
                closeModalWindow(confirmModal);
                loadUsers();
            } else {
                showNotification('Ошибка: ' + (result.message || 'Не удалось удалить'), 'error');
            }
        })
        .catch(function(e) {
            showNotification('Ошибка удаления', 'error');
        })
        .finally(function() {
            userToDelete = null;
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.innerHTML = '<i class="fas fa-trash"></i> Удалить';
        });
    }

    function updateStats(users) {
        totalUsersEl.textContent = users.length;
        adminCountEl.textContent = users.filter(function(u) { return u.role === 'admin'; }).length;
        activeCountEl.textContent = users.length;
    }

    function showNotification(message, type) {
        var existing = document.querySelector('.toast');
        if (existing) existing.remove();
        var notification = document.createElement('div');
        notification.className = 'toast ' + type;
        var iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        notification.innerHTML = '<i class="fas ' + iconClass + '"></i> ' + message;
        document.body.appendChild(notification);
        setTimeout(function() { notification.remove(); }, 4000);
    }
</script>
@endsection