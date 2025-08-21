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
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware(CheckAppAuthentication::class)->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::middleware(CheckIncompleteInput::class)->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
    Route::prefix('kalibrasi')->group(function () {
        Route::get('/print-label/{id}', [PrintController::class, 'label'])->name('kalibrasi.print.label');
        Route::middleware(CheckRoleMinUser::class)->group(function () {
            // API data
            Route::get('/count-equipments/{type_id}', [APIController::class, 'countEquipments'])->name('kalibrasi.api.count.equipments');
            Route::get('/get-masterlist/{id_num}', [APIController::class, 'getMasterList'])->name('kalibrasi.api.get.masterlist');
            Route::get('/get-actual-value/{id}', [APIController::class, 'getActualValue'])->name('kalibrasi.api.get.actual.value');
            Route::get('/get-repair-data/{id}', [APIController::class, 'getRepairData'])->name('kalibrasi.api.get.repair.data');
        });
        Route::middleware(CheckIncompleteInput::class)->group(function () {
            Route::get('/print-report-masterlist/{id}', [PrintController::class, 'reportMasterlist'])->name('kalibrasi.print.report.masterlist');
            Route::get('/print-report-repair/{id}', [PrintController::class, 'reportRepair'])->name('kalibrasi.print.report.repair');
            Route::get('/report', [ReportController::class, 'menu'])->name('kalibrasi.report.menu');
            Route::get('/report/search', [ReportController::class, 'search'])->name('kalibrasi.report.search');
            Route::post('/report/masterlist', [ReportController::class, 'masterlist'])->name('kalibrasi.report.masterlist');
            Route::post('/report/repairs', [ReportController::class, 'repairs'])->name('kalibrasi.report.repairs');
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
                // Route::resource('users', UserController::class);
                Route::prefix('admin/users')->group(function () {
                    Route::get('/', [UserController::class, 'index'])->name('kalibrasi.admin.users.index');
                    Route::post('/store', [UserController::class, 'store'])->name('kalibrasi.admin.users.store');
                    Route::post('/update-user/{id}', [UserController::class, 'update'])->name('kalibrasi.admin.users.update');
                    Route::delete('/delete-user/{id}', [UserController::class, 'destroy']);
                    Route::get('/search', [UserController::class, 'search'])->name('kalibrasi.admin.users.search');
                });
                Route::prefix('admin/standards')->group(function () {
                    Route::get('/', [StandardController::class, 'index'])->name('kalibrasi.admin.standards.index');
                    Route::post('/update-standard/{id}', [StandardController::class, 'update'])->name('kalibrasi.admin.standards.update');
                    Route::post('/store', [StandardController::class, 'store'])->name('kalibrasi.admin.standards.store');
                    Route::delete('/delete-standard/{id}', [StandardController::class, 'destroy']);
                    Route::get('/search', [StandardController::class, 'search'])->name('kalibrasi.admin.standards.search');
                });
                Route::prefix('admin/units')->group(function () {
                    Route::get('/', [UnitController::class, 'index'])->name('kalibrasi.admin.units.index');
                    Route::post('/store', [UnitController::class, 'store'])->name('kalibrasi.admin.units.store');
                    Route::post('/update-unit/{id}', [UnitController::class, 'update'])->name('kalibrasi.admin.units.update');
                    Route::delete('/delete-unit/{id}', [UnitController::class, 'destroy']);
                    Route::get('/search', [UnitController::class, 'search'])->name('kalibrasi.admin.units.search');
                });
                Route::prefix('admin/equipments')->group(function () {
                    Route::get('/', [EquipmentController::class, 'index'])->name('kalibrasi.admin.equipments.index');
                    Route::post('/store', [EquipmentController::class, 'store'])->name('kalibrasi.admin.equipments.store');
                    Route::post('/update-equipment/{id}', [EquipmentController::class, 'update'])->name('kalibrasi.admin.equipments.update');
                    Route::delete('/delete-equipment/{id}', [EquipmentController::class, 'destroy']);
                    Route::get('/search', [EquipmentController::class, 'search'])->name('kalibrasi.admin.equipments.search');
                });
                Route::prefix('admin/master-lists')->group(function () {
                    Route::get('/', [MasterListController::class, 'index'])->name('kalibrasi.admin.master-lists.index');
                    Route::post('/store', [MasterListController::class, 'store'])->name('kalibrasi.admin.master-lists.store');
                    Route::post('/update-master-list/{id}', [MasterListController::class, 'update'])->name('kalibrasi.admin.master-lists.update');
                    Route::delete('/delete-master-list/{id}', [MasterListController::class, 'destroy']);
                    Route::get('/search', [MasterListController::class, 'search'])->name('kalibrasi.admin.master-lists.search');
                });
            });
        });
    });
    Route::prefix('control')->group(function () {
        Route::get('/input/production', [NewEquipmentController::class, 'create'])->name('input.production');
    });

    Route::controller(ChecksheetController::class)->group(function () {
        Route::get('/checksheet', 'index')->name('checksheet.index');
        Route::get('/checksheet/step-by-step', 'wizard_index')->name('checksheet.wizard_index');
    });
});
