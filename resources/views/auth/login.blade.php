<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация - Система учета протоколов</title>
    <style>
        /* Все стили из login.php, без изменений */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        body {
            background-color: #f0f5ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            transition: background-color 0.3s;
        }
        .login-container {
            width: 100%;
            max-width: 450px;
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 40px 35px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s;
        }
        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }
        .login-header h2 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .login-header p {
            color: #7f8c8d;
            font-size: 1rem;
        }
        .login-form {
            width: 100%;
        }
        .input-group {
            margin-bottom: 25px;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        .input-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e8f4ff;
            border-radius: 10px;
            font-size: 1rem;
            transition: border 0.3s, box-shadow 0.3s;
            background-color: #f8fbff;
            color: #2c3e50;
        }
        .input-group input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            background-color: white;
        }
        .forgot-password {
            display: block;
            text-align: right;
            margin-bottom: 28px;
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-password:hover {
            text-decoration: underline;
            color: #2980b9;
        }
        .login-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s, box-shadow 0.2s;
            margin-bottom: 25px;
            letter-spacing: 0.5px;
        }
        .login-button:hover {
            background: linear-gradient(135deg, #2980b9, #34495e);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
        }
        .login-button:active {
            transform: translateY(0);
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.2);
        }
        .login-button:disabled {
            background: linear-gradient(135deg, #bdc3c7, #95a5a6);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .error-message {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-top: 8px;
            display: none;
            font-weight: 500;
        }
        .server-error-message {
            color: #e74c3c;
            background-color: #ffeaea;
            border: 1px solid #ffcccc;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .theme-switcher {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 10px 15px;
            border-radius: 50px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 10px;
            z-index: 100;
        }
        .theme-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #eee;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .theme-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .theme-btn.active {
            border: 3px solid #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.3);
        }
        #theme-light { background: linear-gradient(135deg, #ffffff, #f8f9fa); }
        #theme-dark { background: linear-gradient(135deg, #2c3e50, #34495e); }
        
        /* Тёмная тема */
        body.dark-theme {
            background-color: #1a1a2e;
        }
        body.dark-theme .login-container {
            background-color: #2d3047;
            border-color: rgba(255, 255, 255, 0.1);
        }
        body.dark-theme .login-header h2 {
            color: #ecf0f1;
        }
        body.dark-theme .login-header p {
            color: #bdc3c7;
        }
        body.dark-theme .input-group label {
            color: #ecf0f1;
        }
        body.dark-theme .input-group input {
            background-color: #3a3e5c;
            border-color: #4a5079;
            color: #ecf0f1;
        }
        body.dark-theme .input-group input:focus {
            border-color: #3498db;
            background-color: #2d3047;
        }
        
        @media (max-width: 600px) {
            .login-container { padding: 30px 25px; }
            .login-header h2 { font-size: 1.8rem; }
            .theme-switcher { top: 10px; right: 10px; padding: 8px 12px; }
            .theme-btn { width: 35px; height: 35px; }
        }
    </style>
</head>
<body>
    <div class="theme-switcher">
        <div class="theme-btn active" id="theme-light" title="Светлая тема"></div>
        <div class="theme-btn" id="theme-dark" title="Темная тема"></div>
    </div>
    
    <div class="login-container">
        <div class="login-header">
            <h2>Авторизация</h2>
            <p>Система учета протоколов стипендиальной комиссии ЮГУ</p>
        </div>
        
        @if($errors->any())
        <div class="server-error-message" id="server-error">
            {{ $errors->first() }}
        </div>
        @endif
        
        <form class="login-form" id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="input-group">
                <label for="username">Логин</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>
                <div class="error-message" id="username-error">Пожалуйста, введите логин</div>
            </div>
            
            <div class="input-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
                <div class="error-message" id="password-error">Пожалуйста, введите пароль</div>
            </div>
            
            <!-- Восстановление пароля временно отключено -->
            
            <button type="submit" class="login-button">Войти в систему</button>
        </form>
    </div>

    <script>
        // Полностью скопировать JS из login.php, но убрать проверку CSRF (Laravel делает сама)
        // Также убрать ручное управление disabled кнопки (можно оставить)
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const usernameError = document.getElementById('username-error');
            const passwordError = document.getElementById('password-error');
            const serverError = document.getElementById('server-error');
            const themeButtons = document.querySelectorAll('.theme-btn');
            const loginButton = document.querySelector('.login-button');
            
            if (serverError) {
                usernameInput.addEventListener('input', function() {
                    if (usernameInput.value.trim() !== '') {
                        serverError.style.display = 'none';
                        usernameError.style.display = 'none';
                    }
                });
                passwordInput.addEventListener('input', function() {
                    if (passwordInput.value.trim() !== '') {
                        serverError.style.display = 'none';
                        passwordError.style.display = 'none';
                    }
                });
            }
            
            themeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    themeButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    document.body.classList.remove('dark-theme');
                    if (this.id === 'theme-dark') {
                        document.body.classList.add('dark-theme');
                    }
                    localStorage.setItem('theme', this.id === 'theme-dark' ? 'dark' : 'light');
                });
            });
            
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-theme');
                themeButtons[1].classList.add('active');
                themeButtons[0].classList.remove('active');
            }
            
            function highlightField(field) {
                field.style.borderColor = '#2ecc71';
                field.style.boxShadow = '0 0 0 3px rgba(46, 204, 113, 0.3)';
                setTimeout(() => {
                    field.style.borderColor = '';
                    field.style.boxShadow = '';
                }, 1500);
            }
            
            usernameInput.addEventListener('input', function() {
                if (usernameInput.value.trim() !== '') {
                    usernameError.style.display = 'none';
                }
            });
            
            passwordInput.addEventListener('input', function() {
                if (passwordInput.value.trim() !== '') {
                    passwordError.style.display = 'none';
                }
            });
            
            loginForm.addEventListener('submit', function(event) {
                let isValid = true;
                usernameError.style.display = 'none';
                passwordError.style.display = 'none';
                
                if (usernameInput.value.trim() === '') {
                    usernameError.style.display = 'block';
                    usernameError.textContent = 'Пожалуйста, введите логин';
                    isValid = false;
                }
                
                if (passwordInput.value.trim() === '') {
                    passwordError.style.display = 'block';
                    passwordError.textContent = 'Пожалуйста, введите пароль';
                    isValid = false;
                } else if (passwordInput.value.trim().length < 3) {
                    passwordError.style.display = 'block';
                    passwordError.textContent = 'Пароль слишком короткий';
                    isValid = false;
                }
                
                if (!isValid) {
                    event.preventDefault();
                    if (usernameInput.value.trim() === '') highlightField(usernameInput);
                    if (passwordInput.value.trim() === '' || passwordInput.value.trim().length < 3) highlightField(passwordInput);
                } else {
                    loginButton.disabled = true;
                    loginButton.textContent = 'Вход...';
                    loginButton.style.cursor = 'wait';
                }
            });
            
            if (usernameInput.value === '') {
                usernameInput.focus();
            } else {
                passwordInput.focus();
            }
        });
    </script>
</body>
</html>