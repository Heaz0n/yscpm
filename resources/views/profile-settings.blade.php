@extends('layouts.app')

@section('title', 'Настройки пользователя')

@section('content')
<style>
    :root {
        --primary-blue: #43bbcd;
        --secondary-blue: #2a5a84;
        --accent-gold: #FFD100;
        --light-gold: #FFE566;
        --dark-blue: #003870;
        --white: #FFFFFF;
        --light-gray: #f8f9fa;
    }

    body { background-color: #f0f2f5; }
    .settings-header {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
        color: white;
        padding: 2rem 0;
        text-align: center;
        border-radius: 0 0 2rem 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .settings-header h1 { font-weight: 700; margin-bottom: 0.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .settings-header p { font-size: 1.1rem; opacity: 0.95; }
    .nav-tabs-custom {
        border-bottom: none;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        color: var(--secondary-blue);
        background-color: white;
        border-radius: 50px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    .nav-tabs-custom .nav-link:hover {
        background-color: var(--light-gold);
        color: var(--dark-blue);
        transform: translateY(-2px);
    }
    .nav-tabs-custom .nav-link.active {
        background: linear-gradient(135deg, var(--accent-gold), var(--light-gold));
        color: var(--dark-blue);
        font-weight: 700;
        box-shadow: 0 8px 20px rgba(255, 209, 0, 0.4);
    }
    .nav-tabs-custom .nav-link i { margin-right: 8px; }
    .tab-pane .card {
        border: none;
        border-radius: 1.5rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        overflow: hidden;
        background: white;
    }
    .tab-pane { transition: none !important; }
    .card-header-custom {
        background: linear-gradient(90deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 1rem 1.5rem;
        border-bottom: none;
    }
    .card-header-custom h3 { margin: 0; font-weight: 600; font-size: 1.25rem; }
    .card-body-custom { padding: 2rem; }
    .avatar-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 2rem;
    }
    .avatar-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--accent-gold);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 700;
        color: white;
        margin-bottom: 1rem;
    }
    .avatar-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; justify-content: center; }
    .btn-file { position: relative; overflow: hidden; }
    .btn-file input[type=file] {
        position: absolute;
        top: 0;
        right: 0;
        min-width: 100%;
        min-height: 100%;
        font-size: 100px;
        text-align: right;
        opacity: 0;
        outline: none;
        cursor: inherit;
        display: block;
    }
    .btn-primary-custom {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        border: none;
        color: white;
        padding: 0.6rem 1.8rem;
        border-radius: 50px;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(67, 187, 205, 0.4);
        transition: all 0.3s;
    }
    .btn-primary-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(67, 187, 205, 0.6);
        background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
    }
    .btn-outline-gold {
        background: transparent;
        border: 2px solid var(--accent-gold);
        color: var(--dark-blue);
        padding: 0.6rem 1.8rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-outline-gold:hover {
        background: var(--accent-gold);
        color: var(--dark-blue);
        transform: translateY(-2px);
    }
    .btn-outline-danger {
        border: 2px solid #dc3545;
        background: transparent;
        color: #dc3545;
        padding: 0.6rem 1.8rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-outline-danger:hover {
        background: #dc3545;
        color: white;
        transform: translateY(-2px);
    }
    .btn-danger-custom {
        background: #dc3545;
        border: none;
        border-radius: 50px;
        padding: 0.6rem 1.8rem;
        font-weight: 600;
        color: white;
    }
    .btn-danger-custom:hover { background: #bb2d3b; }
    .form-control-custom {
        border-radius: 50px;
        padding: 0.75rem 1.5rem;
        border: 2px solid #e0e0e0;
        transition: all 0.3s;
        width: 100%;
    }
    .form-control-custom:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 0.25rem rgba(67, 187, 205, 0.25);
        outline: none;
    }
    .input-group-custom { display: flex; align-items: center; }
    .input-group-custom .btn { border-radius: 50px; margin-left: 0.5rem; }
    .password-match.valid { color: #28a745; }
    .password-match.invalid { color: #dc3545; }
    .danger-zone {
        border: 2px solid #dc3545;
        background-color: #fff5f5;
        border-radius: 1.5rem;
        padding: 1.5rem;
        margin-top: 2rem;
    }
    .notification {
        position: fixed;
        top: 1.5rem;
        right: 1.5rem;
        padding: 1rem 1.5rem;
        border-radius: 50px;
        color: white;
        font-size: 0.875rem;
        z-index: 1050;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        animation: slideInRight 0.3s ease-out;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        max-width: 24rem;
        backdrop-filter: blur(8px);
    }
    .notification.success { background: rgba(16, 185, 129, 0.95); border-left: 4px solid #10b981; }
    .notification.error { background: rgba(239, 68, 68, 0.95); border-left: 4px solid #ef4444; }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .form-switch-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    .form-switch-custom:last-child { border-bottom: none; }
    .form-switch-custom .form-check-input { width: 3rem; height: 1.5rem; cursor: pointer; }
    @media (max-width: 768px) {
        .card-body-custom { padding: 1.5rem; }
        .avatar-actions { flex-direction: column; align-items: stretch; }
        .btn-outline-gold, .btn-outline-danger { width: 100%; }
        .nav-tabs-custom .nav-link { padding: 0.5rem 1rem; font-size: 0.9rem; }
    }
</style>

<div class="container mt-4">
    @if(session('notification'))
        <div class="notification {{ session('notification')['type'] }}">
            <i class="bi {{ session('notification')['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i>
            <span>{{ session('notification')['message'] }}</span>
        </div>
    @endif

    <div class="settings-header">
        <h1>Настройка пользователя</h1>
        <p>Управление личными данными, безопасностью и предпочтениями</p>
    </div>

    <ul class="nav nav-tabs-custom" id="settingsTabs" role="tablist">
        <li class="nav-item"><button class="nav-link" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab"><i class="bi bi-person-badge"></i> Личные данные</button></li>
        <li class="nav-item"><button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab"><i class="bi bi-shield-lock"></i> Смена пароля</button></li>
        <li class="nav-item"><button class="nav-link" id="subscription-tab" data-bs-toggle="tab" data-bs-target="#subscription" type="button" role="tab"><i class="bi bi-bell"></i> Уведомления и тема</button></li>
    </ul>

    <div class="tab-content" id="settingsTabsContent">
        <!-- Личные данные -->
        <div class="tab-pane" id="personal" role="tabpanel">
            <div class="card">
                <div class="card-header-custom"><h3><i class="bi bi-person-circle me-2"></i>Личные данные</h3></div>
                <div class="card-body-custom">
                    <div class="avatar-container">
                        @if($user->avatar && Storage::disk('public')->exists($user->avatar))
                            <img src="{{ Storage::url($user->avatar) . '?t=' . time() }}" alt="Avatar" class="avatar-preview">
                        @else
                            <div class="avatar-preview" style="background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));">
                                <i class="bi bi-person-circle" style="font-size: 4rem; color: white;"></i>
                            </div>
                        @endif
                        <div class="avatar-actions">
                            <form method="POST" enctype="multipart/form-data" action="{{ route('profile-settings.uploadAvatar') }}" style="display: inline;">
                                @csrf
                                <span class="btn btn-outline-gold btn-file"><i class="bi bi-camera"></i> Загрузить фото<input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" onchange="this.form.submit()"></span>
                            </form>
                            @if($user->avatar)
                                <form method="POST" action="{{ route('profile-settings.deleteAvatar') }}" style="display: inline;" onsubmit="return confirm('Удалить аватар?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Удалить</button>
                                </form>
                            @endif
                        </div>
                        <small class="text-muted mt-2">JPG, PNG, GIF, WEBP до 2 МБ</small>
                    </div>

                    <form method="POST" action="{{ route('profile-settings.updateProfile') }}" id="profile-form">
                        @csrf
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-bold" style="color: var(--secondary-blue);">ФИО</label>
                            <input type="text" class="form-control form-control-custom @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" required>
                            @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="login" class="form-label fw-bold" style="color: var(--secondary-blue);">Email</label>
                            <input type="email" class="form-control form-control-custom @error('login') is-invalid @enderror" id="login" name="login" value="{{ old('login', $user->login) }}" required>
                            @error('login') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-bold" style="color: var(--secondary-blue);">Телефон</label>
                            <input type="tel" class="form-control form-control-custom" id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="+7 (XXX) XXX-XX-XX">
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label fw-bold" style="color: var(--secondary-blue);">Роль</label>
                            <input type="text" class="form-control form-control-custom" id="role" value="{{ $roles[$user->role] ?? $user->role }}" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100"><i class="bi bi-save"></i> Сохранить изменения</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Смена пароля -->
        <div class="tab-pane" id="password" role="tabpanel">
            <div class="card">
                <div class="card-header-custom"><h3><i class="bi bi-shield-lock me-2"></i>Смена пароля</h3></div>
                <div class="card-body-custom">
                    <div class="alert alert-info d-flex align-items-center mb-4" style="border-radius: 50px; background-color: #e7f1ff; border: none;">
                        <i class="bi bi-person-circle fs-4 me-2" style="color: var(--secondary-blue);"></i>
                        <div><strong>Меняете пароль для:</strong> {{ $user->login }}</div>
                    </div>

                    <form method="POST" action="{{ route('profile-settings.changePassword') }}" id="password-form">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-bold" style="color: var(--secondary-blue);">Текущий пароль</label>
                            <div class="input-group-custom">
                                <input type="password" class="form-control form-control-custom @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password', this)"><i class="bi bi-eye"></i></button>
                            </div>
                            @error('current_password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-bold" style="color: var(--secondary-blue);">Новый пароль</label>
                            <div class="input-group-custom">
                                <input type="password" class="form-control form-control-custom @error('new_password') is-invalid @enderror" id="new_password" name="new_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password', this)"><i class="bi bi-eye"></i></button>
                            </div>
                            @error('new_password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label fw-bold" style="color: var(--secondary-blue);">Подтверждение пароля</label>
                            <div class="input-group-custom">
                                <input type="password" class="form-control form-control-custom" id="confirm_password" name="confirm_password" required oninput="checkPasswordMatch()">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', this)"><i class="bi bi-eye"></i></button>
                            </div>
                            <div id="password-match" class="password-match mt-2"></div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100"><i class="bi bi-key"></i> Изменить пароль</button>
                    </form>

                    <div class="danger-zone mt-5">
                        <form method="POST" action="{{ route('profile-settings.logoutSessions') }}" onsubmit="return confirm('Вы уверены, что хотите завершить все другие сеансы?')">
                            @csrf
                            <button type="submit" class="btn btn-danger-custom"><i class="bi bi-box-arrow-right"></i> Завершить все сеансы</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Уведомления и тема -->
        <div class="tab-pane" id="subscription" role="tabpanel">
            <div class="card">
                <div class="card-header-custom"><h3><i class="bi bi-bell me-2"></i>Уведомления и тема</h3></div>
                <div class="card-body-custom">
                    <form method="POST" action="{{ route('profile-settings.updateSettings') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="theme" class="form-label fw-bold" style="color: var(--secondary-blue);">Тема оформления</label>
                            <select class="form-select form-control-custom" id="theme" name="theme">
                                <option value="light" {{ $theme == 'light' ? 'selected' : '' }}>Светлая</option>
                                <option value="dark" {{ $theme == 'dark' ? 'selected' : '' }}>Тёмная</option>
                                <option value="auto" {{ $theme == 'auto' ? 'selected' : '' }}>Авто (системная)</option>
                            </select>
                        </div>
                        <div class="form-switch-custom">
                            <span><span class="fw-bold" style="color: var(--secondary-blue);">Email уведомления</span><br><small class="text-muted">Получать уведомления о важных событиях</small></span>
                            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" value="1" {{ $email_notify ? 'checked' : '' }}></div>
                        </div>
                        <div class="form-switch-custom">
                            <span><span class="fw-bold" style="color: var(--secondary-blue);">Уведомления в браузере</span><br><small class="text-muted">Показывать уведомления на рабочем столе</small></span>
                            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="desktop_notifications" name="desktop_notifications" value="1" {{ $desktop_notify ? 'checked' : '' }}></div>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100 mt-3"><i class="bi bi-save"></i> Сохранить настройки</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    function checkPasswordMatch() {
        const password = document.getElementById('new_password').value;
        const confirm = document.getElementById('confirm_password').value;
        const matchDiv = document.getElementById('password-match');
        if (confirm === '') { matchDiv.innerHTML = ''; return; }
        if (password === confirm) {
            matchDiv.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Пароли совпадают';
            matchDiv.className = 'password-match valid';
        } else {
            matchDiv.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Пароли не совпадают';
            matchDiv.className = 'password-match invalid';
        }
    }

    document.getElementById('profile-form')?.addEventListener('submit', function(e) {
        const fullName = document.getElementById('full_name').value.trim();
        const login = document.getElementById('login').value.trim();
        if (!fullName || fullName.length > 255) {
            e.preventDefault();
            alert('Пожалуйста, введите корректное ФИО (максимум 255 символов)');
        }
        if (!login || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(login)) {
            e.preventDefault();
            alert('Пожалуйста, введите корректный email адрес');
        }
    });

    document.getElementById('password-form')?.addEventListener('submit', function(e) {
        const current = document.getElementById('current_password').value;
        const newPass = document.getElementById('new_password').value;
        const confirm = document.getElementById('confirm_password').value;
        if (!current) { e.preventDefault(); alert('Введите текущий пароль'); return; }
        if (!newPass) { e.preventDefault(); alert('Введите новый пароль'); return; }
        if (newPass !== confirm) { e.preventDefault(); alert('Новый пароль и подтверждение не совпадают'); return; }
    });

    setTimeout(() => {
        const notification = document.querySelector('.notification');
        if (notification) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }
    }, 4000);

    // Активация нужной вкладки по якорю или сохранённому значению
    const hash = window.location.hash.substr(1);
    if (hash === 'personal' || hash === 'password' || hash === 'subscription') {
        const tab = new bootstrap.Tab(document.querySelector(`#${hash}-tab`));
        tab.show();
    } else if (localStorage.getItem('activeTab')) {
        const tabId = localStorage.getItem('activeTab');
        const tab = new bootstrap.Tab(document.querySelector(`#${tabId}`));
        tab.show();
    }
    document.querySelectorAll('#settingsTabs button').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            localStorage.setItem('activeTab', e.target.id);
        });
    });
</script>
@endsection