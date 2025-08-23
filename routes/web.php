<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckRoleIsAdmin;
use App\Http\Middleware\CheckRoleMinUser;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\CheckIncompleteInput;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChecksheetController;
use App\Http\Controllers\Kalibrasi\APIController;
use App\Http\Controllers\Kalibrasi\PrintController;
use App\Http\Controllers\Kalibrasi\ReportController;
use App\Http\Controllers\Kalibrasi\Admin\UnitController;
use App\Http\Controllers\Kalibrasi\Admin\UserController;
use App\Http\Controllers\Kalibrasi\Admin\StandardController;
use App\Http\Controllers\Kalibrasi\Admin\EquipmentController;
use App\Http\Controllers\Kalibrasi\Admin\MasterListController;
use App\Http\Controllers\Kalibrasi\Input\RepairDataController;
use App\Http\Controllers\Kalibrasi\Input\NewEquipmentController;
use App\Http\Controllers\Kalibrasi\Input\CalibrationDataController;
use App\Http\Middleware\CheckAppAuthentication;

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
    Route::prefix('kalibrasi')->group(function () {
        Route::get('/print-label/{id}', [PrintController::class, 'label'])->name('kalibrasi.print.label');
        Route::middleware(CheckRoleMinUser::class)->controller(APIController::class)->group(function () {
            // API data
            Route::get('/count-equipments/{type_id}', 'countEquipments')->name('kalibrasi.api.count.equipments');
            Route::get('/get-masterlist/{id_num}', 'getMasterList')->name('kalibrasi.api.get.masterlist');
            Route::get('/get-actual-value/{id}', 'getActualValue')->name('kalibrasi.api.get.actual.value');
            Route::get('/get-repair-data/{id}', 'getRepairData')->name('kalibrasi.api.get.repair.data');
        });
        Route::middleware(CheckIncompleteInput::class)->group(function () {
            Route::controller(PrintController::class)->group(function () {
                Route::get('/print-report-masterlist/{id}', 'reportMasterlist')->name('kalibrasi.print.report.masterlist');
                Route::get('/print-report-repair/{id}', 'reportRepair')->name('kalibrasi.print.report.repair');
            });
            Route::controller(ReportController::class)->group(function () {
                Route::get('/report', 'menu')->name('kalibrasi.report.menu');
                Route::get('/report/search', 'search')->name('kalibrasi.report.search');
                Route::post('/report/masterlist', 'masterlist')->name('kalibrasi.report.masterlist');
                Route::post('/report/repairs', 'repairs')->name('kalibrasi.report.repairs');
            });
        });

        // hanya user ke atas
        Route::middleware(CheckRoleMinUser::class)->middleware(CheckIncompleteInput::class)->group(function () {
            Route::get('/input/new-equipment', [NewEquipmentController::class, 'create'])->name('kalibrasi.input.new.equipment');
            Route::post('/input/new-equipment', [NewEquipmentController::class, 'store'])->name('kalibrasi.store.equipment');

            Route::get('/input/calibration-data', [CalibrationDataController::class, 'create'])->name('kalibrasi.input.calibration.data');
            Route::post('/input/calibration-data', [CalibrationDataController::class, 'store'])->name('kalibrasi.store.calibration');
            Route::post('/input/calibration-data/{id}', [CalibrationDataController::class, 'edit'])->name('kalibrasi.edit.calibration');

            Route::get('/input/repair-data', [RepairDataController::class, 'create'])->name('kalibrasi.input.repair');
            Route::post('/input/repair-data', [RepairDataController::class, 'store'])->name('kalibrasi.store.repair');
            Route::post('/input/repair-data/{id}', [RepairDataController::class, 'edit'])->name('kalibrasi.edit.repair');

            Route::post('/standards/store', [DashboardController::class, 'store'])->name('kalibrasi.standards.store');

            // hanya admin
            Route::middleware(CheckRoleIsAdmin::class)->group(function () {
                Route::prefix('admin/users')->controller(UserController::class)->group(function () {
                    Route::get('/', 'index')->name('kalibrasi.admin.users.index');
                    Route::post('/store', 'store')->name('kalibrasi.admin.users.store');
                    Route::post('/update-user/{id}', 'update')->name('kalibrasi.admin.users.update');
                    Route::delete('/delete-user/{id}', 'destroy');
                    Route::get('/search', 'search')->name('kalibrasi.admin.users.search');
                });
                Route::prefix('admin/standards')->controller(StandardController::class)->group(function () {
                    Route::get('/', 'index')->name('kalibrasi.admin.standards.index');
                    Route::post('/update-standard/{id}', 'update')->name('kalibrasi.admin.standards.update');
                    Route::post('/store', 'store')->name('kalibrasi.admin.standards.store');
                    Route::delete('/delete-standard/{id}', 'destroy');
                    Route::get('/search', 'search')->name('kalibrasi.admin.standards.search');
                });
                Route::prefix('admin/units')->controller(UnitController::class)->group(function () {
                    Route::get('/', 'index')->name('kalibrasi.admin.units.index');
                    Route::post('/store', 'store')->name('kalibrasi.admin.units.store');
                    Route::post('/update-unit/{id}', 'update')->name('kalibrasi.admin.units.update');
                    Route::delete('/delete-unit/{id}', 'destroy');
                    Route::get('/search', 'search')->name('kalibrasi.admin.units.search');
                });
                Route::prefix('admin/equipments')->controller(EquipmentController::class)->group(function () {
                    Route::get('/', 'index')->name('kalibrasi.admin.equipments.index');
                    Route::post('/store', 'store')->name('kalibrasi.admin.equipments.store');
                    Route::post('/update-equipment/{id}', 'update')->name('kalibrasi.admin.equipments.update');
                    Route::delete('/delete-equipment/{id}', 'destroy');
                    Route::get('/search', 'search')->name('kalibrasi.admin.equipments.search');
                });
                Route::prefix('admin/master-lists')->controller(MasterListController::class)->group(function () {
                    Route::get('/', 'index')->name('kalibrasi.admin.master-lists.index');
                    Route::post('/store', 'store')->name('kalibrasi.admin.master-lists.store');
                    Route::post('/update-master-list/{id}', 'update')->name('kalibrasi.admin.master-lists.update');
                    Route::delete('/delete-master-list/{id}', 'destroy');
                    Route::get('/search', 'search')->name('kalibrasi.admin.master-lists.search');
                });
            });
        });
    });
    Route::prefix('control')->group(function () {
        Route::get('/input/production', [NewEquipmentController::class, 'create'])->name('input.production');
        Route::controller(ChecksheetController::class)->group(function () {
            Route::get('/checksheet', 'index')->name('checksheet.index');
            Route::get('/checksheet/a', 'wizard_index')->name('checksheet.wizard_index');
        });
    });
});
