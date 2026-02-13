<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ChecksheetController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\CheckRoleIsAdmin;
use App\Http\Middleware\ResumeDraft;
use App\Http\Middleware\SingleLogin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});
Route::get('/ping', function () {
    return response()->json(['pong' => true]);
})->name('ping');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(SingleLogin::class)->middleware(ResumeDraft::class)->group(function () {
        // Rencana & Detail (biar lengkap, bisa kamu tambah belakangan)
        Route::prefix('schedule')->as('schedule.')->controller(ScheduleController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::post('/{id}/update-cell-leader', 'updateCellLeader')->name('updateCellLeader');
            Route::post('/{id}/update-division', 'updateDivision')->name('updateDivision');
            Route::post('/{id}/add-user', 'addUser')->name('addUser');
            Route::delete('/{id}/remove-user/{userId}', 'removeUser')->name('removeUser');
        });

        // Operator data view
        Route::prefix('operator')->as('operator.')->controller(OperatorController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::post('/update-operator/{id}', 'update')->name('update');
            Route::delete('/delete-operator/{id}', 'destroy');
            Route::get('/search', 'search')->name('search');
        });

        Route::middleware(CheckRoleIsAdmin::class)->prefix('admin')->as('admin.')->group(function () {
            // ------------------------
            // QUESTIONS CRUD
            // ------------------------
            Route::prefix('question')->as('question.')->controller(QuestionController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/store', 'store')->name('store');
                Route::get('/{question}/edit', 'edit')->name('edit');
                Route::put('/{question}', 'update')->name('update');
                Route::delete('/{question}', 'destroy')->name('destroy');
                Route::post('/update-order', [QuestionController::class, 'updateOrder'])->name('updateOrder');
            });

            // =========================
            // USERS CRUD
            // =========================
            Route::prefix('users')->as('users.')->controller(UserController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/store', 'store')->name('store');
                Route::post('/update-user/{id}', 'update')->name('update');
                Route::delete('/delete-user/{id}', 'destroy');
                Route::get('/search', 'search')->name('search');
                Route::get('/get-superiors', 'getSuperiors')->name('getSuperiors');
            });
        });

        // =========================
        // CHECKSHEET (2 step: Part A -> Part B)
        // =========================

        Route::get('/checksheets/create', [ChecksheetController::class, 'createPartA'])->name('checksheets.create'); // ?type=awal_shift

        // draft/timer
        Route::post('/checksheets/drafts/start', [ChecksheetController::class, 'startDraft'])->name('drafts.start');
        Route::post('/heartbeat', [ChecksheetController::class, 'heartbeat'])->name('heartbeat');

        // ke Part B
        Route::get('/checksheets/part-b', [ChecksheetController::class, 'showPartB'])->name('checksheets.partB');

        // submit final
        Route::post('/checksheets', [ChecksheetController::class, 'store'])->name('checksheets.store');
    });
});

// Reports (Sementara)
Route::controller(App\Http\Controllers\ReportController::class)->group(function () {
    Route::get("/reports", 'index')->name('reports.index');
    Route::get("/report/{type}", 'form')->name('reports.form');
    Route::get("/reports/daily", 'daily')->name('reports.daily');
    Route::get("/reports/{type}/monthly", 'monthly')->name('reports.monthly');
    Route::get("/reports/{type}/score", 'leaderScore')->name('reports.score');
    Route::get("/reports/leader-score", 'leaderScore')->name('reports.leaderScore');
    Route::get("/reports/leader-consistency", 'leaderConsistency')->name('reports.leaderConsistency');
    // API
    Route::get("api/reports/daily", 'apiDaily');
    Route::get("api/reports/monthly", 'apiMonthly');
    Route::get("api/reports/leader-score", 'apiLeaderScore');
    Route::get("api/reports/leader-consistency", 'apiLeaderConsistency');
});

Route::controller(App\Http\Controllers\ProblemListController::class)->group(function () {
    Route::get('/list-problem', 'index')->name("listProblem.index");
    Route::get('/list-problem/{type}', 'list')->name("listProblem.list");
    Route::get('/list-problem/{type}/{id}', 'edit')->name("listProblem.edit");
    Route::put('/list-problem/{type}/{id}/update', 'update')->name("listProblem.update");
    Route::get('/list-problem/{type}/edit', 'editTemplate');
});
