<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentListController;
use App\Http\Controllers\ProtocolController;
use App\Http\Controllers\ProtocolGeneratorController; // добавьте этот импорт
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Гостевые маршруты (неавторизованные)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Выход из системы
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ---------------------------------------------------------------------
// Защищённые маршруты (требуется авторизация)
// ---------------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Главная страница (редирект на отчёты)
    Route::get('/', function () {
        return redirect()->route('reports.index');
    })->name('dashboard');

    Route::get('/protocols', [ProtocolController::class, 'index'])->name('protocols.index');
    Route::post('/protocols/save-template', [ProtocolController::class, 'saveTemplate'])->name('protocols.save-template');
    Route::post('/protocols/generate', [ProtocolController::class, 'generate'])->name('protocols.generate');
    Route::get('/protocols/get-students', [ProtocolController::class, 'getStudents'])->name('protocols.get-students');
    Route::get('/protocols/get-template-vars', [ProtocolController::class, 'getTemplateVars'])->name('protocols.get-template-vars');

    // --- Администрирование пользователей (только admin) ---
    Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/data', [UserController::class, 'getUsers'])->name('users.data');
        Route::get('/users/roles', [UserController::class, 'getRoles'])->name('users.roles');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // --- Школы и направления ---
    Route::prefix('schools')->name('schools.')->group(function () {
        Route::get('/', [SchoolController::class, 'index'])->name('index');
        Route::post('/add-school', [SchoolController::class, 'storeSchool'])->name('storeSchool');
        Route::post('/edit-school', [SchoolController::class, 'updateSchool'])->name('updateSchool');
        Route::post('/delete-school', [SchoolController::class, 'deleteSchool'])->name('deleteSchool');
        Route::post('/add-direction', [SchoolController::class, 'storeDirection'])->name('storeDirection');
        Route::post('/edit-direction', [SchoolController::class, 'updateDirection'])->name('updateDirection');
        Route::post('/delete-direction', [SchoolController::class, 'deleteDirection'])->name('deleteDirection');
    });

    // --- Группы ---
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::post('/groups/data', [GroupController::class, 'getData'])->name('groups.data');

    // --- Категории ---
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::post('/categories/save-payout-count', [CategoryController::class, 'savePayoutCount'])->name('categories.savePayoutCount');

    // --- Профиль пользователя ---
    Route::prefix('profile')->name('profile-settings.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/update-profile', [ProfileController::class, 'updateProfile'])->name('updateProfile');
        Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('changePassword');
        Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar'])->name('uploadAvatar');
        Route::post('/delete-avatar', [ProfileController::class, 'deleteAvatar'])->name('deleteAvatar');
        Route::post('/logout-sessions', [ProfileController::class, 'logoutSessions'])->name('logoutSessions');
        Route::post('/update-settings', [ProfileController::class, 'updateSettings'])->name('updateSettings');
    });

    // --- Отчёты ---
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/data', [ReportController::class, 'getData'])->name('data');
        Route::get('/category-detail', [ReportController::class, 'getCategoryDetail'])->name('category-detail');
    });

    // =============================================================
    // УПРАВЛЕНИЕ СТУДЕНТАМИ (СПИСОК)
    // =============================================================
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [StudentListController::class, 'index'])->name('list');
        Route::post('/action', [StudentListController::class, 'action'])->name('action');
    });

    

    // =============================================================
    // ГЕНЕРАЦИЯ ПРОТОКОЛОВ
    // =============================================================
    Route::prefix('protocols')->name('protocols.')->group(function () {
        // Страница формы генерации
        Route::get('/generate', [ProtocolGeneratorController::class, 'index'])->name('generate');
        // Обработчик генерации (POST)
        Route::post('/generate', [ProtocolGeneratorController::class, 'generate'])->name('generate.post');
        // Если у вас есть список уже сгенерированных протоколов:
        Route::get('/', function () {
            return redirect()->route('reports.index'); // или на список протоколов
        })->name('index');
    });
});