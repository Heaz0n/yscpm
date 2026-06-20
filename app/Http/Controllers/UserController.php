<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    // Все методы защищены middleware 'auth' и 'admin' в маршрутах

    public function index()
    {
        $this->checkAdmin();
        return view('users.index');
    }

    public function getUsers(Request $request)
    {
        $this->checkAdmin();
        $search = $request->input('search', '');
        $id = $request->input('id');

        if ($id) {
            $user = User::find($id);
            return response()->json(['success' => true, 'data' => $user ? [$user] : []]);
        }

        $query = User::query();
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('login', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%")
                  ->orWhere('school', 'like', "%{$search}%")
                  ->orWhere('school_short', 'like', "%{$search}%");
            });
        }
        $users = $query->orderBy('full_name')->orderBy('login')->get();
        return response()->json(['success' => true, 'data' => $users]);
    }

    public function getRoles()
    {
        $this->checkAdmin();
        $roles = [
            ['value' => 'admin', 'label' => 'Администратор системы'],
            ['value' => 'director', 'label' => 'Руководитель высшей школы'],
            ['value' => 'deputy_director', 'label' => 'Заместитель руководителя'],
            ['value' => 'secretary', 'label' => 'Секретарь комиссии'],
            ['value' => 'member', 'label' => 'Член комиссии'],
        ];
        return response()->json(['success' => true, 'data' => $roles]);
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'login' => 'required|string|unique:users,login',
            'full_name' => 'nullable|string',
            'password' => 'required|string|min:3',
            'role' => 'required|in:admin,director,deputy_director,secretary,member',
            'school' => 'nullable|string',
            'school_short' => 'nullable|string',
        ]);

        $user = User::create([
            'login' => $validated['login'],
            'full_name' => $validated['full_name'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'school' => $validated['school'],
            'school_short' => $validated['school_short'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Пользователь успешно создан',
            'user' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);

        $rules = [
            'login' => 'required|string|unique:users,login,' . $id,
            'full_name' => 'nullable|string',
            'role' => 'required|in:admin,director,deputy_director,secretary,member',
            'school' => 'nullable|string',
            'school_short' => 'nullable|string',
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:3';
        }
        $validated = $request->validate($rules);

        $data = $request->only(['login', 'full_name', 'role', 'school', 'school_short']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Пользователь успешно обновлен'
        ]);
    }

    public function destroy($id)
    {
        $this->checkAdmin();
        $user = User::findOrFail($id);

        if ($user->id == Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Вы не можете удалить самого себя'
            ], 403);
        }

        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'Пользователь успешно удален'
        ]);
    }

    private function checkAdmin()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Доступ запрещён. Требуются права администратора.');
        }
    }
}