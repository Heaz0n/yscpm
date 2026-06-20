<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentReason;
use App\Models\TemplateVariable;
use App\Models\GeneratedProtocol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;

class ProtocolGeneratorController extends Controller
{
    /**
     * Показать форму для генерации протокола.
     */
    public function index()
    {
        $schools = School::orderBy('name')->get();
        $years = AcademicYear::orderBy('year', 'desc')->pluck('year')->toArray();
        $currentYear = $years[0] ?? null;

        return view('protocols.generate', compact('schools', 'years', 'currentYear'));
    }

    /**
     * Сгенерировать протокол и скачать его.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'school_code' => 'required|exists:Schools,code',
            'academic_year' => 'required|string',
            'month' => 'required|integer|min:1|max:12',
            'day' => 'required|integer|min:1|max:31',
            'protocol_number' => 'required|string|max:50',
        ]);

        $schoolCode = $validated['school_code'];
        $academicYear = $validated['academic_year'];
        $monthNum = $validated['month'];
        $day = $validated['day'];
        $protocolNumber = $validated['protocol_number'];

        // Проверяем, есть ли студенты с выплатами за этот месяц
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
            return back()->with('error', 'Нет студентов с выплатами за указанный месяц и год.');
        }

        // Подготовка данных для документа
        $monthNames = [
            1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
            5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
            9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
        ];
        $monthName = $monthNames[$monthNum];

        // Переменные шаблона
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

        // Формируем массив для документа
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

            // Возвращаем файл для скачивания
            return response()->make($fileContent, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="' . $protocol->file_name . '"',
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка генерации протокола: ' . $e->getMessage());
        }
    }

    /**
     * Генерация Word-документа (код скопирован из StudentListController).
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
}