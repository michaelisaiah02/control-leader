<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckRoleIsAdmin;
use App\Http\Middleware\CheckRoleMinUser;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\CheckIncompleteInput;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Middleware\CheckAppAuthentication;
use App\Http\Controllers\Kalibrasi\APIController;
use App\Http\Controllers\Kalibrasi\PrintController;
use App\Http\Controllers\Kalibrasi\ReportController;
use App\Http\Controllers\Kalibrasi\Admin\UnitController;
use App\Http\Controllers\Kalibrasi\Admin\UserController;
use App\Http\Controllers\ControlLeader\ChecksheetController;
use App\Http\Controllers\Kalibrasi\Admin\StandardController;
use App\Http\Controllers\Kalibrasi\Admin\EquipmentController;
use App\Http\Controllers\ControlLeader\SchedulePlanController;
use App\Http\Controllers\Kalibrasi\Admin\MasterListController;
use App\Http\Controllers\Kalibrasi\Input\RepairDataController;
use App\Http\Controllers\ControlLeader\ScheduleDetailController;
use App\Http\Controllers\Kalibrasi\Input\NewEquipmentController;
use App\Http\Controllers\Kalibrasi\Input\CalibrationDataController;

Route::get('/ping', function () {
    return response()->json(['pong' => true]);
})->name('ping');

Route::middleware('guest')->group(function () {
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
            Route::get('/input/new-equipment', [NewEquipmentController::class, 'create'])->name('input.new.equipment');
            Route::post('/input/new-equipment', [NewEquipmentController::class, 'store'])->name('store.equipment');

            Route::get('/input/calibration-data', [CalibrationDataController::class, 'create'])->name('input.calibration.data');
            Route::post('/input/calibration-data', [CalibrationDataController::class, 'store'])->name('store.calibration');
            Route::post('/input/calibration-data/{id}', [CalibrationDataController::class, 'edit'])->name('edit.calibration');

            Route::get('/input/repair-data', [RepairDataController::class, 'create'])->name('input.repair');
            Route::post('/input/repair-data', [RepairDataController::class, 'store'])->name('store.repair');
            Route::post('/input/repair-data/{id}', [RepairDataController::class, 'edit'])->name('edit.repair');

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
    Route::prefix('control')->as('control.')->middleware(['auth'])->group(function () {

        // (opsional) landing
        Route::view('/', 'control.dashboard')->name('dashboard');

        // Rencana & Detail (biar lengkap, bisa kamu tambah belakangan)
        Route::resource('schedule-plans', SchedulePlanController::class)->only([
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy'
        ]);

        Route::scopeBindings()->group(function () {
            Route::resource('schedule-plans.details', ScheduleDetailController::class)
                ->shallow()
                ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        });

        // =========================
        // CHECKSHEET (2 step: Part A -> Part B)
        // =========================

        // Part A
        Route::get('details/{detail}/checksheets/create', [ChecksheetController::class, 'createPartA'])
            ->name('checksheets.create');

        // Part B (masih pakai detail, belum bikin draft row)
        Route::get('details/{detail}/checksheets/part-b', [ChecksheetController::class, 'showPartB'])
            ->name('checksheets.partB');

        // Final submit (save ke DB)
        Route::post('checksheets', [ChecksheetController::class, 'store'])
            ->name('checksheets.store');

        // Finalisasi & Approve
        Route::post('checksheets/{checksheet}/finalize', [ChecksheetController::class, 'finalize'])
            ->name('checksheets.finalize'); // control.checksheets.finalize

        Route::post('checksheets/{checksheet}/approve', [ChecksheetController::class, 'approve'])
            ->name('checksheets.approve'); // control.checksheets.approve

        // Lain-lain (lihat/ubah/hapus/export)
        Route::get('checksheets/{checksheet}', [ChecksheetController::class, 'show'])
            ->name('checksheets.show');
        Route::get('checksheets/{checksheet}/edit', [ChecksheetController::class, 'edit'])
            ->name('checksheets.edit');
        Route::patch('checksheets/{checksheet}', [ChecksheetController::class, 'update'])
            ->name('checksheets.update');
        Route::delete('checksheets/{checksheet}', [ChecksheetController::class, 'destroy'])
            ->name('checksheets.destroy');
        Route::get('checksheets/{checksheet}/export', [ChecksheetController::class, 'export'])
            ->name('checksheets.export');
        Route::post('/heartbeat', fn() => response()->noContent())->name('heartbeat');

        // =========================
        // API kecil buat dropdown schedule/target (AJAX)
        // =========================
        Route::get('api/schedules', [ScheduleDetailController::class, 'options'])
            ->name('api.schedules.options'); // ?type=leader_checks_operator&date=YYYY-MM-DD&shift=1
    });
});
