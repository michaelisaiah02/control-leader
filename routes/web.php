<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChecksheetFormController;
use App\Http\Controllers\ControlLeader\ChecksheetController;
use App\Http\Controllers\ControlLeader\ScheduleDetailController;
use App\Http\Controllers\ControlLeader\SchedulePlanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Kalibrasi\Admin\EquipmentController;
use App\Http\Controllers\Kalibrasi\Admin\MasterListController;
use App\Http\Controllers\Kalibrasi\Admin\StandardController;
use App\Http\Controllers\Kalibrasi\Admin\UnitController;
use App\Http\Controllers\Kalibrasi\Admin\UserController;
use App\Http\Controllers\Kalibrasi\APIController;
use App\Http\Controllers\Kalibrasi\Input\CalibrationDataController;
use App\Http\Controllers\Kalibrasi\Input\NewEquipmentController;
use App\Http\Controllers\Kalibrasi\Input\RepairDataController;
use App\Http\Controllers\Kalibrasi\PrintController;
use App\Http\Controllers\Kalibrasi\ReportController;
use App\Http\Middleware\CheckAppAuthentication;
use App\Http\Middleware\CheckIncompleteInput;
use App\Http\Middleware\CheckLogin;
use App\Http\Middleware\CheckRoleIsAdmin;
use App\Http\Middleware\CheckRoleMinUser;
use App\Http\Middleware\ResumeDraft;
use App\Http\Middleware\SingleLogin;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['pong' => true]);
})->name('ping');

Route::middleware(CheckLogin::class)->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('welcome');
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
    });
});

Route::middleware(CheckAppAuthentication::class)->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::middleware(CheckIncompleteInput::class)->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
    Route::prefix('kalibrasi')->as('kalibrasi.')->group(function () {
        Route::get('/print-label/{id}', [PrintController::class, 'label'])->name('print.label');
        Route::middleware(CheckRoleMinUser::class)->controller(APIController::class)->group(function () {
            // API data
            Route::get('/count-equipments/{type_id}', 'countEquipments')->name('api.count.equipments');
            Route::get('/get-masterlist/{id_num}', 'getMasterList')->name('api.get.masterlist');
            Route::get('/get-actual-value/{id}', 'getActualValue')->name('api.get.actual.value');
            Route::get('/get-repair-data/{id}', 'getRepairData')->name('api.get.repair.data');
        });
        Route::middleware(CheckIncompleteInput::class)->group(function () {
            Route::controller(PrintController::class)->group(function () {
                Route::get('/print-report-masterlist/{id}', 'reportMasterlist')->name('print.report.masterlist');
                Route::get('/print-report-repair/{id}', 'reportRepair')->name('print.report.repair');
            });
            Route::controller(ReportController::class)->group(function () {
                Route::get('/report', 'menu')->name('report.menu');
                Route::get('/report/search', 'search')->name('report.search');
                Route::post('/report/masterlist', 'masterlist')->name('report.masterlist');
                Route::post('/report/repairs', 'repairs')->name('report.repairs');
            });
        });

        // hanya user ke atas
        Route::middleware(CheckRoleMinUser::class)->middleware(CheckIncompleteInput::class)->group(function () {
            Route::controller(NewEquipmentController::class)->group(function () {
                Route::get('/input/new-equipment', 'create')->name('input.new.equipment');
                Route::post('/input/new-equipment', 'store')->name('store.equipment');
            });

            Route::controller(CalibrationDataController::class)->group(function () {
                Route::get('/input/calibration-data', 'create')->name('input.calibration.data');
                Route::post('/input/calibration-data', 'store')->name('store.calibration');
                Route::post('/input/calibration-data/{id}', 'edit')->name('edit.calibration');
            });

            Route::controller(RepairDataController::class)->group(function () {
                Route::get('/input/repair-data', 'create')->name('input.repair');
                Route::post('/input/repair-data', 'store')->name('store.repair');
                Route::post('/input/repair-data/{id}', 'edit')->name('edit.repair');
            });
            Route::post('/standards/store', [DashboardController::class, 'store'])->name('standards.store');

            // hanya admin
            Route::middleware(CheckRoleIsAdmin::class)->group(function () {
                Route::prefix('admin/users')->controller(UserController::class)->group(function () {
                    Route::get('/', 'index')->name('admin.users.index');
                    Route::post('/store', 'store')->name('admin.users.store');
                    Route::post('/update-user/{id}', 'update')->name('admin.users.update');
                    Route::delete('/delete-user/{id}', 'destroy');
                    Route::get('/search', 'search')->name('admin.users.search');
                });
                Route::prefix('admin/standards')->controller(StandardController::class)->group(function () {
                    Route::get('/', 'index')->name('admin.standards.index');
                    Route::post('/update-standard/{id}', 'update')->name('admin.standards.update');
                    Route::post('/store', 'store')->name('admin.standards.store');
                    Route::delete('/delete-standard/{id}', 'destroy');
                    Route::get('/search', 'search')->name('admin.standards.search');
                });
                Route::prefix('admin/units')->controller(UnitController::class)->group(function () {
                    Route::get('/', 'index')->name('admin.units.index');
                    Route::post('/store', 'store')->name('admin.units.store');
                    Route::post('/update-unit/{id}', 'update')->name('admin.units.update');
                    Route::delete('/delete-unit/{id}', 'destroy');
                    Route::get('/search', 'search')->name('admin.units.search');
                });
                Route::prefix('admin/equipments')->controller(EquipmentController::class)->group(function () {
                    Route::get('/', 'index')->name('admin.equipments.index');
                    Route::post('/store', 'store')->name('admin.equipments.store');
                    Route::post('/update-equipment/{id}', 'update')->name('admin.equipments.update');
                    Route::delete('/delete-equipment/{id}', 'destroy');
                    Route::get('/search', 'search')->name('admin.equipments.search');
                });
                Route::prefix('admin/master-lists')->controller(MasterListController::class)->group(function () {
                    Route::get('/', 'index')->name('admin.master-lists.index');
                    Route::post('/store', 'store')->name('admin.master-lists.store');
                    Route::post('/update-master-list/{id}', 'update')->name('admin.master-lists.update');
                    Route::delete('/delete-master-list/{id}', 'destroy');
                    Route::get('/search', 'search')->name('admin.master-lists.search');
                });
            });
        });
    });
    Route::prefix('control')->as('control.')->middleware(SingleLogin::class)->middleware(ResumeDraft::class)->group(function () {
        // Rencana & Detail (biar lengkap, bisa kamu tambah belakangan)
        Route::resource('schedule-plans', SchedulePlanController::class)->only([
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy',
        ]);

        Route::scopeBindings()->group(function () {
            Route::resource('schedule-plans.details', ScheduleDetailController::class)
                ->shallow()
                ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
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

        // =========================
        // API kecil buat dropdown schedule/target (AJAX)
        // =========================
        Route::get('api/schedules', [ScheduleDetailController::class, 'options'])
            ->name('api.schedules.options'); // ?type=leader_checks_operator&date=YYYY-MM-DD&shift=1
    });
});

// Checksheet CRUD (Sementara)
Route::controller(ChecksheetFormController::class)->group(function () {
    Route::get("/checksheet/add", 'create')->name('form.checksheet.create');
    Route::post("/checksheet/add", 'store')->name('form.checksheet.create.store');
    Route::get("/checksheet/{id}/edit", 'edit')->name('form.checksheet.edit');
    Route::put("/checksheet/{id}/edit", 'update')->name('form.checksheet.edit.update');
});
