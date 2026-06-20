<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\UserToken;

class AuthController extends Controller
{
    // Показываем форму входа
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Обработка входа
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Защита от брутфорса (5 попыток за 5 минут)
        $key = 'login_attempts_' . $request->ip();
        $attempts = Cache::get($key, 0);
        if ($attempts >= 5) {
            return back()->withErrors(['username' => 'Слишком много неудачных попыток. Подождите 5 минут.'])->onlyInput('username');
        }

        // Ищем пользователя по логину
        $user = User::where('login', $request->username)->first();

        // Проверка пароля (сначала пробуем Hash::check, если не подходит — сравниваем как plain text для миграции)
        $passwordValid = false;
        if ($user) {
            if (Hash::needsRehash($user->password) && $request->password === $user->password) {
                // Старый plain-text пароль — хешируем и сохраняем
                $user->password = Hash::make($request->password);
                $user->save();
                $passwordValid = true;
            } elseif (Hash::check($request->password, $user->password)) {
                $passwordValid = true;
            }
        }

        if ($user && $passwordValid) {
            // Успешный вход — сбрасываем счетчик попыток
            Cache::forget($key);
            Auth::login($user, $request->filled('remember')); // remember пока не используется, но можно добавить чекбокс

            // Регистрируем время входа в сессию (как в старой системе, для проверки истечения)
            session(['login_time' => time()]);

            return redirect()->intended(route('dashboard'));
        }

        // Неудачная попытка — увеличиваем счетчик
        Cache::put($key, $attempts + 1, now()->addMinutes(5));
        return back()->withErrors(['username' => 'Неверный логин или пароль'])->onlyInput('username');
    }

    // Выход
    public function logout(Request $request)
{
    if ($user = Auth::user()) {
        // Удаляем все remember-токены пользователя (если отношение определено)
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }
    }
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
}
}