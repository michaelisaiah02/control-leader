<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChecksheetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\ProblemListController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Ypq\QuestionController;
use App\Http\Controllers\Ypq\UserController;
use App\Http\Middleware\CheckActiveChecksheet;
use App\Http\Middleware\CheckRole;
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
    Route::post('/update-password', [UserController::class, 'updatePassword'])->name('updatePassword');
    Route::middleware(CheckActiveChecksheet::class)->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware(SingleLogin::class)->group(function () {
            // Rencana & Detail (biar lengkap, bisa kamu tambah belakangan)
            Route::prefix('schedule')->as('schedule.')->controller(ScheduleController::class)->group(function () {
                // Main page (Supervisor Checks Leader)
                Route::get('/', 'index')->name('index');
                Route::post('/{plan}/add-leader', 'addWeeklyLeader')->name('addWeeklyLeader');
                Route::post('/{plan}/remove-leader', 'removeWeeklyLeader')->name('removeWeeklyLeader');


                // PAGE: Leader Checks Operator
                Route::get('/leader', 'leaderIndex')->name('leader');
                Route::post('/{plan}/update-cell', 'updateCell')->name('updateCell');
            });

            // Operator data view
            Route::prefix('operator')->as('operator.')->controller(OperatorController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/store', 'store')->name('store');
                Route::put('/update-operator/{id}', 'update')->name('update');
                Route::delete('/delete-operator/{id}', 'destroy');
                Route::get('/search', 'search')->name('search');
            });

            Route::middleware(CheckRole::class)->group(function () {
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

            Route::prefix('checksheets')->as('checksheets.')->controller(ChecksheetController::class)->group(function () {
                Route::get('/create', 'createPartA')->name('create');
                Route::get('/part-b', 'showPartB')->name('partB');
                Route::post('/', 'store')->name('store');
                Route::post('/cancel', 'cancel')->name('cancel');
            });
        });
    });

    Route::prefix('list-problem')->as('listProblem.')->controller(ProblemListController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{type}', 'list')->name('list');
        Route::get('/{type}/{id}', 'edit')->name('edit');
        Route::put('/{type}/{id}/update', 'update')->name('update');
        Route::get('/{type}/edit', 'editTemplate');
    });

    Route::prefix('reports')->as('reports.')->controller(ReportController::class)->group(function () {
        // 1. Static Routes (Tarik ke atas semua)
        Route::get('/', 'index')->name('index');
        Route::get('/daily', 'daily')->name('daily');
        Route::get('/leader-score', 'leaderScore')->name('leaderScore');
        Route::get('/leader-consistency', 'leaderConsistency')->name('leaderConsistency');

        // 2. API Routes
        Route::get('/api/daily', 'apiDaily');
        Route::get('/api/consistency', 'apiConsistency');
        Route::get('/api/supervisor-score', 'apiSupervisorScore');
        Route::get('/api/leader-score', 'apiLeaderScore');
        Route::get('/api/operator-score', 'apiOperatorScore');
        Route::get('/api/supervisor-consistency', 'apiSupervisorConsistency');
        Route::get('/api/leader-consistency', 'apiLeaderConsistency');

        // 3. Dynamic Routes (Taruh paling bawah biar nggak nyedot URL lain)
        Route::get('/{type}', 'form')->name('form');
        Route::get('/{type}/consistency', 'consistency')->name('consistency');
        Route::get('/{type}/score', 'score')->name('score'); // Nama method gue sesuaikan jadi 'score'
    });
});
