<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user_role = $user->role;
        $user_school_code = $user->school_code;

        // Получаем учебные годы
        $academic_years = DB::table('AcademicYears')->orderBy('year', 'desc')->pluck('year')->toArray();
        $current_academic_year = $academic_years[0] ?? date('Y') . '/' . (date('Y') + 1);

        $schools = [];
        if ($user_role === 'admin') {
            $schools = DB::table('Schools')->orderBy('name')->pluck('name', 'code')->toArray();
        }

        $months_ru = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель', 5 => 'Май', 6 => 'Июнь',
            7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
        ];

        return view('reports.index', compact('user_role', 'user_school_code', 'academic_years', 'current_academic_year', 'schools', 'months_ru'));
    }

    public function getData(Request $request)
    {
        $user = Auth::user();
        $user_role = $user->role;
        $user_school_code = $user->school_code;

        $selected_year = $request->academic_year;
        $month_from = (int)$request->month_from;
        $month_to = (int)$request->month_to;
        $selected_school = $request->school ?? ($user_role !== 'admin' ? $user_school_code : 'all');

        if ($user_role !== 'admin' && $selected_school !== $user_school_code) {
            $selected_school = $user_school_code;
        }

        $conditions = ["sr.academic_year = :year"];
        $params = [':year' => $selected_year];

        if ($month_from > 0 && $month_to > 0) {
            $conditions[] = "sr.month BETWEEN :month_from AND :month_to";
            $params[':month_from'] = $month_from;
            $params[':month_to']   = $month_to;
        } elseif ($month_from > 0) {
            $conditions[] = "sr.month >= :month_from";
            $params[':month_from'] = $month_from;
        } elseif ($month_to > 0) {
            $conditions[] = "sr.month <= :month_to";
            $params[':month_to'] = $month_to;
        }

        if ($selected_school !== 'all') {
            $conditions[] = "sc.code = :school_code";
            $params[':school_code'] = $selected_school;
        } elseif ($user_role !== 'admin') {
            $conditions[] = "sc.code = :school_code";
            $params[':school_code'] = $user_school_code;
        }

        $where_clause = implode(' AND ', $conditions);

        // Основные метрики
        $sql = "SELECT COALESCE(SUM(sr.amount), 0) as total_amount,
                       COUNT(*) as total_payments,
                       COUNT(DISTINCT sr.student_id) as total_students,
                       COALESCE(AVG(sr.amount), 0) as avg_amount
                FROM StudentReasons sr
                JOIN Students s ON sr.student_id = s.id
                JOIN `Groups` g ON s.group_id = g.id
                JOIN Directions d ON g.direction_id = d.code
                JOIN Schools sc ON d.school_code = sc.code
                WHERE $where_clause";
        $metrics = DB::selectOne($sql, $params);
        $metrics = (array)$metrics;

        // Распределение по категориям
        $sql = "SELECT c.id, c.category_short, COUNT(*) as cnt, SUM(sr.amount) as total
                FROM StudentReasons sr
                JOIN Students s ON sr.student_id = s.id
                JOIN `Groups` g ON s.group_id = g.id
                JOIN Directions d ON g.direction_id = d.code
                JOIN Schools sc ON d.school_code = sc.code
                JOIN categories c ON sr.category_id = c.id
                WHERE $where_clause
                GROUP BY sr.category_id, c.id, c.category_short
                HAVING total > 0
                ORDER BY total DESC";
        $categories_stats = DB::select($sql, $params);

        // По месяцам
        $sql = "SELECT sr.month, COUNT(*) as cnt, SUM(sr.amount) as total
                FROM StudentReasons sr
                JOIN Students s ON sr.student_id = s.id
                JOIN `Groups` g ON s.group_id = g.id
                JOIN Directions d ON g.direction_id = d.code
                JOIN Schools sc ON d.school_code = sc.code
                WHERE $where_clause
                GROUP BY sr.month
                ORDER BY sr.month";
        $monthly_stats = DB::select($sql, $params);

        // По бюджетам
        $sql = "SELECT COALESCE(s.budget, 'Не указан') as budget, COUNT(*) as cnt, SUM(sr.amount) as total
                FROM StudentReasons sr
                JOIN Students s ON sr.student_id = s.id
                JOIN `Groups` g ON s.group_id = g.id
                JOIN Directions d ON g.direction_id = d.code
                JOIN Schools sc ON d.school_code = sc.code
                WHERE $where_clause
                GROUP BY s.budget
                HAVING total > 0
                ORDER BY total DESC";
        $budget_stats = DB::select($sql, $params);

        // По школам (только если выбраны все)
        $school_stats = [];
        if ($selected_school === 'all') {
            $school_conditions = array_filter($conditions, function($cond) {
                return strpos($cond, 'sc.code') === false;
            });
            $school_where = implode(' AND ', $school_conditions);
            $sql = "SELECT sc.name as school_name, COUNT(*) as cnt, SUM(sr.amount) as total
                    FROM StudentReasons sr
                    JOIN Students s ON sr.student_id = s.id
                    JOIN `Groups` g ON s.group_id = g.id
                    JOIN Directions d ON g.direction_id = d.code
                    JOIN Schools sc ON d.school_code = sc.code
                    WHERE $school_where
                    GROUP BY sc.code, sc.name
                    HAVING total > 0
                    ORDER BY total DESC";
            $school_stats = DB::select($sql, $params);
        }

        // Топ-10 студентов
        $sql = "SELECT s.full_name, g.group_name, SUM(sr.amount) as total
                FROM StudentReasons sr
                JOIN Students s ON sr.student_id = s.id
                JOIN `Groups` g ON s.group_id = g.id
                JOIN Directions d ON g.direction_id = d.code
                JOIN Schools sc ON d.school_code = sc.code
                WHERE $where_clause
                GROUP BY sr.student_id, s.full_name, g.group_name
                ORDER BY total DESC
                LIMIT 10";
        $top_students = DB::select($sql, $params);

        // Количество протоколов
        $protocol_conditions = ["academic_year = :year"];
        $protocol_params = [':year' => $selected_year];
        if ($selected_school !== 'all') {
            $protocol_conditions[] = "school_code = :school_code";
            $protocol_params[':school_code'] = $selected_school;
        } elseif ($user_role !== 'admin') {
            $protocol_conditions[] = "school_code = :school_code";
            $protocol_params[':school_code'] = $user_school_code;
        }
        $protocol_where = implode(' AND ', $protocol_conditions);
        $sql = "SELECT COUNT(*) FROM GeneratedProtocols WHERE $protocol_where";
        $total_protocols = DB::selectOne($sql, $protocol_params);
        $total_protocols = (int)($total_protocols->{'COUNT(*)'} ?? 0);

        return response()->json([
            'metrics' => $metrics,
            'categories' => $categories_stats,
            'monthly' => $monthly_stats,
            'budgets' => $budget_stats,
            'schools' => $school_stats,
            'topStudents' => $top_students,
            'totalProtocols' => $total_protocols
        ]);
    }

    public function getCategoryDetail(Request $request)
    {
        $user = Auth::user();
        $user_role = $user->role;
        $user_school_code = $user->school_code;

        $category_id = (int)$request->category_id;
        $selected_year = $request->academic_year;
        $month_from = (int)$request->month_from;
        $month_to = (int)$request->month_to;
        $selected_school = $request->school ?? ($user_role !== 'admin' ? $user_school_code : 'all');

        if ($user_role !== 'admin' && $selected_school !== $user_school_code) {
            $selected_school = $user_school_code;
        }

        $conditions = ["sr.academic_year = :year", "sr.category_id = :cat_id"];
        $params = [':year' => $selected_year, ':cat_id' => $category_id];

        if ($month_from > 0 && $month_to > 0) {
            $conditions[] = "sr.month BETWEEN :month_from AND :month_to";
            $params[':month_from'] = $month_from;
            $params[':month_to']   = $month_to;
        } elseif ($month_from > 0) {
            $conditions[] = "sr.month >= :month_from";
            $params[':month_from'] = $month_from;
        } elseif ($month_to > 0) {
            $conditions[] = "sr.month <= :month_to";
            $params[':month_to'] = $month_to;
        }

        if ($selected_school !== 'all') {
            $conditions[] = "sc.code = :school_code";
            $params[':school_code'] = $selected_school;
        } elseif ($user_role !== 'admin') {
            $conditions[] = "sc.code = :school_code";
            $params[':school_code'] = $user_school_code;
        }

        $where_clause = implode(' AND ', $conditions);

        $sql = "SELECT s.full_name, g.group_name, sr.amount, sr.month
                FROM StudentReasons sr
                JOIN Students s ON sr.student_id = s.id
                JOIN `Groups` g ON s.group_id = g.id
                JOIN Directions d ON g.direction_id = d.code
                JOIN Schools sc ON d.school_code = sc.code
                WHERE $where_clause
                ORDER BY sr.amount DESC";
        $students = DB::select($sql, $params);

        return response()->json($students);
    }
}