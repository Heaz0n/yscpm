<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roles = [
            'admin' => 'Администратор системы',
            'director' => 'Руководитель высшей школы',
            'deputy_director' => 'Заместитель руководителя',
            'secretary' => 'Секретарь комиссии',
            'member' => 'Член комиссии'
        ];

        $initials = '';
        if ($user->full_name) {
            $parts = explode(' ', trim($user->full_name));
            if (count($parts) >= 2) {
                $initials = mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1);
            } else {
                $initials = mb_substr($parts[0], 0, 2);
            }
            $initials = mb_strtoupper($initials);
        } else {
            $initials = '??';
        }

        $theme = session('theme', 'light');
        $email_notify = session('email_notify', true);
        $desktop_notify = session('desktop_notify', true);

        return view('profile-settings', compact('user', 'roles', 'initials', 'theme', 'email_notify', 'desktop_notify'));
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'login' => 'required|email|max:50|unique:users,login,' . $user->id,
            'phone' => 'nullable|string|max:50|regex:/^[0-9+\-\s()]+$/',
        ]);

        $user->full_name = $request->full_name;
        $user->login = $request->login;
        $user->phone = $request->phone;
        $user->save();

        session(['full_name' => $user->full_name]);

        return redirect()->route('profile-settings.index')->with('notification', ['type' => 'success', 'message' => 'Профиль успешно обновлён']);
    }

    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:3|different:current_password',
            'confirm_password' => 'required|same:new_password',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Неверный текущий пароль'])->withInput();
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('profile-settings.index')->with('notification', ['type' => 'success', 'message' => 'Пароль успешно изменён']);
    }

    public function uploadAvatar(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,gif,webp|max:2048',
        ]);

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        return redirect()->route('profile-settings.index')->with('notification', ['type' => 'success', 'message' => 'Аватар успешно загружен']);
    }

    public function deleteAvatar()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->avatar = null;
        $user->save();

        return redirect()->route('profile-settings.index')->with('notification', ['type' => 'success', 'message' => 'Аватар удалён']);
    }

    public function logoutSessions()
    {
        $userId = Auth::id();
        $currentSessionId = session()->getId();

        DB::table('user_sessions')
            ->where('user_id', $userId)
            ->where('session_id', '!=', $currentSessionId)
            ->delete();

        return redirect()->route('profile-settings.index')->with('notification', ['type' => 'success', 'message' => 'Все другие сеансы успешно завершены']);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'theme' => 'in:light,dark,auto',
            'email_notifications' => 'nullable|boolean',
            'desktop_notifications' => 'nullable|boolean',
        ]);

        session(['theme' => $request->theme ?? 'light']);
        session(['email_notify' => $request->has('email_notifications')]);
        session(['desktop_notify' => $request->has('desktop_notifications')]);

        return redirect()->route('profile-settings.index')->with('notification', ['type' => 'success', 'message' => 'Настройки сохранены']);
    }
}