<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\School;
use App\Models\Direction;

class SchoolController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->role ?? '';
        $userSchoolCode = $user->school_code ?? null;
        $isAdmin = ($userRole === 'admin');

        if ($isAdmin) {
            $schools = School::orderBy('code')->get();
            $directions = Direction::with('school')->orderBy('code')->get();
            $userSchool = null;
        } else {
            $userSchool = $userSchoolCode ? School::where('code', $userSchoolCode)->first() : null;
            $schools = $userSchool ? collect([$userSchool]) : collect();
            $directions = $userSchoolCode ? Direction::where('vsh_code', $userSchoolCode)->orderBy('code')->get() : collect();
        }

        $showSchoolModal = $isAdmin || ($userSchool && in_array($userRole, ['director', 'deputy_director']));
        $showDirectionModal = $isAdmin || ($userSchool && in_array($userRole, ['director', 'deputy_director', 'secretary']));

        return view('schools.index', compact(
            'schools', 'directions', 'isAdmin', 'userSchool', 'userRole', 'userSchoolCode',
            'showSchoolModal', 'showDirectionModal'
        ));
    }

    public function storeSchool(Request $request)
    {
        $this->authorizeSchoolAction($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:Schools,name',
            'abbreviation' => 'nullable|string|max:50',
            'director' => 'nullable|string|max:100',
            'deputy_director' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $school = School::create($validated);
        return redirect()->route('schools.index')->with('notification', ['type' => 'success', 'message' => "Школа '{$school->name}' успешно добавлена"]);
    }

    public function updateSchool(Request $request)
    {
        $this->authorizeSchoolAction($request);

        $school = School::findOrFail($request->code);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:Schools,name,' . $school->code . ',code',
            'abbreviation' => 'nullable|string|max:50',
            'director' => 'nullable|string|max:100',
            'deputy_director' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $school->update($validated);
        return redirect()->route('schools.index')->with('notification', ['type' => 'success', 'message' => "Школа '{$school->name}' успешно обновлена"]);
    }

    public function deleteSchool(Request $request)
    {
        $school = School::findOrFail($request->code);

        $user = Auth::user();
        if ($user->role !== 'admin' && !($user->role === 'director' && $user->school_code == $school->code)) {
            return redirect()->route('schools.index')->with('notification', ['type' => 'error', 'message' => 'У вас нет прав для удаления школы']);
        }

        if ($school->directions()->count() > 0) {
            return redirect()->route('schools.index')->with('notification', ['type' => 'error', 'message' => 'Невозможно удалить школу, так как существуют связанные направления']);
        }

        $schoolName = $school->name;
        $school->delete();
        return redirect()->route('schools.index')->with('notification', ['type' => 'success', 'message' => "Школа '$schoolName' успешно удалена"]);
    }

    public function storeDirection(Request $request)
    {
        $this->authorizeDirectionAction($request);

        $validated = $request->validate([
            'vsh_code' => 'required|exists:Schools,code',
            'direction_name' => 'required|string|max:255',
            'level' => 'nullable|in:Бакалавриат,Магистратура,Аспирантура',
            'notes' => 'nullable|string',
        ]);

        $direction = Direction::create($validated);
        return redirect()->route('schools.index')->with('notification', ['type' => 'success', 'message' => "Направление '{$direction->direction_name}' успешно добавлено"]);
    }

    public function updateDirection(Request $request)
    {
        $this->authorizeDirectionAction($request);

        $direction = Direction::findOrFail($request->code);

        $validated = $request->validate([
            'vsh_code' => 'required|exists:Schools,code',
            'direction_name' => 'required|string|max:255',
            'level' => 'nullable|in:Бакалавриат,Магистратура,Аспирантура',
            'notes' => 'nullable|string',
        ]);

        $direction->update($validated);
        return redirect()->route('schools.index')->with('notification', ['type' => 'success', 'message' => "Направление '{$direction->direction_name}' успешно обновлено"]);
    }

    public function deleteDirection(Request $request)
    {
        $direction = Direction::findOrFail($request->code);

        $user = Auth::user();
        if ($user->role !== 'admin' && !($user->school_code == $direction->vsh_code && in_array($user->role, ['director', 'deputy_director']))) {
            return redirect()->route('schools.index')->with('notification', ['type' => 'error', 'message' => 'У вас нет прав для удаления направления']);
        }

        $directionName = $direction->direction_name;
        $direction->delete();
        return redirect()->route('schools.index')->with('notification', ['type' => 'success', 'message' => "Направление '$directionName' успешно удалено"]);
    }

    private function authorizeSchoolAction(Request $request)
    {
        $user = Auth::user();
        $isAdmin = ($user->role === 'admin');

        if (!$isAdmin && !in_array($user->role, ['director', 'deputy_director'])) {
            abort(403, 'У вас нет прав для этого действия');
        }

        if (!$isAdmin && isset($request->code) && $request->code != $user->school_code) {
            abort(403, 'Вы можете редактировать только свою школу');
        }
    }

    private function authorizeDirectionAction(Request $request)
    {
        $user = Auth::user();
        $isAdmin = ($user->role === 'admin');

        if (!$isAdmin && !in_array($user->role, ['director', 'deputy_director', 'secretary'])) {
            abort(403, 'У вас нет прав для этого действия');
        }

        if (!$isAdmin && isset($request->vsh_code) && $request->vsh_code != $user->school_code) {
            abort(403, 'Вы можете добавлять направления только для своей школы');
        }

        if (!$isAdmin && isset($request->code)) {
            $direction = Direction::find($request->code);
            if ($direction && $direction->vsh_code != $user->school_code) {
                abort(403, 'Вы можете редактировать только направления своей школы');
            }
        }
    }
}