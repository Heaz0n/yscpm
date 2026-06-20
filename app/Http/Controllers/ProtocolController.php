<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\School;
use App\Models\Category;
use App\Models\Student;
use App\Models\Group;
use App\Models\Direction;
use App\Models\StudentReason;
use App\Models\GeneratedProtocol;
use App\Models\TemplateVariable;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;

class ProtocolController extends Controller
{
    // Вспомогательные функции

    public static function extractYearStatic($academicYear, $part = 'second')
    {
        if (preg_match('/^\d{4}\/\d{4}$/', $academicYear)) {
            $years = explode('/', $academicYear);
            return $part === 'first' ? $years[0] : $years[1];
        }
        return $academicYear;
    }

    private function escapeLatex($string)
    {
        $map = [
            '&' => '\&',
            '%' => '\%',
            '$' => '\$',
            '#' => '\#',
            '_' => '\_',
            '{' => '\{',
            '}' => '\}',
            '~' => '\textasciitilde{}',
            '^' => '\textasciicircum{}',
            '\\' => '\textbackslash{}'
        ];
        return str_replace(
            array_keys($map),
            array_values($map),
            htmlspecialchars($string, ENT_QUOTES, 'UTF-8')
        );
    }

    private function formatAmount($amount, $condition, $max_amount = null)
    {
        if ($condition === 'expense_limit') {
            $max = number_format($max_amount ?? 0, 2, '.', ' ');
            return "В объёме затрат, но не более {$max} руб.";
        } else {
            return number_format($amount, 2, '.', ' ');
        }
    }

    private function getDefaultTemplateVariables($school_code = 1, $academic_year = '2024/2025')
    {
        $year = self::extractYearStatic($academic_year, 'second');
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;
        $defaultAcademicYear = "{$currentYear}/{$nextYear}";
        $schoolName = School::where('code', $school_code)->value('name') ?? 'Инженерная школа цифровых технологий';

        return [
            'UNIVERSITY' => 'ФГБОУ ВО «Югорский государственный университет»',
            'SCHOOL' => $schoolName,
            'SCHOOL_CODE' => $school_code,
            'PROTOCOL_NUMBER' => '-',
            'DATE' => '-',
            'DAY' => '-',
            'MONTH' => '-',
            'YEAR' => $academic_year ?: $defaultAcademicYear,
            'CITY' => 'Ханты-Мансийск',
            'CHAIRPERSON' => 'Самарина О.В. -- руководитель инженерной школы цифровых технологий, доцент инженерной школы цифровых технологий.',
            'CHAIR_DEGREE' => 'к.ф.-м.н., доцент',
            'MEMBERS' => "Самарин В.А. -- руководитель образовательной программы 09.03.01\n«Информатика и вычислительная техника», доцент инженерной школы цифровых технологий;\n\nПронькина Т.В. -- руководитель образовательной программы 09.03.04\n«Программная инженерия», доцент инженерной школы цифровых технологий;\n\nТукмачева Ю.А. -- преподаватель инженерной школы цифровых технологий,\nкуратор направления 10.03.01 Информационная безопасность;\n\nЛисимов Артём Андреевич -- старший преподаватель инженерной школы\nцифровых технологий, куратор направлений 09.03.01 Информатика и\nвычислительная техника и 09.03.04 Программная инженерия;\n\nСуфиянов Денис Илфатович -- студент группы ПИ31б, представителя совета\nобучающихся.",
            'SECRETARY' => 'Шевченко А.С. -- заместитель руководителя инженерной школы цифровых технологий по воспитательной работе, доцент инженерной школы цифровых технологий.',
            'SECRETARY_DEGREE' => 'канд. физ.-мат. наук, доцент',
            'AGENDA' => 'Оказание материальной поддержки нуждающимся студентам инженерной школы цифровых технологий.',
            'LISTENED' => 'Шевченко А.С. -- заместитель руководителя инженерной школы цифровых технологий по воспитательной работе, доцент инженерной школы цифровых технологий;',
            'SIGN_CHAIR' => 'Самарина О.В.',
            'SIGN_SECRETARY' => 'Шевченко А.С.',
            'DECISION' => ''
        ];
    }

    private function getTemplateVariables($school_code, $academic_year)
    {
        $default = $this->getDefaultTemplateVariables($school_code, $academic_year);

        $vars = TemplateVariable::where('school_code', $school_code)
            ->where('academic_year', $academic_year)
            ->pluck('value', 'placeholder')
            ->toArray();

        if (empty($vars)) {
            $vars = TemplateVariable::where('school_code', $school_code)
                ->where(function ($q) use ($academic_year) {
                    $q->where('academic_year', $academic_year)
                      ->orWhereNull('academic_year')
                      ->orWhere('academic_year', '');
                })
                ->orderBy('academic_year', 'desc')
                ->pluck('value', 'placeholder')
                ->toArray();
        }

        $vars = array_merge($default, $vars);
        $vars['PROTOCOL_NUMBER'] = '-';
        return $vars;
    }

    private function getStudentCategories($filters = [])
    {
        $search = $filters['search'] ?? null;
        $monthName = $filters['month'] ?? null;
        $academic_year = $filters['year'] ?? date('Y');
        $school_code = $filters['school_code'] ?? null;

        $monthNum = null;
        if ($monthName) {
            $monthMap = [
                'января' => 1, 'февраля' => 2, 'марта' => 3, 'апреля' => 4, 'мая' => 5,
                'июня' => 6, 'июля' => 7, 'августа' => 8, 'сентября' => 9, 'октября' => 10,
                'ноября' => 11, 'декабря' => 12
            ];
            $monthNum = $monthMap[$monthName] ?? null;
        }

        $query = Student::join('Groups as g', 'Students.group_id', '=', 'g.id')
            ->join('Directions as d', 'g.direction_id', '=', 'd.code')
            ->join('Schools as sc', 'd.vsh_code', '=', 'sc.code')
            ->leftJoin('StudentReasons as sr', function ($join) use ($academic_year) {
                $join->on('Students.id', '=', 'sr.student_id')
                    ->where('sr.academic_year', '=', $academic_year);
            })
            ->leftJoin('categories as c', 'sr.category_id', '=', 'c.id')
            ->select(
                'Students.id',
                'Students.full_name',
                'Students.budget',
                'g.group_name',
                'sc.name as school_name',
                'sc.code as school_code',
                'sr.category_id',
                'c.category_short',
                'c.max_amount',
                'c.amount_condition',
                'sr.amount',
                'sr.month'
            );

        if ($search) {
            $query->where('Students.full_name', 'like', "%{$search}%");
        }
        if ($monthNum !== null) {
            $query->where('sr.month', $monthNum);
        }
        if ($school_code) {
            $query->where('sc.code', $school_code);
        }

        $rows = $query->orderBy('Students.full_name')->get();

        $students = [];
        foreach ($rows as $row) {
            $student_id = $row->id;
            if (!isset($students[$student_id])) {
                $students[$student_id] = [
                    'id' => $row->id,
                    'full_name' => $row->full_name,
                    'budget' => $row->budget,
                    'group_name' => $row->group_name,
                    'school_name' => $row->school_name,
                    'category_id' => $row->category_id,
                    'category_short' => $row->category_short ?? 'Не указано',
                    'amount' => $row->amount ?? '10000.00',
                    'month' => $row->month,
                    'amount_condition' => $row->amount_condition ?? 'fixed',
                    'max_amount' => $row->max_amount ?? 0
                ];
            }
        }
        return array_values($students);
    }

    private function getAcademicYears()
    {
        $years = DB::table('AcademicYears')->orderBy('year', 'desc')->pluck('year')->toArray();
        if (!empty($years)) return $years;

        $years = TemplateVariable::whereNotNull('academic_year')
            ->where('academic_year', '!=', '')
            ->distinct()
            ->pluck('academic_year')
            ->toArray();
        sort($years);
        return $years ?: [date('Y') . '/' . (date('Y') + 1)];
    }

    private function getSchools()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return School::orderBy('name')->get(['code', 'name']);
        } else {
            return School::where('code', $user->school_code)->get(['code', 'name']);
        }
    }

    // Главная страница
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->role;
        $userSchoolCode = $user->school_code;

        $monthMap = [
            'января' => 1, 'февраля' => 2, 'марта' => 3, 'апреля' => 4, 'мая' => 5,
            'июня' => 6, 'июля' => 7, 'августа' => 8, 'сентября' => 9, 'октября' => 10,
            'ноября' => 11, 'декабря' => 12
        ];

        $academicYears = $this->getAcademicYears();
        $defaultAcademicYear = $academicYears[0] ?? '2024/2025';

        $filterAcademicYear = request('academic_year', $defaultAcademicYear);
        $filterMonthNum = request('month') ? (int) request('month') : null;
        $filterMonthName = '';
        if ($filterMonthNum) {
            $monthNames = array_flip($monthMap);
            $filterMonthName = $monthNames[$filterMonthNum] ?? '';
        }
        $filterSchoolCode = request('school_code') ? (int) request('school_code') : null;

        $schools = $this->getSchools();

        if ($userRole === 'admin') {
            $initialSchoolCode = request('school_code', $schools->isNotEmpty() ? $schools->first()->code : 1);
        } else {
            $initialSchoolCode = $userSchoolCode;
        }

        $initialYear = $filterAcademicYear;
        $initialMonthName = $filterMonthName ?: '-';
        $initialDay = '-';

        $templateVars = $this->getTemplateVariables($initialSchoolCode, $initialYear);
        $templateVars['DAY'] = $initialDay;
        $templateVars['MONTH'] = $initialMonthName;
        $templateVars['YEAR'] = $initialYear;

        $categories = Category::orderBy('number')->get(['id', 'category_short']);
        $categories_js = $categories->toArray();

        $filters = [
            'search' => '',
            'month' => $initialMonthName,
            'year' => $initialYear,
            'school_code' => $initialSchoolCode
        ];
        $studentCategories = $this->getStudentCategories($filters);

        $displayYear = self::extractYearStatic($templateVars['YEAR'] ?? $defaultAcademicYear, 'second');

        return view('protocols.index', compact(
            'userRole',
            'userSchoolCode',
            'monthMap',
            'academicYears',
            'defaultAcademicYear',
            'initialYear',
            'initialMonthName',
            'initialDay',
            'initialSchoolCode',
            'schools',
            'templateVars',
            'categories_js',
            'studentCategories',
            'displayYear'
        ));
    }

    // Сохранение шаблона
    public function saveTemplate(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->role;
        $userSchoolCode = $user->school_code;

        if (!$request->has('template') || !is_array($request->template)) {
            return response()->json(['status' => 'error', 'message' => 'Неверные данные шаблона'], 400);
        }

        if ($userRole === 'admin') {
            $school_code = $request->input('template.SCHOOL_CODE', 1);
        } else {
            $school_code = $userSchoolCode;
        }
        $academic_year = $request->input('template.YEAR', date('Y') . '/' . (date('Y') + 1));

        DB::beginTransaction();
        try {
            foreach ($request->template as $placeholder => $value) {
                if ($placeholder === 'PROTOCOL_NUMBER') continue;
                if (preg_match('/^[A-Z_]+$/', $placeholder)) {
                    $maxLength = ($placeholder === 'MEMBERS') ? 16000 : 1000;
                    $value = substr(trim($value), 0, $maxLength);

                    TemplateVariable::updateOrCreate(
                        [
                            'school_code' => $school_code,
                            'academic_year' => $academic_year,
                            'placeholder' => $placeholder,
                        ],
                        ['value' => $value]
                    );
                }
            }

            // Обновляем дату (с тире)
            if (isset($request->template['DAY'], $request->template['MONTH'], $request->template['YEAR'])) {
                $day = trim($request->template['DAY']);
                $month = trim($request->template['MONTH']);
                $year = trim($request->template['YEAR']);
                $monthMap = [
                    'января' => 1, 'февраля' => 2, 'марта' => 3, 'апреля' => 4, 'мая' => 5,
                    'июня' => 6, 'июля' => 7, 'августа' => 8, 'сентября' => 9, 'октября' => 10,
                    'ноября' => 11, 'декабря' => 12
                ];
                if ($day && $month && $year && isset($monthMap[$month])) {
                    $date = "«– – {$year} г.»";
                    TemplateVariable::updateOrCreate(
                        [
                            'school_code' => $school_code,
                            'academic_year' => $academic_year,
                            'placeholder' => 'DATE',
                        ],
                        ['value' => $date]
                    );
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Шаблон сохранен']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Ошибка сохранения: ' . $e->getMessage()], 500);
        }
    }

    // Генерация протокола
    public function generate(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->role;
        $userSchoolCode = $user->school_code;

        $filename = trim($request->input('filename', ''));
        $format = $request->input('format', 'pdf');
        $month = trim($request->input('month', ''));
        $year = trim($request->input('year', date('Y') . '/' . (date('Y') + 1)));
        $day = trim($request->input('day', '-'));
        $protocol_number = trim($request->input('protocol_number', '')); // не используется

        if ($userRole === 'admin') {
            $school_code = $request->filled('school_code') ? $request->input('school_code') : null;
        } else {
            $school_code = $userSchoolCode;
        }

        $monthMap = [
            'января' => 1, 'февраля' => 2, 'марта' => 3, 'апреля' => 4, 'мая' => 5,
            'июня' => 6, 'июля' => 7, 'августа' => 8, 'сентября' => 9, 'октября' => 10,
            'ноября' => 11, 'декабря' => 12
        ];
        if (!isset($monthMap[$month])) {
            return response()->json(['status' => 'error', 'message' => 'Неверный месяц'], 400);
        }

        $fileYear = self::extractYearStatic($year, 'second');
        $extension = $format === 'word' ? 'docx' : 'pdf';
        $filename = preg_replace('/\.(docx|pdf)$/i', '', $filename);
        if (empty($filename)) {
            $filename = "protocol_3_{$fileYear}";
        }
        $filename = "{$filename}.{$extension}";

        // Валидация имени файла (упрощённо)
        if (!preg_match('/^[\p{L}\p{N}\s._-]+$/u', preg_replace('/\.(docx|pdf)$/i', '', $filename))) {
            return response()->json(['status' => 'error', 'message' => 'Неверное имя файла'], 400);
        }

        $students = $this->getStudentCategories([
            'month' => $month,
            'year' => $year,
            'school_code' => $school_code
        ]);

        // Сохраняем протокол в БД
        try {
            $snapshot = array_map(function ($student) {
                return [
                    'id' => $student['id'],
                    'full_name' => $student['full_name'],
                    'budget' => $student['budget'],
                    'group_name' => $student['group_name'],
                    'category_short' => $student['category_short'] ?? '',
                    'amount' => $student['amount'] ?? 0,
                    'month' => $student['month'] ?? null,
                    'amount_condition' => $student['amount_condition'] ?? 'fixed',
                    'max_amount' => $student['max_amount'] ?? 0
                ];
            }, $students);

            GeneratedProtocol::create([
                'protocol_number' => '-',
                'month' => $month,
                'academic_year' => $year,
                'school_code' => $school_code,
                'student_snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                'file_name' => $filename,
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка сохранения протокола: ' . $e->getMessage());
        }

        $templateVars = $this->getTemplateVariables($school_code, $year);
        $templateVars['PROTOCOL_NUMBER'] = '-';
        $templateVars['DAY'] = $day;
        $templateVars['MONTH'] = $month;
        $templateVars['YEAR'] = $fileYear;
        $templateVars['DATE'] = "«– – {$fileYear} г.»";

        $rfStudents = array_filter($students, function ($s) {
            $budget = isset($s['budget']) ? strtoupper(trim($s['budget'])) : '';
            return $budget !== 'ХМАО' && $budget !== 'XMAO' && $budget !== 'HMAC' &&
                   $budget !== 'ХМАО-ЮГРА' && $budget !== 'ЮГРА';
        });
        $hmaoStudents = array_filter($students, function ($s) {
            $budget = isset($s['budget']) ? strtoupper(trim($s['budget'])) : '';
            return $budget === 'ХМАО' || $budget === 'XMAO' || $budget === 'HMAC' ||
                   $budget === 'ХМАО-ЮГРА' || $budget === 'ЮГРА';
        });

        if ($format === 'word') {
            return $this->generateWord($filename, $templateVars, $rfStudents, $hmaoStudents);
        } else {
            return $this->generatePdf($filename, $templateVars, $rfStudents, $hmaoStudents);
        }
    }

    private function generateWord($filename, $templateVars, $rfStudents, $hmaoStudents)
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);
        $section = $phpWord->addSection([
            'marginTop' => Converter::cmToTwip(2),
            'marginBottom' => Converter::cmToTwip(2),
            'marginLeft' => Converter::cmToTwip(3),
            'marginRight' => Converter::cmToTwip(1.5)
        ]);

        $section->addText("ПРОТОКОЛ № {$templateVars['PROTOCOL_NUMBER']}", ['bold' => true, 'size' => 14], ['alignment' => 'center', 'spaceAfter' => 120]);
        $section->addText('Заседания стипендиальной комиссии', ['bold' => true, 'size' => 14], ['alignment' => 'center', 'spaceAfter' => 120]);
        $section->addText($templateVars['SCHOOL'], ['bold' => true, 'size' => 14], ['alignment' => 'center', 'spaceAfter' => 120]);
        $section->addText($templateVars['UNIVERSITY'], ['bold' => true, 'size' => 14], ['alignment' => 'center', 'spaceAfter' => 240]);

        $run = $section->addTextRun(['spaceAfter' => 240]);
        $run->addText("«– – {$templateVars['YEAR']} г.»", ['size' => 12]);
        $run->addText("\t\t\t\t\t\t\t\t", ['size' => 12]);
        $run->addText("г. {$templateVars['CITY']}", ['size' => 12]);
        $section->addTextBreak(1, ['size' => 12], ['spaceAfter' => 240]);

        foreach ([
            ['Председатель комиссии:', $templateVars['CHAIRPERSON']],
            ['Члены комиссии:', $templateVars['MEMBERS'], true],
            ['Секретарь комиссии:', $templateVars['SECRETARY']],
            ['Повестка дня:', $templateVars['AGENDA']],
            ['Слушали:', $templateVars['LISTENED']],
            ['Решили:', $templateVars['DECISION']]
        ] as $item) {
            $section->addText($item[0], ['bold' => true, 'size' => 12], ['spaceAfter' => 60]);
            $text = $item[1];
            if (isset($item[2]) && $item[2]) {
                foreach (explode("\n", $text) as $line) {
                    if (trim($line) !== '') {
                        $section->addText(trim($line), ['size' => 12], ['spaceBefore' => 0, 'spaceAfter' => 0]);
                    }
                }
                $section->addTextBreak(1, ['size' => 12], ['spaceAfter' => 240]);
            } else {
                $section->addText($text, ['size' => 12], ['spaceAfter' => 240]);
            }
        }

        // Таблица РФ
        $section->addText("Студенты (РФ):", ['bold' => true, 'size' => 12], ['spaceBefore' => 120, 'spaceAfter' => 60]);
        $tableRf = $section->addTable(['borderSize' => 6, 'width' => 100 * 50, 'unit' => 'pct']);
        $widths = [
            Converter::cmToTwip(0.5),
            Converter::cmToTwip(5),
            Converter::cmToTwip(2),
            Converter::cmToTwip(2),
            Converter::cmToTwip(5),
            Converter::cmToTwip(2)
        ];
        $tableRf->addRow();
        foreach (['№', 'ФИО', 'Бюджет', 'Группа', 'Основание', 'Сумма'] as $i => $header) {
            $tableRf->addCell($widths[$i], ['valign' => 'center'])->addText($header, ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        }

        if (empty($rfStudents)) {
            $tableRf->addRow();
            $tableRf->addCell($widths[0], ['valign' => 'center'])->addText('1', ['size' => 12], ['alignment' => 'center']);
            $tableRf->addCell($widths[1], ['valign' => 'center'])->addText('Нет данных', ['size' => 12]);
            $tableRf->addCell($widths[2], ['valign' => 'center'])->addText('-', ['size' => 12], ['alignment' => 'center']);
            $tableRf->addCell($widths[3], ['valign' => 'center'])->addText('-', ['size' => 12], ['alignment' => 'center']);
            $tableRf->addCell($widths[4], ['valign' => 'center'])->addText('Нет студентов', ['size' => 12]);
            $tableRf->addCell($widths[5], ['valign' => 'center'])->addText('0', ['size' => 12], ['alignment' => 'center']);
        } else {
            foreach (array_slice($rfStudents, 0, 30) as $index => $student) {
                $reason = $student['category_short'] ?: 'Не указано';
                $formattedAmount = $this->formatAmount($student['amount'], $student['amount_condition'], $student['max_amount']);
                $tableRf->addRow();
                $tableRf->addCell($widths[0], ['valign' => 'center'])->addText($index + 1, ['size' => 12], ['alignment' => 'center']);
                $tableRf->addCell($widths[1], ['valign' => 'center'])->addText($student['full_name'], ['size' => 12]);
                $tableRf->addCell($widths[2], ['valign' => 'center'])->addText($student['budget'] ?: 'РФ', ['size' => 12], ['alignment' => 'center']);
                $tableRf->addCell($widths[3], ['valign' => 'center'])->addText($student['group_name'], ['size' => 12], ['alignment' => 'center']);
                $tableRf->addCell($widths[4], ['valign' => 'center'])->addText($reason, ['size' => 12]);
                $tableRf->addCell($widths[5], ['valign' => 'center'])->addText($formattedAmount, ['size' => 12]);
            }
        }

        // Таблица ХМАО
        $section->addText("Студенты (ХМАО):", ['bold' => true, 'size' => 12], ['spaceBefore' => 120, 'spaceAfter' => 60]);
        $tableHmao = $section->addTable(['borderSize' => 6, 'width' => 100 * 50, 'unit' => 'pct']);
        $tableHmao->addRow();
        foreach (['№', 'ФИО', 'Бюджет', 'Группа', 'Основание', 'Сумма'] as $i => $header) {
            $tableHmao->addCell($widths[$i], ['valign' => 'center'])->addText($header, ['bold' => true, 'size' => 12], ['alignment' => 'center']);
        }

        if (empty($hmaoStudents)) {
            $tableHmao->addRow();
            $tableHmao->addCell($widths[0], ['valign' => 'center'])->addText('1', ['size' => 12], ['alignment' => 'center']);
            $tableHmao->addCell($widths[1], ['valign' => 'center'])->addText('Нет данных', ['size' => 12]);
            $tableHmao->addCell($widths[2], ['valign' => 'center'])->addText('-', ['size' => 12], ['alignment' => 'center']);
            $tableHmao->addCell($widths[3], ['valign' => 'center'])->addText('-', ['size' => 12], ['alignment' => 'center']);
            $tableHmao->addCell($widths[4], ['valign' => 'center'])->addText('Нет студентов', ['size' => 12]);
            $tableHmao->addCell($widths[5], ['valign' => 'center'])->addText('0', ['size' => 12], ['alignment' => 'center']);
        } else {
            foreach (array_slice($hmaoStudents, 0, 3) as $index => $student) {
                $reason = $student['category_short'] ?: 'Не указано';
                $formattedAmount = $this->formatAmount($student['amount'], $student['amount_condition'], $student['max_amount']);
                $tableHmao->addRow();
                $tableHmao->addCell($widths[0], ['valign' => 'center'])->addText($index + 1, ['size' => 12], ['alignment' => 'center']);
                $tableHmao->addCell($widths[1], ['valign' => 'center'])->addText($student['full_name'], ['size' => 12]);
                $tableHmao->addCell($widths[2], ['valign' => 'center'])->addText($student['budget'] ?: 'ХМАО', ['size' => 12], ['alignment' => 'center']);
                $tableHmao->addCell($widths[3], ['valign' => 'center'])->addText($student['group_name'], ['size' => 12], ['alignment' => 'center']);
                $tableHmao->addCell($widths[4], ['valign' => 'center'])->addText($reason, ['size' => 12]);
                $tableHmao->addCell($widths[5], ['valign' => 'center'])->addText($formattedAmount, ['size' => 12]);
            }
        }

        $section->addTextBreak(1, ['size' => 12], ['spaceAfter' => 240]);

        $section->addText("Руководитель инженерной школы цифровых технологий", ['size' => 12], ['spaceAfter' => 60]);
        $run = $section->addTextRun(['spaceAfter' => 240]);
        $run->addText($templateVars['CHAIR_DEGREE'] . " ", ['size' => 12]);
        $run->addText("____________________ ", ['underline' => 'single', 'size' => 12]);
        $run->addText($templateVars['SIGN_CHAIR'], ['size' => 12]);

        $section->addText("Заместитель руководителя инженерной школы цифровых технологий по воспитательной работе", ['size' => 12], ['spaceAfter' => 60]);
        $run = $section->addTextRun(['spaceAfter' => 240]);
        $run->addText($templateVars['SECRETARY_DEGREE'] . " ", ['size' => 12]);
        $run->addText("____________________ ", ['underline' => 'single', 'size' => 12]);
        $run->addText($templateVars['SIGN_SECRETARY'], ['size' => 12]);

        $tempDir = storage_path('app/temp/');
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
        $outputPath = $tempDir . $filename;
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputPath);

        $fileContent = base64_encode(file_get_contents($outputPath));
        unlink($outputPath);

        return response()->json([
            'status' => 'success',
            'message' => 'Протокол сгенерирован',
            'file' => $fileContent,
            'filename' => $filename,
            'contentType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]);
    }

    private function generatePdf($filename, $templateVars, $rfStudents, $hmaoStudents)
    {
        // Используем LaTeX, как в оригинале
        $templatePath = storage_path('app/templates/template.tex');
        if (!file_exists($templatePath)) {
            return response()->json(['status' => 'error', 'message' => 'Шаблон LaTeX не найден'], 404);
        }

        $templateContent = file_get_contents($templatePath);

        $studentListRf = '';
        foreach (array_slice($rfStudents, 0, 30) as $index => $student) {
            $reason = $student['category_short'] ?: 'Не указано';
            $budget = isset($student['budget']) && trim($student['budget']) ? $student['budget'] : 'РФ';
            $formattedAmount = $this->formatAmount($student['amount'], $student['amount_condition'], $student['max_amount']);
            $row = [
                'number' => $index + 1,
                'fio' => $this->escapeLatex($student['full_name']),
                'budget' => $this->escapeLatex($budget),
                'group' => $this->escapeLatex($student['group_name']),
                'reason' => $this->escapeLatex($reason),
                'amount' => $this->escapeLatex($formattedAmount)
            ];
            $studentListRf .= "{$row['number']} & {$row['fio']} & {$row['budget']} & {$row['group']} & {$row['reason']} & {$row['amount']} \\\\\n";
        }
        if (empty($studentListRf)) {
            $studentListRf = "1 & Нет данных & - & - & Нет студентов & 0 \\\\\n";
        }

        $studentListHmao = '';
        foreach (array_slice($hmaoStudents, 0, 3) as $index => $student) {
            $reason = $student['category_short'] ?: 'Не указано';
            $budget = isset($student['budget']) && trim($student['budget']) ? $student['budget'] : 'ХМАО';
            $formattedAmount = $this->formatAmount($student['amount'], $student['amount_condition'], $student['max_amount']);
            $row = [
                'number' => $index + 1,
                'fio' => $this->escapeLatex($student['full_name']),
                'budget' => $this->escapeLatex($budget),
                'group' => $this->escapeLatex($student['group_name']),
                'reason' => $this->escapeLatex($reason),
                'amount' => $this->escapeLatex($formattedAmount)
            ];
            $studentListHmao .= "{$row['number']} & {$row['fio']} & {$row['budget']} & {$row['group']} & {$row['reason']} & {$row['amount']} \\\\\n";
        }
        if (empty($studentListHmao)) {
            $studentListHmao = "1 & Нет данных & - & - & Нет студентов & 0 \\\\\n";
        }

        $members = $templateVars['MEMBERS'];
        $membersLines = explode("\n", $members);
        $membersLatex = '';
        foreach ($membersLines as $line) {
            if (trim($line) !== '') {
                $membersLatex .= $this->escapeLatex(trim($line)) . ' \\\\ ';
            }
        }
        $templateVars['MEMBERS_LATEX'] = $membersLatex ?: 'Нет данных';

        $dateWithHyphen = "«-- {$templateVars['YEAR']} г.»";
        $latexContent = str_replace(
            ['{STUDENT_LIST_RF}', '{STUDENT_LIST_HMAO}', '{MEMBERS_LATEX}', '{DATE}', '{PROTOCOL_NUMBER}'],
            [rtrim($studentListRf, "\n"), rtrim($studentListHmao, "\n"), $templateVars['MEMBERS_LATEX'], $dateWithHyphen, '-'],
            $templateContent
        );
        foreach ($templateVars as $placeholder => $value) {
            if ($placeholder !== 'MEMBERS_LATEX' && $placeholder !== 'DECISION' && $placeholder !== 'DATE' && $placeholder !== 'PROTOCOL_NUMBER') {
                $latexContent = str_replace("{{$placeholder}}", $this->escapeLatex($value), $latexContent);
            }
        }
        $latexContent = str_replace('{DECISION}', $this->escapeLatex($templateVars['DECISION'] ?? ''), $latexContent);

        $tempDir = storage_path('app/temp/');
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
        $tempTexPath = $tempDir . 'template.tex';
        file_put_contents($tempTexPath, $latexContent);

        $outputPath = $tempDir . $filename;
        $command = "latexmk -pdf -output-directory=$tempDir $tempTexPath 2>&1";
        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка компиляции LaTeX: ' . implode("\n", $output)
            ], 500);
        }

        $fileContent = base64_encode(file_get_contents($outputPath));
        unlink($tempTexPath);
        unlink($outputPath);
        array_map('unlink', glob($tempDir . 'temp_protocol.*'));

        return response()->json([
            'status' => 'success',
            'message' => 'Протокол сгенерирован',
            'file' => $fileContent,
            'filename' => $filename,
            'contentType' => 'application/pdf'
        ]);
    }

    // AJAX: получить студентов
    public function getStudents(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->role;
        $userSchoolCode = $user->school_code;

        $filters = [
            'search' => $request->input('search', ''),
            'month' => $request->input('month', ''),
            'year' => $request->input('year', date('Y') . '/' . (date('Y') + 1)),
        ];
        if ($userRole === 'admin') {
            $filters['school_code'] = $request->input('school_code') ? (int) $request->input('school_code') : null;
        } else {
            $filters['school_code'] = $userSchoolCode;
        }
        $students = $this->getStudentCategories($filters);
        return response()->json($students);
    }

    // AJAX: получить переменные шаблона
    public function getTemplateVars(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->role;
        $userSchoolCode = $user->school_code;

        if ($userRole === 'admin') {
            $school_code = $request->input('school_code', 1);
        } else {
            $school_code = $userSchoolCode;
        }
        $academic_year = $request->input('academic_year', date('Y') . '/' . (date('Y') + 1));
        $templateVars = $this->getTemplateVariables($school_code, $academic_year);

        return response()->json(['status' => 'success', 'template_vars' => $templateVars]);
    }
}