<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Система учета протоколов стипендиальной комиссии - ЮГУ')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #43bbcd;
            --secondary-blue: #2a5a84;
            --accent-blue: #6BB1E5;
            --dark-blue: #003870;
            --light-blue: #E8F2FC;
            --gold: #FFD100;
            --light-gold: #FFE566;
            --white: #FFFFFF;
            --light-gray: #F8FAFD;
            --dark-gray: #2D3748;
            --sidebar-width: 300px;
            --sidebar-width-collapsed: 72px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background-color: var(--light-gray); margin: 0; padding: 0; overflow-x: hidden; }
        .sidebar-container { position: fixed; left: 0; top: 0; height: 100vh; z-index: 1000; display: flex; }
        .sidebar { background: linear-gradient(165deg, var(--primary-blue) 0%, var(--secondary-blue) 100%); box-shadow: 5px 0 25px rgba(0, 84, 166, 0.15); width: var(--sidebar-width); height: 100%; display: flex; flex-direction: column; overflow: hidden; border-right: 1px solid rgba(255, 255, 255, 0.08); }
        .sidebar-container.collapsed .sidebar { width: var(--sidebar-width-collapsed); box-shadow: 3px 0 15px rgba(0, 84, 166, 0.1); }
        .sidebar-header { padding: 24px 16px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.12); flex-shrink: 0; background: linear-gradient(180deg, rgba(0, 56, 112, 0.3) 0%, transparent 100%); }
        .header-top { display: flex; align-items: center; justify-content: flex-end; margin-bottom: 24px; }
        .collapse-btn { background: rgba(255, 255, 255, 0.12); border: none; color: var(--white); width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; backdrop-filter: blur(5px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .collapse-btn:hover { background: rgba(255, 255, 255, 0.2); color: var(--gold); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); }
        .sidebar-container.collapsed .collapse-btn { transform: rotate(180deg); }
        .user-card { padding: 14px; background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.04)); border-radius: 12px; cursor: pointer; border: 1px solid rgba(255, 255, 255, 0.06); backdrop-filter: blur(10px); }
        .user-card:hover { background: linear-gradient(135deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.06)); border-color: rgba(255, 255, 255, 0.1); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); }
        .user-card.compact { display: flex; align-items: center; justify-content: center; width: 54px; height: 54px; padding: 0; margin: 0 auto; border-radius: 50%; background: rgba(255, 255, 255, 0.1); }
        .user-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--gold), var(--light-gold)); display: flex; align-items: center; justify-content: center; color: var(--dark-blue); font-weight: 700; font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); border: 2px solid rgba(255, 255, 255, 0.3); object-fit: cover; }
        .user-card:hover .user-avatar { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25); }
        .user-card.compact .user-avatar { width: 42px; height: 42px; font-size: 1rem; border-width: 2px; }
        .user-info { display: flex; align-items: center; gap: 14px; }
        .user-details { flex: 1; min-width: 0; }
        .user-name { font-weight: 600; font-size: 0.95rem; color: var(--white); white-space: normal; word-break: break-word; line-height: 1.3; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); }
        .user-status { width: 10px; height: 10px; border-radius: 50%; background: linear-gradient(135deg, #4CAF50, #45a049); box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.4); flex-shrink: 0; margin-left: auto; }
        .sidebar-nav { flex: 1; padding: 20px 0; overflow-y: auto; display: flex; flex-direction: column; scrollbar-width: thin; scrollbar-color: rgba(255, 255, 255, 0.2) transparent; }
        .nav-section { margin-bottom: 12px; }
        .nav-section-title { font-size: 0.75rem; color: rgba(255, 255, 255, 0.55); font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; padding: 0 20px 10px; margin-top: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); display: none; }
        .sidebar-container:not(.collapsed) .nav-section-title { display: block; }
        .nav-item { position: relative; margin: 4px 16px; }
        .sidebar-container.collapsed .nav-item { margin: 4px 12px; }
        .nav-link { display: flex; align-items: center; padding: 14px 16px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 10px; font-weight: 500; font-size: 0.92rem; position: relative; overflow: hidden; border: 1px solid transparent; }
        .sidebar-container.collapsed .nav-link { padding: 14px; justify-content: center; }
        .nav-link:hover { background: rgba(255, 255, 255, 0.1); color: var(--white); border-color: rgba(255, 255, 255, 0.1); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .nav-link.active { background: linear-gradient(90deg, rgba(255, 209, 0, 0.2) 0%, rgba(255, 229, 102, 0.15) 100%); color: var(--gold); font-weight: 600; border-color: rgba(255, 209, 0, 0.3); box-shadow: 0 6px 16px rgba(255, 209, 0, 0.15); }
        .nav-link.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 4px; height: 50%; background: var(--gold); border-radius: 0 4px 4px 0; box-shadow: 0 0 10px var(--gold); }
        .nav-icon { font-size: 1.15rem; color: var(--light-gold); min-width: 24px; text-align: center; filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.1)); }
        .nav-link:hover .nav-icon, .nav-link.active .nav-icon { color: var(--gold); filter: drop-shadow(0 2px 4px rgba(255, 209, 0, 0.3)); }
        .nav-text { margin-left: 14px; white-space: normal; word-break: break-word; line-height: 1.3; }
        .sidebar-container.collapsed .nav-text { display: none; }
        .references-block { position: relative; }
        .references-toggle { display: flex; align-items: center; justify-content: space-between; width: 100%; background: transparent; border: none; text-align: left; padding: 14px 16px; color: rgba(255, 255, 255, 0.85); cursor: pointer; border-radius: 10px; font-weight: 500; font-size: 0.92rem; border: 1px solid transparent; }
        .sidebar-container.collapsed .references-toggle { padding: 14px; justify-content: center; }
        .references-toggle:hover { background: rgba(255, 255, 255, 0.1); color: var(--white); border-color: rgba(255, 255, 255, 0.1); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .references-toggle.active { background: linear-gradient(90deg, rgba(255, 209, 0, 0.2) 0%, rgba(255, 229, 102, 0.15) 100%); color: var(--gold); font-weight: 600; border-color: rgba(255, 209, 0, 0.3); }
        .references-toggle.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 4px; height: 50%; background: var(--gold); border-radius: 0 4px 4px 0; box-shadow: 0 0 10px var(--gold); }
        .toggle-content { display: flex; align-items: center; flex: 1; }
        .sidebar-container.collapsed .toggle-content { justify-content: center; }
        .references-arrow { font-size: 0.85rem; color: var(--light-gold); margin-left: auto; filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1)); transition: transform 0.2s; }
        .references-toggle.active .references-arrow { transform: rotate(90deg); color: var(--gold); }
        .sidebar-container.collapsed .references-arrow { display: none; }
        .references-menu { display: none; padding-left: 24px; margin-top: 6px; }
        .references-menu.open { display: block; }
        .menu-item { padding: 12px 16px 12px 40px; margin: 3px 0; border-radius: 8px; display: flex; align-items: center; color: rgba(255, 255, 255, 0.8); text-decoration: none; font-size: 0.88rem; font-weight: 500; position: relative; border: 1px solid transparent; }
        .menu-item span { white-space: normal; word-break: break-word; line-height: 1.3; }
        .menu-item:hover { background: rgba(255, 255, 255, 0.08); color: var(--light-gold); border-color: rgba(255, 255, 255, 0.1); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .menu-item.active { background: linear-gradient(90deg, rgba(107, 177, 229, 0.2) 0%, rgba(107, 177, 229, 0.1) 100%); color: var(--accent-blue); font-weight: 600; border-color: rgba(107, 177, 229, 0.3); }
        .menu-item::before { content: ''; position: absolute; left: 24px; top: 50%; transform: translateY(-50%); width: 5px; height: 5px; background: var(--accent-blue); border-radius: 50%; opacity: 0.7; }
        .menu-item:hover::before { opacity: 1; background: var(--light-gold); box-shadow: 0 0 8px var(--light-gold); }
        .menu-item.active::before { opacity: 1; background: var(--gold); box-shadow: 0 0 10px var(--gold); transform: translateY(-50%) scale(1.2); }
        .menu-icon { margin-right: 12px; color: var(--accent-blue); font-size: 0.95rem; min-width: 18px; text-align: center; }
        .menu-item:hover .menu-icon { color: var(--light-gold); }
        .menu-item.active .menu-icon { color: var(--gold); }
        .menu-divider { height: 1px; background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.15) 50%, transparent 100%); margin: 10px 16px; }
        .sidebar-container.collapsed .references-menu { display: none !important; }
        .logout-section { margin-top: auto; padding: 20px 16px 24px; border-top: 1px solid rgba(255, 255, 255, 0.12); background: linear-gradient(180deg, transparent 0%, rgba(0, 56, 112, 0.2) 100%); }
        .sidebar-container.collapsed .logout-section { padding: 20px 12px; }
        .logout-btn { display: flex; align-items: center; padding: 14px 16px; background: linear-gradient(135deg, rgba(220, 53, 69, 0.2) 0%, rgba(220, 53, 69, 0.1) 100%); border: 1px solid rgba(220, 53, 69, 0.25); border-radius: 10px; color: #FF8A8A; font-weight: 500; font-size: 0.92rem; cursor: pointer; width: 100%; backdrop-filter: blur(5px); }
        .sidebar-container.collapsed .logout-btn { padding: 14px; justify-content: center; }
        .logout-btn:hover { background: linear-gradient(135deg, rgba(220, 53, 69, 0.3) 0%, rgba(220, 53, 69, 0.2) 100%); border-color: rgba(220, 53, 69, 0.35); color: #FF6B6B; box-shadow: 0 6px 16px rgba(220, 53, 69, 0.2); }
        .logout-icon { font-size: 1.15rem; color: #FF8A8A; filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.1)); }
        .logout-btn:hover .logout-icon { color: #FF6B6B; }
        .logout-text { margin-left: 14px; font-weight: 500; white-space: normal; word-break: break-word; }
        .sidebar-container.collapsed .logout-text { display: none; }
        .mobile-toggle { position: fixed; top: 18px; left: 18px; z-index: 1002; background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue)); border: none; color: white; width: 48px; height: 48px; border-radius: 10px; font-size: 1.4rem; box-shadow: 0 6px 20px rgba(0, 84, 166, 0.3); display: none; align-items: center; justify-content: center; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.15); }
        .mobile-toggle:hover { background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue)); box-shadow: 0 8px 25px rgba(0, 84, 166, 0.4); }
        .mobile-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.6); z-index: 999; display: none; backdrop-filter: blur(5px); }
        @media (max-width: 991.98px) {
            .sidebar-container { transform: translateX(-100%); transition: transform 0.3s ease; }
            .sidebar-container.mobile-open { transform: translateX(0); }
            .sidebar-container.collapsed { width: 240px; }
            .sidebar-container.collapsed .sidebar { width: 240px; }
            .mobile-toggle { display: flex; }
            .mobile-overlay.active { display: block; }
            .collapse-btn { display: none; }
        }
        @media (min-width: 992px) {
            .mobile-toggle { display: none; }
            .mobile-overlay { display: none !important; }
            .collapse-btn { display: flex; }
        }
        .sidebar-nav::-webkit-scrollbar { width: 5px; }
        .sidebar-nav::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); border-radius: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.3); }
        .sidebar-container.collapsed .sidebar { background: linear-gradient(165deg, var(--primary-blue) 0%, var(--secondary-blue) 100%); }
        .sidebar-container.collapsed .header-top { justify-content: center; margin-bottom: 16px; }
        .sidebar-container.collapsed .sidebar-header { padding: 20px 8px 16px; }
        .main-content { margin-left: var(--sidebar-width); transition: margin-left 0.3s ease; min-height: 100vh; padding: 20px; }
        .sidebar-container.collapsed ~ .main-content { margin-left: var(--sidebar-width-collapsed); }
        @media (max-width: 991.98px) { .main-content { margin-left: 0 !important; padding-top: 70px; } }
    </style>
</head>
<body>
    <button class="mobile-toggle" id="mobileToggle"><i class="bi bi-list"></i></button>
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="sidebar-container" id="sidebarContainer">
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="header-top">
                    <button class="collapse-btn" id="collapseBtn"><i class="bi bi-chevron-left"></i></button>
                </div>
                <div class="user-card" id="userCard">
                    @php
                        $user = Auth::user();
                        $fullName = $user->full_name ?? 'Пользователь';
                        $initials = '';
                        if ($user && $user->full_name) {
                            $parts = explode(' ', trim($user->full_name));
                            $initials = count($parts) >= 2
                                ? mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1)
                                : mb_substr($parts[0], 0, 2);
                            $initials = mb_strtoupper($initials);
                        } else {
                            $initials = '??';
                        }
                        $is_admin = ($user->role ?? '') === 'admin';
                        $avatar = $user->avatar ?? null;
                    @endphp
                    <div class="user-info">
                        @if($avatar && file_exists(public_path($avatar)))
                            <img src="{{ asset($avatar) }}" class="user-avatar" style="object-fit: cover;">
                        @else
                            <div class="user-avatar">{{ $initials }}</div>
                        @endif
                        <div class="user-details">
                            <div class="user-name">{{ $fullName }}</div>
                        </div>
                        <div class="user-status"></div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-item references-block">
                        <button class="references-toggle" id="referencesToggle">
                            <div class="toggle-content">
                                <i class="bi bi-journal-bookmark-fill nav-icon"></i>
                                <span class="nav-text">Справочники</span>
                            </div>
                            <i class="bi bi-chevron-right references-arrow"></i>
                        </button>
                        <div class="references-menu" id="referencesMenu">
    <a href="{{ route('categories.index') }}" class="menu-item"><i class="bi bi-tags-fill menu-icon"></i><span>Категории</span></a>
    <a href="{{ route('schools.index') }}" class="menu-item"><i class="bi bi-building-gear menu-icon"></i><span>Высшие школы и направления</span></a>
    <a href="{{ route('groups.index') }}" class="menu-item"><i class="bi bi-people-fill menu-icon"></i><span>Группы и студенты</span></a>
    <!-- Новый пункт: Генерация протокола -->
    <a href="{{ route('protocols.index') }}" class="menu-item"><i class="bi bi-file-earmark-text menu-icon"></i><span>Генерация протокола</span></a>
    @if(!$is_admin)
        {{-- <a href="{{ route('template.index') }}" class="menu-item"><i class="bi bi-file-earmark menu-icon"></i><span>Шаблон</span></a> --}}
    @endif
    @if($is_admin)
        <div class="menu-divider"></div>
        <a href="{{ route('admin.users.index') }}" class="menu-item"><i class="bi bi-person-badge menu-icon"></i><span>Пользователи системы</span></a>
    @endif
</div>
                    </div>

                    @if(!$is_admin)
                        <div class="nav-item">
                            <a href="{{ route('students.list') }}" class="nav-link"><i class="bi bi-person-badge nav-icon"></i><span class="nav-text">Материальная помощь</span></a>
                        </div>
                    @endif

                    <div class="nav-item">
                        <a href="{{ route('reports.index') }}" class="nav-link"><i class="bi bi-bar-chart-steps nav-icon"></i><span class="nav-text">Отчёты</span></a>
                    </div>

                    <div class="nav-item">
                        <a href="{{ route('profile-settings.index') }}" class="nav-link"><i class="bi bi-gear-wide nav-icon"></i><span class="nav-text">Настройки</span></a>
                    </div>
                </div>
            </nav>

            <div class="logout-section">
                <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                    @csrf
                    <button type="submit" class="logout-btn"><i class="bi bi-box-arrow-right logout-icon"></i><span class="logout-text">Выйти из системы</span></button>
                </form>
            </div>
        </div>
    </div>

    <div class="main-content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarContainer = document.getElementById('sidebarContainer');
            const collapseBtn = document.getElementById('collapseBtn');
            const mobileToggle = document.getElementById('mobileToggle');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const referencesToggle = document.getElementById('referencesToggle');
            const referencesMenu = document.getElementById('referencesMenu');

            let isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            let isMobileOpen = false;
            let isReferencesOpen = false;

            const isAdmin = @json($is_admin);
            const userInitials = @json($initials);
            const userName = @json($fullName);
            const userAvatar = @json($avatar);

            function updateUserCard() {
                const userCard = document.getElementById('userCard');
                if (sidebarContainer.classList.contains('collapsed')) {
                    userCard.classList.add('compact');
                    let content = '';
                    if (userAvatar) {
                        content = `<img src="${userAvatar}" class="user-avatar" style="object-fit: cover;">`;
                    } else {
                        content = `<div class="user-avatar">${userInitials}</div>`;
                    }
                    userCard.innerHTML = content;
                } else {
                    userCard.classList.remove('compact');
                    let avatarContent = '';
                    if (userAvatar) {
                        avatarContent = `<img src="${userAvatar}" class="user-avatar" style="object-fit: cover;">`;
                    } else {
                        avatarContent = `<div class="user-avatar">${userInitials}</div>`;
                    }
                    userCard.innerHTML = `
                        <div class="user-info">
                            ${avatarContent}
                            <div class="user-details">
                                <div class="user-name">${userName}</div>
                            </div>
                            <div class="user-status"></div>
                        </div>
                    `;
                }
            }

            function setActiveMenu() {
                const currentPath = window.location.pathname;
                document.querySelectorAll('.nav-link').forEach(link => {
                    const href = link.getAttribute('href');
                    if (href && (currentPath === href || currentPath === href + '/')) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });
                let anyReferenceActive = false;
                document.querySelectorAll('.menu-item').forEach(item => {
                    const href = item.getAttribute('href');
                    if (href && (currentPath === href || currentPath === href + '/')) {
                        item.classList.add('active');
                        anyReferenceActive = true;
                    } else {
                        item.classList.remove('active');
                    }
                });
                if (anyReferenceActive) {
                    referencesToggle.classList.add('active');
                    if (!sidebarContainer.classList.contains('collapsed') && window.innerWidth >= 992) {
                        referencesMenu.classList.add('open');
                        isReferencesOpen = true;
                    }
                } else {
                    referencesToggle.classList.remove('active');
                }
            }

            function initSidebar() {
                if (isCollapsed && window.innerWidth >= 992) {
                    sidebarContainer.classList.add('collapsed');
                } else if (window.innerWidth >= 992 && !sidebarContainer.classList.contains('collapsed')) {
                    sidebarContainer.classList.remove('collapsed');
                }
                updateUserCard();
                setActiveMenu();
            }

            if (collapseBtn) {
                collapseBtn.addEventListener('click', function() {
                    if (window.innerWidth < 992) return;
                    sidebarContainer.classList.toggle('collapsed');
                    isCollapsed = sidebarContainer.classList.contains('collapsed');
                    localStorage.setItem('sidebarCollapsed', isCollapsed);
                    updateUserCard();
                    if (isCollapsed) {
                        referencesToggle.classList.remove('active');
                        referencesMenu.classList.remove('open');
                        isReferencesOpen = false;
                    } else {
                        if (referencesToggle.classList.contains('active')) {
                            referencesMenu.classList.add('open');
                            isReferencesOpen = true;
                        }
                    }
                });
            }

            if (referencesToggle) {
                referencesToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (window.innerWidth < 992 || sidebarContainer.classList.contains('collapsed')) return;
                    isReferencesOpen = !isReferencesOpen;
                    if (isReferencesOpen) {
                        referencesToggle.classList.add('active');
                        referencesMenu.classList.add('open');
                    } else {
                        referencesToggle.classList.remove('active');
                        referencesMenu.classList.remove('open');
                    }
                });
            }

            document.addEventListener('click', function(e) {
                if (referencesToggle && !referencesToggle.contains(e.target) && !referencesMenu.contains(e.target)) {
                    referencesToggle.classList.remove('active');
                    referencesMenu.classList.remove('open');
                    isReferencesOpen = false;
                }
            });

            if (mobileToggle) {
                mobileToggle.addEventListener('click', function() {
                    isMobileOpen = !isMobileOpen;
                    sidebarContainer.classList.toggle('mobile-open');
                    mobileOverlay.classList.toggle('active');
                    document.body.style.overflow = isMobileOpen ? 'hidden' : '';
                });
            }

            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', function() {
                    isMobileOpen = false;
                    sidebarContainer.classList.remove('mobile-open');
                    mobileOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                });
            }

            const logoutForm = document.getElementById('logoutForm');
            if (logoutForm) {
                logoutForm.addEventListener('submit', function(e) {
                    if (!confirm('Вы уверены, что хотите выйти из системы?')) {
                        e.preventDefault();
                    }
                });
            }

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebarContainer.classList.remove('mobile-open');
                    mobileOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                    isMobileOpen = false;
                    if (isCollapsed) {
                        sidebarContainer.classList.add('collapsed');
                    } else {
                        sidebarContainer.classList.remove('collapsed');
                    }
                    if (referencesToggle.classList.contains('active') && !sidebarContainer.classList.contains('collapsed')) {
                        referencesMenu.classList.add('open');
                        isReferencesOpen = true;
                    }
                } else {
                    sidebarContainer.classList.remove('collapsed');
                }
                updateUserCard();
            });

            initSidebar();
        });
    </script>
</body>
</html>