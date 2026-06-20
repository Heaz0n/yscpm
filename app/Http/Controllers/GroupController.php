<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentFile;
use App\Models\Direction;
use App\Models\School;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class GroupController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $is_admin = $user->role === 'admin';
        $user_school_code = $user->school_code;

        $groups = [];
        if ($is_admin) {
            $groups = Group::with(['direction.school'])
                ->orderBy('group_name')
                ->get()
                ->map(function($group) {
                    return [
                        'id' => $group->id,
                        'direction_id' => $group->direction_id,
                        'group_name' => $group->group_name,
                        'notes' => $group->notes,
                        'direction_name' => $group->direction->direction_name ?? null,
                        'vsh_code' => $group->direction->vsh_code ?? null,
                        'school_name' => $group->direction->school->name ?? null,
                    ];
                });
        } else {
            $groups = Group::whereHas('direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            })
            ->with(['direction.school'])
            ->orderBy('group_name')
            ->get()
            ->map(function($group) {
                return [
                    'id' => $group->id,
                    'direction_id' => $group->direction_id,
                    'group_name' => $group->group_name,
                    'notes' => $group->notes,
                    'direction_name' => $group->direction->direction_name ?? null,
                    'vsh_code' => $group->direction->vsh_code ?? null,
                    'school_name' => $group->direction->school->name ?? null,
                ];
            });
        }

        $schools = $is_admin ? School::orderBy('name')->get() : [];
        $user_school_info = !$is_admin && $user_school_code ? School::where('code', $user_school_code)->first() : null;
        $selected_group_id = session('selected_group_id', null);

        return view('groups.index', compact(
            'groups', 'is_admin', 'user_school_code', 'schools', 'user_school_info', 'selected_group_id'
        ));
    }

    public function getData(Request $request)
    {
        $user = Auth::user();
        $is_admin = $user->role === 'admin';
        $user_school_code = $user->school_code;
        $action = $request->input('action');

        switch ($action) {
            case 'check_updates':
                return $this->checkUpdates($request, $is_admin, $user_school_code);
            case 'get_directions':
                return $this->getDirections($request, $is_admin, $user_school_code);
            case 'get_students':
                return $this->getStudents($request, $is_admin, $user_school_code);
            case 'get_groups_by_direction':
                return $this->getGroupsByDirection($request, $is_admin, $user_school_code);
            case 'get_all_groups':
                return $this->getAllGroups($is_admin, $user_school_code);
            case 'add_group':
                return $this->addGroup($request, $is_admin, $user_school_code);
            case 'edit_group':
                return $this->editGroup($request, $is_admin, $user_school_code);
            case 'delete_group':
                return $this->deleteGroup($request, $is_admin, $user_school_code);
            case 'add_student':
                return $this->addStudent($request, $is_admin, $user_school_code);
            case 'edit_student':
                return $this->editStudent($request, $is_admin, $user_school_code);
            case 'delete_student':
                return $this->deleteStudent($request, $is_admin, $user_school_code);
            case 'delete_all_students':
                return $this->deleteAllStudents($request, $is_admin, $user_school_code);
            case 'upload_students':
                return $this->uploadStudents($request, $is_admin, $user_school_code);
            case 'select_group':
                session(['selected_group_id' => $request->input('group_id')]);
                return response()->json(['type' => 'success']);
            case 'get_student_files':
                return $this->getStudentFiles($request, $is_admin, $user_school_code);
            case 'upload_student_file':
                return $this->uploadStudentFile($request, $is_admin, $user_school_code);
            case 'delete_student_file':
                return $this->deleteStudentFile($request, $is_admin, $user_school_code);
            case 'mass_update_budget':
                return $this->massUpdateBudget($request, $is_admin, $user_school_code);
            default:
                return response()->json(['type' => 'error', 'message' => 'Неизвестное действие']);
        }
    }

    private function checkUpdates($request, $is_admin, $user_school_code)
    {
        $groups = $this->getGroupsData($is_admin, $user_school_code);
        $students = [];
        if ($request->input('group_id')) {
            $students = Student::where('group_id', $request->input('group_id'))
                ->orderBy('full_name')
                ->get();
        }
        return response()->json(['type' => 'success', 'groups' => $groups, 'students' => $students]);
    }

    private function getGroupsData($is_admin, $user_school_code)
    {
        $query = Group::with(['direction.school']);
        if (!$is_admin) {
            $query->whereHas('direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            });
        }
        return $query->orderBy('group_name')->get()->map(function($group) {
            return [
                'id' => $group->id,
                'direction_id' => $group->direction_id,
                'group_name' => $group->group_name,
                'notes' => $group->notes,
                'direction_name' => $group->direction->direction_name ?? null,
                'vsh_code' => $group->direction->vsh_code ?? null,
                'school_name' => $group->direction->school->name ?? null,
            ];
        });
    }

    private function getDirections($request, $is_admin, $user_school_code)
    {
        $vsh_code = $request->input('vsh_code');
        $query = Direction::query();
        if (!$is_admin) {
            $query->where('vsh_code', $user_school_code);
        } elseif ($vsh_code) {
            $query->where('vsh_code', $vsh_code);
        }
        return response()->json($query->orderBy('direction_name')->get());
    }

    private function getStudents($request, $is_admin, $user_school_code)
    {
        $group_id = $request->input('group_id');
        if (!$group_id) {
            return response()->json([]);
        }

        if (!$is_admin) {
            $group = Group::whereHas('direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            })->where('id', $group_id)->exists();
            if (!$group) {
                return response()->json(['type' => 'error', 'message' => 'Доступ к этой группе запрещен']);
            }
        }

        $students = Student::where('group_id', $group_id)->orderBy('full_name')->get();
        return response()->json($students);
    }

    private function getGroupsByDirection($request, $is_admin, $user_school_code)
    {
        $direction_id = $request->input('direction_id');
        $query = Group::with(['direction.school'])->where('direction_id', $direction_id);
        if (!$is_admin) {
            $query->whereHas('direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            });
        }
        $groups = $query->orderBy('group_name')->get()->map(function($group) {
            return [
                'id' => $group->id,
                'direction_id' => $group->direction_id,
                'group_name' => $group->group_name,
                'notes' => $group->notes,
                'direction_name' => $group->direction->direction_name ?? null,
                'vsh_code' => $group->direction->vsh_code ?? null,
                'school_name' => $group->direction->school->name ?? null,
            ];
        });
        return response()->json(['type' => 'success', 'groups' => $groups]);
    }

    private function getAllGroups($is_admin, $user_school_code)
    {
        $groups = $this->getGroupsData($is_admin, $user_school_code);
        return response()->json(['type' => 'success', 'groups' => $groups]);
    }

    private function addGroup($request, $is_admin, $user_school_code)
    {
        $group_name = trim($request->input('group_name'));
        $direction_id = $request->input('direction_id');
        $notes = $request->input('notes');

        if (empty($group_name)) {
            return response()->json(['type' => 'error', 'message' => 'Наименование группы обязательно']);
        }

        if (!$is_admin) {
            $exists = Direction::where('code', $direction_id)->where('vsh_code', $user_school_code)->exists();
            if (!$exists) {
                return response()->json(['type' => 'error', 'message' => 'Выбранное направление не существует или недоступно для вашей школы']);
            }
        }

        $exists = Group::where('direction_id', $direction_id)->where('group_name', $group_name)->exists();
        if ($exists) {
            return response()->json(['type' => 'error', 'message' => "Группа с названием '$group_name' уже существует в этом направлении"]);
        }

        $group = Group::create([
            'direction_id' => $direction_id,
            'group_name' => $group_name,
            'notes' => $notes
        ]);

        return response()->json([
            'type' => 'success',
            'message' => "Группа '$group_name' успешно добавлена",
            'group' => $group
        ]);
    }

    private function editGroup($request, $is_admin, $user_school_code)
    {
        $id = $request->input('id');
        $group_name = trim($request->input('group_name'));
        $notes = $request->input('notes');

        if (empty($id) || empty($group_name)) {
            return response()->json(['type' => 'error', 'message' => 'ID и наименование группы обязательны']);
        }

        $group = Group::find($id);
        if (!$group) {
            return response()->json(['type' => 'error', 'message' => 'Группа не найдена']);
        }

        if (!$is_admin) {
            $exists = Group::whereHas('direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            })->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['type' => 'error', 'message' => 'Группа не найдена или недоступна для вашей школы']);
            }
        }

        $exists = Group::where('direction_id', $group->direction_id)
            ->where('group_name', $group_name)
            ->where('id', '!=', $id)
            ->exists();
        if ($exists) {
            return response()->json(['type' => 'error', 'message' => "Группа с названием '$group_name' уже существует в этом направлении"]);
        }

        $group->group_name = $group_name;
        $group->notes = $notes;
        $group->save();

        return response()->json(['type' => 'success', 'message' => "Группа '$group_name' успешно обновлена"]);
    }

    private function deleteGroup($request, $is_admin, $user_school_code)
    {
        $id = $request->input('id');
        if (empty($id)) {
            return response()->json(['type' => 'error', 'message' => 'ID группы обязателен']);
        }

        $group = Group::find($id);
        if (!$group) {
            return response()->json(['type' => 'error', 'message' => 'Группа не найдена']);
        }

        if (!$is_admin) {
            $exists = Group::whereHas('direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            })->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['type' => 'error', 'message' => 'Группа не найдена или недоступна для вашей школы']);
            }
        }

        if (Student::where('group_id', $id)->count() > 0) {
            return response()->json(['type' => 'error', 'message' => 'Нельзя удалить группу, в которой есть студенты']);
        }

        $groupName = $group->group_name;
        $group->delete();

        return response()->json(['type' => 'success', 'message' => "Группа '$groupName' успешно удалена"]);
    }

    private function addStudent($request, $is_admin, $user_school_code)
    {
        $group_id = $request->input('group_id') ?: session('selected_group_id');
        $full_name = trim($request->input('full_name'));
        $budget = $request->input('budget');
        $phone = $request->input('phone');
        $telegram = $request->input('telegram');

        if (!$group_id) {
            return response()->json(['type' => 'error', 'message' => 'ID группы обязателен']);
        }

        if (!$is_admin) {
            $exists = Group::whereHas('direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            })->where('id', $group_id)->exists();
            if (!$exists) {
                return response()->json(['type' => 'error', 'message' => 'Выбранная группа не существует или недоступна для вашей школы']);
            }
        }

        if (!empty($full_name)) {
            $exists = Student::where('group_id', $group_id)->where('full_name', $full_name)->exists();
            if ($exists) {
                return response()->json(['type' => 'error', 'message' => "Студент с ФИО '$full_name' уже существует в этой группе"]);
            }
        }

        if (!empty($budget) && !in_array($budget, ['РФ', 'ХМАО'])) {
            return response()->json(['type' => 'error', 'message' => "Недопустимое значение для поля 'Бюджет'. Допустимые значения: РФ, ХМАО"]);
        }

        $student = Student::create([
            'group_id' => $group_id,
            'full_name' => $full_name,
            'budget' => $budget,
            'phone' => $phone,
            'telegram' => $telegram
        ]);

        return response()->json([
            'type' => 'success',
            'message' => "Студент успешно добавлен",
            'group_id' => $group_id,
            'student' => $student
        ]);
    }

    private function editStudent($request, $is_admin, $user_school_code)
    {
        $id = $request->input('id');
        $full_name = trim($request->input('full_name'));
        $budget = $request->input('budget');
        $phone = $request->input('phone');
        $telegram = $request->input('telegram');

        if (empty($id)) {
            return response()->json(['type' => 'error', 'message' => 'ID студента обязателен']);
        }

        $student = Student::find($id);
        if (!$student) {
            return response()->json(['type' => 'error', 'message' => 'Студент не найден']);
        }

        if (!$is_admin) {
            $exists = Student::whereHas('group.direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            })->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['type' => 'error', 'message' => 'Студент не найден или недоступен для вашей школы']);
            }
        }

        if (!empty($full_name)) {
            $exists = Student::where('group_id', $student->group_id)
                ->where('full_name', $full_name)
                ->where('id', '!=', $id)
                ->exists();
            if ($exists) {
                return response()->json(['type' => 'error', 'message' => "Студент с ФИО '$full_name' уже существует в этой группе"]);
            }
        }

        if (!empty($budget) && !in_array($budget, ['РФ', 'ХМАО'])) {
            return response()->json(['type' => 'error', 'message' => "Недопустимое значение для поля 'Бюджет'. Допустимые значения: РФ, ХМАО"]);
        }

        $student->full_name = $full_name;
        $student->budget = $budget;
        $student->phone = $phone;
        $student->telegram = $telegram;
        $student->save();

        return response()->json([
            'type' => 'success',
            'message' => "Студент успешно обновлен",
            'group_id' => $student->group_id,
            'student' => $student
        ]);
    }

    private function deleteStudent($request, $is_admin, $user_school_code)
    {
        $id = $request->input('id');
        $group_id = $request->input('group_id');

        if (empty($id)) {
            return response()->json(['type' => 'error', 'message' => 'ID студента обязателен']);
        }

        $student = Student::find($id);
        if (!$student) {
            return response()->json(['type' => 'error', 'message' => 'Студент не найден']);
        }

        if (!$is_admin) {
            $exists = Student::whereHas('group.direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            })->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['type' => 'error', 'message' => 'Студент не найден или недоступен для вашей школы']);
            }
        }

        $studentName = $student->full_name;
        $student->delete();

        return response()->json([
            'type' => 'success',
            'message' => "Студент '$studentName' успешно удален",
            'group_id' => $group_id
        ]);
    }

    private function deleteAllStudents($request, $is_admin, $user_school_code)
    {
        $group_id = $request->input('group_id');
        if (empty($group_id)) {
            return response()->json(['type' => 'error', 'message' => 'ID группы обязателен']);
        }

        if (!$is_admin) {
            $exists = Group::whereHas('direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            })->where('id', $group_id)->exists();
            if (!$exists) {
                return response()->json(['type' => 'error', 'message' => 'Группа не существует или недоступна для вашей школы']);
            }
        }

        $count = Student::where('group_id', $group_id)->count();
        if ($count == 0) {
            return response()->json(['type' => 'error', 'message' => 'В группе нет студентов для удаления']);
        }

        Student::where('group_id', $group_id)->delete();

        return response()->json([
            'type' => 'success',
            'message' => "Все студенты группы успешно удалены",
            'group_id' => $group_id
        ]);
    }

    private function uploadStudents($request, $is_admin, $user_school_code)
{
    $group_id = $request->input('group_id') ?: session('selected_group_id');
    if (!$group_id) {
        return response()->json(['type' => 'error', 'message' => 'ID группы обязателен']);
    }

    if (!$request->hasFile('excel_file')) {
        return response()->json(['type' => 'error', 'message' => 'Файл не выбран']);
    }

    $file = $request->file('excel_file');
    if ($file->getSize() > 10 * 1024 * 1024) {
        return response()->json(['type' => 'error', 'message' => 'Файл слишком большой. Максимальный размер: 10MB']);
    }

    $ext = strtolower($file->getClientOriginalExtension());
    if (!in_array($ext, ['xls', 'xlsx'])) {
        return response()->json(['type' => 'error', 'message' => 'Недопустимый формат файла. Поддерживаются только .xls и .xlsx']);
    }

    try {
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        $fioColumn = null;
        $headerRow = 1;
        $highestColumnIndex = Coordinate::columnIndexFromString($worksheet->getHighestColumn());

        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cell = $worksheet->getCell(Coordinate::stringFromColumnIndex($col) . $headerRow);
            $header = trim($cell->getValue());
            if (mb_strtolower($header, 'UTF-8') === 'фио') {
                $fioColumn = $col;
                break;
            }
        }

        if ($fioColumn === null) {
            return response()->json(['type' => 'error', 'message' => 'Столбец с заголовком "ФИО" не найден.']);
        }

        $addedStudents = [];
        $errors = [];
        $existingStudents = Student::where('group_id', $group_id)->pluck('full_name')->toArray();

        for ($row = 2; $row <= $highestRow; $row++) {
            $cell = $worksheet->getCell(Coordinate::stringFromColumnIndex($fioColumn) . $row);
            $full_name = trim($cell->getValue());
            if (empty($full_name)) {
                continue;
            }

            if (in_array($full_name, $addedStudents)) {
                $errors[] = "Студент '$full_name' уже добавлен из этого файла";
                continue;
            }

            if (in_array($full_name, $existingStudents)) {
                $errors[] = "Студент '$full_name' уже существует в выбранной группе";
                continue;
            }

            try {
                Student::create([
                    'group_id' => $group_id,
                    'full_name' => $full_name
                ]);
                $addedStudents[] = $full_name;
                $existingStudents[] = $full_name;
            } catch (\Exception $e) {
                $errors[] = "Ошибка при добавлении студента '$full_name': " . $e->getMessage();
            }
        }

        $addedCount = count($addedStudents);
        $errorCount = count($errors);

        $message = $addedCount > 0 ? "Успешно добавлено $addedCount студентов" : "Ни один студент не был добавлен";
        $type = $addedCount > 0 ? 'success' : 'error';

        if (!empty($errors)) {
            $displayErrors = array_slice($errors, 0, 5);
            $message .= ". Ошибки: " . implode("; ", $displayErrors);
            if ($errorCount > 5) {
                $message .= " (и еще " . ($errorCount - 5) . " ошибок)";
            }
        }

        return response()->json([
            'type' => $type,
            'message' => $message,
            'group_id' => $group_id,
            'added_count' => $addedCount,
            'error_count' => $errorCount
        ]);

    } catch (\Exception $e) {
        return response()->json(['type' => 'error', 'message' => 'Ошибка обработки файла: ' . $e->getMessage()]);
    }
}

    private function getStudentFiles($request, $is_admin, $user_school_code)
    {
        $student_id = $request->input('student_id');
        if (!$student_id) {
            return response()->json(['type' => 'error', 'message' => 'ID студента не указан']);
        }

        $query = StudentFile::where('student_id', $student_id);
        if (!$is_admin) {
            $query->whereHas('student.group.direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            });
        }

        $files = $query->orderBy('uploaded_at', 'desc')->get();
        return response()->json(['type' => 'success', 'files' => $files]);
    }

    private function uploadStudentFile($request, $is_admin, $user_school_code)
    {
        $student_id = $request->input('student_id');
        if (!$student_id) {
            return response()->json(['type' => 'error', 'message' => 'ID студента не указан']);
        }

        if (!$request->hasFile('uploaded_file')) {
            return response()->json(['type' => 'error', 'message' => 'Файл не выбран']);
        }

        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];
        $file = $request->file('uploaded_file');
        $ext = strtolower($file->getClientOriginalExtension());
        $size = $file->getSize();

        if (!in_array($ext, $allowed)) {
            return response()->json(['type' => 'error', 'message' => 'Недопустимый тип файла. Разрешены: ' . implode(', ', $allowed)]);
        }

        if ($size > 10 * 1024 * 1024) {
            return response()->json(['type' => 'error', 'message' => 'Файл слишком большой. Максимальный размер: 10MB']);
        }

        $path = $file->store("uploads/students/{$student_id}", 'public');
        $fileName = $file->getClientOriginalName();

        $studentFile = StudentFile::create([
            'student_id' => $student_id,
            'file_name' => $fileName,
            'file_type' => $ext,
            'file_path' => $path,
            'file_size' => $size
        ]);

        return response()->json([
            'type' => 'success',
            'message' => 'Файл успешно загружен',
            'file_id' => $studentFile->id
        ]);
    }

    private function deleteStudentFile($request, $is_admin, $user_school_code)
    {
        $file_id = $request->input('file_id');
        if (!$file_id) {
            return response()->json(['type' => 'error', 'message' => 'ID файла не указан']);
        }

        $file = StudentFile::find($file_id);
        if (!$file) {
            return response()->json(['type' => 'error', 'message' => 'Файл не найден']);
        }

        if (!$is_admin) {
            $exists = StudentFile::whereHas('student.group.direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            })->where('id', $file_id)->exists();
            if (!$exists) {
                return response()->json(['type' => 'error', 'message' => 'Файл не найден или недоступен для вашей школы']);
            }
        }

        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();
        return response()->json(['type' => 'success', 'message' => 'Файл успешно удален']);
    }

    private function massUpdateBudget($request, $is_admin, $user_school_code)
    {
        $studentIds = json_decode($request->input('student_ids'), true);
        $budget = $request->input('budget');
        $group_id = $request->input('group_id');

        if (empty($studentIds) || !is_array($studentIds)) {
            return response()->json(['type' => 'error', 'message' => 'Не указаны студенты']);
        }

        if (!in_array($budget, ['РФ', 'ХМАО'])) {
            return response()->json(['type' => 'error', 'message' => 'Некорректное значение бюджета']);
        }

        if (!$group_id) {
            return response()->json(['type' => 'error', 'message' => 'Не указана группа']);
        }

        if (!$is_admin) {
            $exists = Group::whereHas('direction', function($q) use ($user_school_code) {
                $q->where('vsh_code', $user_school_code);
            })->where('id', $group_id)->exists();
            if (!$exists) {
                return response()->json(['type' => 'error', 'message' => 'Доступ к группе запрещён']);
            }
        }

        $count = Student::whereIn('id', $studentIds)->update(['budget' => $budget]);

        return response()->json([
            'type' => 'success',
            'message' => 'Бюджет успешно обновлён для ' . $count . ' студента(ов)'
        ]);
    }
}