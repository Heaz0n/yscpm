<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Group;
use App\Models\Category;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\StudentCategory;
use App\Models\StudentReason;
use App\Models\GeneratedProtocol;
use App\Models\TemplateVariable;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class StudentListController extends Controller
{
    /**
     * Показать страницу со списком студентов и фильтрами.
     */
    public function index(Request $request)
    {
        // Данные для фильтров и выпадающих списков
        $schools = School::orderBy('name')->get();
        $years = AcademicYear::orderBy('year', 'desc')->pluck('year')->toArray();
        $categories = Category::orderBy('number')->get();
        $groups = Group::orderBy('group_name')->get();

        // Выбранный учебный год (из сессии или GET)
        $selectedYear = $request->session()->get('selected_year', $years[0] ?? null);
        if ($request->has('year')) {
            $selectedYear = $request->input('year');
            $request->session()->put('selected_year', $selectedYear);
        }

        // Роль и школа текущего пользователя
        $user = auth()->user();
        $currentUserRole = $user->role;
        $currentUserSchool = $user->school_code;

        // Количество выплат (из настроек)
        $payoutCount = Setting::where('setting_key', 'payout_count')->value('setting_value') ?? 4;

        return view('students.list', compact(
            'schools', 'years', 'categories', 'groups',
            'selectedYear', 'currentUserRole', 'currentUserSchool', 'payoutCount'
        ));
    }

    /**
     * Обработка всех AJAX-действий.
     */
    public function action(Request $request)
    {
        $action = $request->input('action');
        $user = auth()->user();

        switch ($action) {
            case 'get_students':
                return $this->getStudents($request, $user);
            case 'save_reason':
                return $this->saveReason($request, $user);
            case 'remove_reason':
                return $this->removeReason($request, $user);
            case 'update_student':
                return $this->updateStudent($request, $user);
            case 'add_student_category':
                return $this->addStudentCategory($request, $user);
            case 'add_students_category':
                return $this->addStudentsCategory($request, $user);
            case 'generate_protocol':
                return $this->generateProtocol($request, $user);
            case 'reset_reasons':
                return $this->resetReasons($request, $user);
            case 'get_all_students':
                return $this->getAllStudents($request, $user);
            case 'delete_student':
                return $this->deleteStudent($request, $user);
            case 'update_payout_count':
                return $this->updatePayoutCount($request, $user);
            case 'add_academic_year':
                return $this->addAcademicYear($request, $user);
            default:
                return response()->json(['status' => 'error', 'message' => 'Неизвестное действие']);
        }
    }

    // ---- Вспомогательные методы ----

    /**
     * Получить студентов с фильтрами.
     */
    private function getStudents(Request $request, $user)
    {
        $schools = json_decode($request->input('schools', '[]'), true);
        $years = json_decode($request->input('years', '[]'), true);
        $regions = json_decode($request->input('regions', '[]'), true);
        $fio = trim($request->input('fio', ''));

        // Если не админ – ограничиваем своей школой
        if ($user->role !== 'admin' && $user->school_code) {
            $schools = [$user->school_code];
        }

        $query = Student::with(['group.direction.school', 'categories', 'reasons'])
            ->join('Groups as g', 'Students.group_id', '=', 'g.id')
            ->join('Directions as d', 'g.direction_id', '=', 'd.code')
            ->join('Schools as sc', 'd.vsh_code', '=', 'sc.code')
            ->select('Students.*', 'g.group_name', 'd.direction_name');

        if (!empty($schools) && !in_array('', $schools)) {
            $query->whereIn('sc.code', $schools);
        }
        if (!empty($years) && !in_array('', $years)) {
            $query->whereHas('categories', function ($q) use ($years) {
                $q->whereIn('academic_year', $years);
            });
        }
        if (!empty($regions) && !in_array('none', $regions)) {
            $query->whereIn('budget', $regions);
        } elseif (in_array('none', $regions)) {
            $query->whereNull('budget');
        }
        if (!empty($fio)) {
            $query->where('Students.full_name', 'like', "%$fio%");
        }

        $students = $query->orderBy('Students.full_name')->get();

        // Формируем результат как в старом коде
        $result = [];
        foreach ($students as $student) {
            $reasons = [];
            foreach ($student->reasons as $reason) {
                $reasons[$reason->month] = [
                    'reason' => $this->getCategoryName($reason->category_id),
                    'amount' => $reason->amount,
                    'category_id' => $reason->category_id,
                ];
            }
            $result[] = [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'budget' => $student->budget ?? '-',
                'group_id' => $student->group_id,
                'group_name' => $student->group_name,
                'direction_name' => $student->direction_name,
                'reasons' => $reasons,
                'academic_year' => $student->categories->first()->pivot->academic_year ?? null,
            ];
        }

        return response()->json(['status' => 'success', 'data' => $result, 'total' => count($result)]);
    }

    /**
     * Получить название категории без номера.
     */
    private function getCategoryName($categoryId)
    {
        $category = Category::find($categoryId);
        if (!$category) return '';
        return preg_replace('/^' . preg_quote($category->number, '/') . '\s*[-–—:]\s*/', '', $category->category_name);
    }

    /**
     * Сохранить основание (выплату) для студента.
     */
    private function saveReason(Request $request, $user)
    {
        $studentId = $request->input('student_id');
        $month = $request->input('month');
        $categoryId = $request->input('category_id');
        $amount = $request->input('amount');
        $academicYear = $request->input('academic_year', $this->getMostRecentAcademicYear());

        if (!$studentId || !$month || !$categoryId || $amount === null || !$academicYear) {
            return response()->json(['status' => 'error', 'message' => 'Неверные входные данные']);
        }

        // Проверка доступа (если не админ – проверяем принадлежность студента к школе пользователя)
        if ($user->role !== 'admin') {
            $student = Student::with('group.direction.school')->find($studentId);
            if (!$student || $student->group->direction->school->code != $user->school_code) {
                return response()->json(['status' => 'error', 'message' => 'Доступ запрещен']);
            }
        }

        // Проверка лимитов (как в старом коде)
        $category = Category::find($categoryId);
        if (!$category) {
            return response()->json(['status' => 'error', 'message' => 'Категория не найдена']);
        }

        // Логика проверок (упрощённо – можно вынести в сервис)
        // Проверяем, нет ли уже другой категории в этом полугодии (кроме исключений 4,12)
        $semester = ($month >= 9 && $month <= 12) ? 1 : 2;
        list($startYear, $endYear) = explode('/', $academicYear);
        $calendarYear = ($month >= 9 && $month <= 12) ? $startYear : $endYear;

        // Проверка на существующую запись
        $existing = StudentReason::where('student_id', $studentId)
            ->where('month', $month)
            ->where('academic_year', $academicYear)
            ->first();

        if (!$existing) {
            // Проверка: только одна категория в полугодии (кроме исключений)
            if (!in_array($categoryId, [4, 12])) {
                $otherCount = StudentReason::where('student_id', $studentId)
                    ->where('academic_year', $academicYear)
                    ->where('semester', $semester)
                    ->whereNotIn('category_id', [4, 12])
                    ->where('category_id', '!=', $categoryId)
                    ->count();
                if ($otherCount > 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Студент уже имеет материальную поддержку по другой категории в этом полугодии.'
                    ]);
                }
            }

            // Проверка частоты выплат
            $paymentFrequency = $category->payment_frequency;
            if (strpos($paymentFrequency, '2 раза') !== false) {
                $count = StudentReason::where('student_id', $studentId)
                    ->where('category_id', $categoryId)
                    ->where('academic_year', $academicYear)
                    ->count();
                if ($count >= 2) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Данная категория предусматривает не более 2 выплат в учебный год.'
                    ]);
                }
            }

            // Запрет выплат два месяца подряд
            $adjacentMonths = [];
            if ($month > 1) $adjacentMonths[] = $month - 1;
            if ($month < 12) $adjacentMonths[] = $month + 1;
            if ($month == 12) $adjacentMonths[] = 1;
            if ($month == 1) $adjacentMonths[] = 12;
            if (!empty($adjacentMonths)) {
                $existsAdj = StudentReason::where('student_id', $studentId)
                    ->whereIn('month', $adjacentMonths)
                    ->where('academic_year', $academicYear)
                    ->exists();
                if ($existsAdj) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Нельзя назначать выплаты в два месяца подряд (месяц через месяц).'
                    ]);
                }
            }
        }

        // Сохраняем или обновляем
        StudentReason::updateOrCreate(
            [
                'student_id' => $studentId,
                'month' => $month,
                'academic_year' => $academicYear,
            ],
            [
                'category_id' => $categoryId,
                'amount' => $amount,
                'semester' => $semester,
                'year' => $calendarYear,
            ]
        );

        return response()->json(['status' => 'success']);
    }

    /**
     * Удалить основание.
     */
    private function removeReason(Request $request, $user)
    {
        $studentId = $request->input('student_id');
        $month = $request->input('month');
        $academicYear = $request->input('academic_year', $this->getMostRecentAcademicYear());

        if (!$studentId || !$month || !$academicYear) {
            return response()->json(['status' => 'error', 'message' => 'Неверные данные']);
        }

        // Проверка доступа
        if ($user->role !== 'admin') {
            $student = Student::with('group.direction.school')->find($studentId);
            if (!$student || $student->group->direction->school->code != $user->school_code) {
                return response()->json(['status' => 'error', 'message' => 'Доступ запрещен']);
            }
        }

        StudentReason::where('student_id', $studentId)
            ->where('month', $month)
            ->where('academic_year', $academicYear)
            ->delete();

        return response()->json(['status' => 'success']);
    }

    /**
     * Обновить поле студента (ФИО, группа, бюджет).
     */
    private function updateStudent(Request $request, $user)
    {
        $studentId = $request->input('student_id');
        $field = $request->input('field');
        $value = $request->input('value');

        if (!$studentId || !in_array($field, ['full_name', 'group_id', 'budget'])) {
            return response()->json(['status' => 'error', 'message' => 'Неверные данные']);
        }

        // Проверка доступа
        if ($user->role !== 'admin') {
            $student = Student::with('group.direction.school')->find($studentId);
            if (!$student || $student->group->direction->school->code != $user->school_code) {
                return response()->json(['status' => 'error', 'message' => 'Доступ запрещен']);
            }
        }

        $student = Student::find($studentId);
        if ($field === 'group_id') {
            $student->group_id = $value;
        } else {
            $student->$field = $value;
        }
        $student->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * Добавить категорию одному студенту (из модального окна добавления).
     */
    private function addStudentCategory(Request $request, $user)
    {
        $studentId = $request->input('student_id');
        $categoryId = $request->input('category_id');
        $academicYear = $request->input('academic_year', $this->getMostRecentAcademicYear());

        if (!$studentId || !$academicYear) {
            return response()->json(['status' => 'error', 'message' => 'Неверные данные']);
        }

        // Если категория не указана – берём первую
        if (!$categoryId) {
            $categoryId = Category::first()->id ?? null;
            if (!$categoryId) {
                return response()->json(['status' => 'error', 'message' => 'Нет доступных категорий']);
            }
        }

        // Проверка доступа
        if ($user->role !== 'admin') {
            $student = Student::with('group.direction.school')->find($studentId);
            if (!$student || $student->group->direction->school->code != $user->school_code) {
                return response()->json(['status' => 'error', 'message' => 'Доступ запрещен']);
            }
        }

        StudentCategory::updateOrCreate(
            [
                'student_id' => $studentId,
                'academic_year' => $academicYear,
            ],
            ['category_id' => $categoryId]
        );

        return response()->json(['status' => 'success']);
    }

    /**
     * Добавить категорию нескольким студентам.
     */
    private function addStudentsCategory(Request $request, $user)
    {
        $studentIds = json_decode($request->input('student_ids', '[]'));
        $categoryId = $request->input('category_id');
        $academicYear = $request->input('academic_year', $this->getMostRecentAcademicYear());

        if (empty($studentIds) || !$categoryId || !$academicYear) {
            return response()->json(['status' => 'error', 'message' => 'Неверные данные']);
        }

        $added = [];
        $errors = [];
        foreach ($studentIds as $studentId) {
            // Проверка доступа
            if ($user->role !== 'admin') {
                $student = Student::with('group.direction.school')->find($studentId);
                if (!$student || $student->group->direction->school->code != $user->school_code) {
                    $errors[] = "Студент ID $studentId: доступ запрещен";
                    continue;
                }
            }

            try {
                StudentCategory::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'academic_year' => $academicYear,
                    ],
                    ['category_id' => $categoryId]
                );
                $added[] = $studentId;
            } catch (\Exception $e) {
                $errors[] = "Студент ID $studentId: " . $e->getMessage();
            }
        }

        return response()->json([
            'status' => 'success',
            'added' => $added,
            'errors' => $errors,
            'message' => 'Добавлено студентов: ' . count($added) . (count($errors) ? '. Ошибки: ' . implode('; ', $errors) : '')
        ]);
    }

    /**
     * Генерация протокола (Word).
     */
    private function generateProtocol(Request $request, $user)
    {
        $protocolNumber = $request->input('protocol_number');
        $monthNum = $request->input('month');
        $day = $request->input('day', 1);
        $academicYear = $request->input('academic_year', $this->getMostRecentAcademicYear());
        $schoolCode = $request->input('school_code');

        if (!$protocolNumber || !$monthNum || !$academicYear || !$schoolCode) {
            return response()->json(['status' => 'error', 'message' => 'Недостаточно данных']);
        }

        // Проверка доступа к школе
        if ($user->role !== 'admin' && $schoolCode != $user->school_code) {
            return response()->json(['status' => 'error', 'message' => 'Доступ запрещен']);
        }

        // Получаем студентов с выплатами за указанный месяц и год
        $students = Student::join('Groups as g', 'Students.group_id', '=', 'g.id')
            ->join('Directions as d', 'g.direction_id', '=', 'd.code')
            ->join('Schools as sc', 'd.vsh_code', '=', 'sc.code')
            ->join('StudentReasons as sr', 'Students.id', '=', 'sr.student_id')
            ->join('categories as c', 'sr.category_id', '=', 'c.id')
            ->where('sr.month', $monthNum)
            ->where('sr.academic_year', $academicYear)
            ->where('sc.code', $schoolCode)
            ->orderBy('Students.full_name')
            ->get(['Students.*', 'g.group_name', 'sr.amount', 'c.number', 'c.category_short', 'c.category_name']);

        if ($students->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Нет студентов с выплатами за указанный месяц']);
        }

        // Данные для шаблона
        $monthNames = [
            1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
            5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
            9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
        ];
        $monthName = $monthNames[$monthNum];

        // Переменные шаблона из TemplateVariables
        $vars = TemplateVariable::where('school_code', $schoolCode)
            ->where('academic_year', $academicYear)
            ->pluck('value', 'placeholder')
            ->toArray();

        $metadata = [
            'protocol_number' => $protocolNumber,
            'school' => $vars['SCHOOL'] ?? '',
            'university' => $vars['UNIVERSITY'] ?? '',
            'city' => $vars['CITY'] ?? '',
            'chairperson' => $vars['CHAIRPERSON'] ?? '',
            'members' => $vars['MEMBERS'] ?? '',
            'secretary' => $vars['SECRETARY'] ?? '',
            'chair_degree' => $vars['CHAIR_DEGREE'] ?? '',
            'secretary_degree' => $vars['SECRETARY_DEGREE'] ?? '',
            'agenda' => $vars['AGENDA'] ?? '',
            'listened' => $vars['LISTENED'] ?? '',
            'decision' => $vars['DECISION'] ?? '',
        ];

        // Формируем массив данных для документа
        $documentData = [];
        foreach ($students as $s) {
            $reasonWithNumber = trim($s->number . ' ' . ($s->category_short ?: $s->category_name));
            $documentData[] = [
                'full_name' => $s->full_name,
                'budget' => $s->budget ?? '-',
                'group_name' => $s->group_name,
                'reason' => $reasonWithNumber,
                'amount' => $s->amount,
            ];
        }

        // Генерация Word
        try {
            $fileContent = $this->generateWordDocument($documentData, $monthName, $day, $academicYear, $metadata);

            // Сохраняем протокол в БД
            $protocol = GeneratedProtocol::create([
                'protocol_number' => $protocolNumber,
                'month' => $monthName,
                'academic_year' => $academicYear,
                'school_code' => $schoolCode,
                'student_snapshot' => $students->toArray(),
                'file_name' => 'protocol_' . $protocolNumber . '_' . $monthName . '.docx',
                'file_content' => $fileContent,
            ]);

            return response()->json([
                'status' => 'success',
                'file' => base64_encode($fileContent),
                'filename' => $protocol->file_name,
                'contentType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Ошибка генерации: ' . $e->getMessage()]);
        }
    }

    /**
     * Генерация Word-документа с помощью PhpWord.
     */
    private function generateWordDocument($students, $monthName, $day, $academicYear, $metadata)
    {
        $templatePath = storage_path('app/templates/Protocol_template.docx');
        if (!file_exists($templatePath)) {
            throw new \Exception('Шаблон документа не найден: ' . $templatePath);
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // Заполнение переменных
        $templateProcessor->setValue('PROTOCOL_NUMBER', htmlspecialchars($metadata['protocol_number']));
        $templateProcessor->setValue('SCHOOL', htmlspecialchars($metadata['school']));
        $templateProcessor->setValue('UNIVERSITY', htmlspecialchars($metadata['university']));
        $templateProcessor->setValue('DATE', htmlspecialchars("«{$day}» {$monthName} " . explode('/', $academicYear)[0]));
        $templateProcessor->setValue('CITY', htmlspecialchars($metadata['city']));
        $templateProcessor->setValue('CHAIRPERSON', htmlspecialchars($metadata['chairperson']));
        $templateProcessor->setValue('MEMBERS', htmlspecialchars($metadata['members']));
        $templateProcessor->setValue('SECRETARY', htmlspecialchars($metadata['secretary']));
        $templateProcessor->setValue('AGENDA', htmlspecialchars($metadata['agenda']));
        $templateProcessor->setValue('LISTENED', htmlspecialchars($metadata['listened']));
        $templateProcessor->setValue('DECISION', htmlspecialchars($metadata['decision']));
        $templateProcessor->setValue('CHAIR_DEGREE', htmlspecialchars($metadata['chair_degree']));
        $templateProcessor->setValue('SIGN_CHAIR', htmlspecialchars($metadata['chairperson']));
        $templateProcessor->setValue('SECRETARY_DEGREE', htmlspecialchars($metadata['secretary_degree']));
        $templateProcessor->setValue('SIGN_SECRETARY', htmlspecialchars($metadata['secretary']));

        // Разбиваем студентов на РФ и ХМАО
        $rfStudents = [];
        $hmaoStudents = [];
        foreach ($students as $student) {
            $budget = strtoupper(trim($student['budget']));
            if (in_array($budget, ['ХМАО', 'XMAO', 'HMAC', 'ХМАО-ЮГРА', 'ЮГРА'])) {
                $hmaoStudents[] = $student;
            } else {
                $rfStudents[] = $student;
            }
        }

        // Заполнение таблицы РФ
        if (!empty($rfStudents)) {
            $rowDataRF = [];
            foreach ($rfStudents as $index => $s) {
                $rowDataRF[] = [
                    'NumberRF' => $index + 1,
                    'ФИО_RF' => htmlspecialchars($s['full_name']),
                    'Бюджет_RF' => htmlspecialchars($s['budget'] ?? 'РФ'),
                    'Группа_RF' => htmlspecialchars($s['group_name']),
                    'Основание_RF' => htmlspecialchars($s['reason']),
                    'Сумма_RF' => htmlspecialchars($s['amount'] ?? '0.00'),
                ];
            }
            $templateProcessor->cloneRowAndSetValues('NumberRF', $rowDataRF);
        } else {
            $templateProcessor->setValue('NumberRF', '1');
            $templateProcessor->setValue('ФИО_RF', 'Нет данных');
            $templateProcessor->setValue('Бюджет_RF', '-');
            $templateProcessor->setValue('Группа_RF', '-');
            $templateProcessor->setValue('Основание_RF', 'Нет студентов');
            $templateProcessor->setValue('Сумма_RF', '0');
        }

        // Заполнение таблицы ХМАО
        if (!empty($hmaoStudents)) {
            $rowDataHMAO = [];
            foreach ($hmaoStudents as $index => $s) {
                $rowDataHMAO[] = [
                    'NumberHMAO' => $index + 1,
                    'ФИО_HMAO' => htmlspecialchars($s['full_name']),
                    'Бюджет_HMAO' => htmlspecialchars($s['budget'] ?? 'ХМАО'),
                    'Группа_HMAO' => htmlspecialchars($s['group_name']),
                    'Основание_HMAO' => htmlspecialchars($s['reason']),
                    'Сумма_HMAO' => htmlspecialchars($s['amount'] ?? '0.00'),
                ];
            }
            $templateProcessor->cloneRowAndSetValues('NumberHMAO', $rowDataHMAO);
        } else {
            $templateProcessor->setValue('NumberHMAO', '1');
            $templateProcessor->setValue('ФИО_HMAO', 'Нет данных');
            $templateProcessor->setValue('Бюджет_HMAO', '-');
            $templateProcessor->setValue('Группа_HMAO', '-');
            $templateProcessor->setValue('Основание_HMAO', 'Нет студентов');
            $templateProcessor->setValue('Сумма_HMAO', '0');
        }

        // Сохраняем во временный файл и читаем содержимое
        $tempFile = tempnam(sys_get_temp_dir(), 'protocol') . '.docx';
        $templateProcessor->saveAs($tempFile);
        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    /**
     * Сброс всех оснований и категорий.
     */
    private function resetReasons(Request $request, $user)
    {
        if ($user->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Доступ запрещен']);
        }

        DB::transaction(function () {
            StudentReason::truncate();
            StudentCategory::truncate();
            Student::query()->update([
                'application_text' => null,
                'application_file_path' => null,
                'application_date' => null,
                'document_paths' => null,
                'documents_info' => null,
                'status' => 'pending',
                'status_updated_at' => null,
                'notes' => null,
            ]);
        });

        return response()->json(['status' => 'success']);
    }

    /**
     * Получить список всех студентов (для модального окна добавления).
     */
    private function getAllStudents(Request $request, $user)
    {
        $fio = trim($request->input('fio', ''));
        $query = Student::with('group')
            ->select('id', 'full_name', 'budget', 'group_id');

        if ($fio) {
            $query->where('full_name', 'like', "%$fio%");
        }

        $students = $query->orderBy('full_name')->limit(50)->get();

        $data = $students->map(function ($s) {
            return [
                'id' => $s->id,
                'full_name' => $s->full_name,
                'budget' => $s->budget ?? '-',
                'group_name' => $s->group->group_name ?? '',
            ];
        });

        return response()->json(['status' => 'success', 'data' => $data, 'total' => count($data)]);
    }

    /**
     * Удалить студента со всеми связанными данными.
     */
    private function deleteStudent(Request $request, $user)
    {
        $studentId = $request->input('student_id');
        if (!$studentId) {
            return response()->json(['status' => 'error', 'message' => 'Неверный идентификатор']);
        }

        $student = Student::find($studentId);
        if (!$student) {
            return response()->json(['status' => 'error', 'message' => 'Студент не найден']);
        }

        // Проверка доступа
        if ($user->role !== 'admin') {
            $schoolCode = $student->group->direction->school->code ?? null;
            if ($schoolCode != $user->school_code) {
                return response()->json(['status' => 'error', 'message' => 'Доступ запрещен']);
            }
        }

        DB::transaction(function () use ($student) {
            $student->reasons()->delete();
            $student->categories()->detach();
            $student->delete();
        });

        return response()->json(['status' => 'success']);
    }

    /**
     * Обновить количество выплат.
     */
    private function updatePayoutCount(Request $request, $user)
    {
        if (!in_array($user->role, ['admin', 'director', 'member'])) {
            return response()->json(['status' => 'error', 'message' => 'Доступ запрещен']);
        }

        $newCount = $request->input('payout_count');
        if (!is_numeric($newCount) || $newCount < 2 || $newCount > 4) {
            return response()->json(['status' => 'error', 'message' => 'Некорректное значение. Допустимо 2, 3 или 4.']);
        }

        Setting::updateOrCreate(
            ['setting_key' => 'payout_count'],
            ['setting_value' => $newCount]
        );

        return response()->json(['status' => 'success']);
    }

    /**
     * Добавить учебный год.
     */
    private function addAcademicYear(Request $request, $user)
    {
        if (!in_array($user->role, ['admin', 'director', 'member'])) {
            return response()->json(['status' => 'error', 'message' => 'Доступ запрещен']);
        }

        $year = $request->input('year');
        if (!preg_match('/^\d{4}\/\d{4}$/', $year)) {
            return response()->json(['status' => 'error', 'message' => 'Неверный формат года']);
        }
        list($start, $end) = explode('/', $year);
        if ((int)$end !== (int)$start + 1) {
            return response()->json(['status' => 'error', 'message' => 'Годы должны быть последовательными']);
        }

        AcademicYear::firstOrCreate(['year' => $year]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Получить последний учебный год.
     */
    private function getMostRecentAcademicYear()
    {
        $year = AcademicYear::orderBy('year', 'desc')->first();
        return $year ? $year->year : null;
    }
}