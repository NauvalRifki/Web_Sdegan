<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SumberDataController;
use App\Http\Controllers\penggunaController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\RekapOpController;
use App\Http\Controllers\RekapVeriController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerifikatorController;
use App\Http\Controllers\DataController;
use App\Http\Middleware\CheckRole;
use League\CommonMark\Extension\SmartPunct\DashParser;

// Rute untuk login dan logout
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

//api dashboard
Route::get('/api/komoditas', [DashboardController::class, 'getKomoditas']);
Route::get('/api/filter', [DashboardController::class, 'filter']);
Route::get('/api/loadTotalData', [DashboardController::class, 'AllData']);
Route::get('/api/grafik', [DashboardController::class, 'grafik']);
Route::get('/api/grafikAll', [DashboardController::class, 'grafikAll']);
Route::get('/api/grafikTerbaru', [DashboardController::class, 'grafikTerbaru']);

//pengguna umum
Route::get('/dashboard_umum', [DashboardController::class, 'containerbesar'])->name('dashboard_umum');
Route::get('unduh-pdf', [DashboardController::class, 'generatePDFumum'])->name('unduh_umum.pdf');

    
Route::post('/send-notifikasi', [ApiController::class, 'send']);

Route::middleware([CheckRole::class.':admin'])->group(function () {
    Route::get('/admin/index', [AdminController::class, 'containerbesar'])->name('dashboard_admin.index');

    Route::get('admin/SumberData', [SumberDataController::class, 'index'])->name('SumberData.index');
    Route::get('admin/SumberData/create', [SumberDataController::class, 'create'])->name('SumberData.create');
    Route::post('admin/SumberData', [SumberDataController::class, 'store'])->name('SumberData.store');
    Route::get('admin/SumberData/{SumberData}', [SumberDataController::class, 'show'])->name('SumberData.show');
    Route::get('admin/SumberData/{SumberData}/edit', [SumberDataController::class, 'edit'])->name('SumberData.edit');
    Route::put('admin/SumberData/{SumberData}', [SumberDataController::class, 'update'])->name('SumberData.update');
    Route::delete('admin/SumberData/{SumberData}', [SumberDataController::class, 'destroy'])->name('SumberData.destroy');

    Route::get('admin/pengguna', [penggunaController::class, 'index'])->name('pengguna.index');
    Route::get('admin/pengguna/create', [penggunaController::class, 'create'])->name('pengguna.create');
    Route::post('admin/pengguna', [penggunaController::class, 'store'])->name('pengguna.store');
    Route::get('admin/pengguna/{pengguna}', [penggunaController::class, 'show'])->name('pengguna.show');
    Route::get('admin/pengguna/{pengguna}/edit', [penggunaController::class, 'edit'])->name('pengguna.edit');
    Route::put('admin/pengguna/{pengguna}', [penggunaController::class, 'update'])->name('pengguna.update');
    Route::delete('admin/pengguna/{pengguna}', [penggunaController::class, 'destroy'])->name('pengguna.destroy');

    Route::get('/admin/data_pangan', [DataController::class, 'index'])->name('dashboard_admin.sub_menu.data_pangan');
    Route::get('/admin/input_data', [DataController::class, 'showForm'])->name('dashboard_admin.sub_menu.input_data');
    Route::post('/admin/input_data', [DataController::class, 'insert'])->name('dashboard_admin.sub_menu.data.insert');
    Route::get('/admin/edit_entry/{id}', [DataController::class, 'edit'])->name('dashboard_admin.sub_menu.data.edit');
    Route::put('/admin/data/{id}', [DataController::class, 'update'])->name('dashboard_admin.sub_menu.data.update');
    Route::delete('admin/sub_menu/data/{id}/destroy', [DataController::class, 'destroy'])->name('dashboard_admin.sub_menu.data.destroy');
    Route::post('/admin/data-pangan/bulk-delete', [DataController::class, 'bulkDelete'])->name('dashboard_admin.sub_menu.data.bulkDelete');


    Route::get('/admin/verify_data', [DataController::class, 'verifyadmin'])->name('dashboard_admin.sub_menu.verify_data');
    Route::put('/admin/verifikasi/data/{id}', [DataController::class, 'updateStatusVerifikasi'])->name('dashboard_admin.sub_menu.update_status_verifikasi');
    Route::post('/admin/kembalikan-data', [DataController::class, 'kembalikanData'])->name('dashboard_admin.sub_menu.kembalikan_data');
    Route::post('/admin/save_data', [AdminController::class, 'saveData'])->name('dashboard_admin.sub_menu.save_data');

    Route::get('/admin/rekap', [RekapController::class, 'index'])->name('rekap.index');
    Route::get('/export/rekap', [RekapController::class, 'exportRekap'])->name('export.rekap');
    Route::get('/export/het', [RekapController::class, 'exportHet'])->name('export.het');

    Route::get('/admin/unduh-pdf', [AdminController::class, 'generatePdf'])->name('unduh.pdf');

});

Route::middleware([CheckRole::class.':operator'])->group(function () {
    Route::get('/operator/index', [OperatorController::class, 'containerbesar'])->name('dashboard_operator.index');
    Route::get('/operator/data_pangan', [DataController::class, 'indexoperator'])->name('dashboard_operator.data_pangan');
    Route::get('/operator/input_data', [DataController::class, 'showFormoperator'])->name('dashboard_operator.input_data');
    Route::post('/operator/input_data', [DataController::class, 'insertoperator'])->name('dashboard_operator.data.insert');
    Route::get('/operator/edit_entry/{id}', [DataController::class, 'editoperator'])->name('dashboard_operator.data.edit');
    Route::put('/operator/data/{id}', [DataController::class, 'updateoperator'])->name('dashboard_operator.data.update');
    Route::post('/operator/data-pangan/bulk-delete', [DataController::class, 'bulkDelete'])->name('dashboard_operator.data.bulkDelete');

    Route::get('/operator/rekap', [RekapOpController::class, 'index'])->name('rekap.index');
    Route::get('/export/rekapop', [RekapOpController::class, 'exportRekapOp'])->name('export.rekap_op');
    Route::get('/export/het_op', [RekapOpController::class, 'exportHetOp'])->name('export.het_op');

    Route::get('/operator/unduh-pdf', [OperatorController::class, 'generatePdfop'])->name('unduh_op.pdf');

});

Route::middleware([CheckRole::class.':verifikator'])->group(function () {
    Route::get('/verifikator/index', [VerifikatorController::class, 'containerbesar'])->name('dashboard_verifikator.index');
    Route::get('/verifikator/verify_data', [DataController::class, 'verifyverifikator'])->name('dashboard_verifikator.verify_data');
    Route::post('/verifikator/save', [VerifikatorController::class, 'saveDataverifikator'])->name('dashboard_verifikator.save_data');

    Route::put('/verifikator/verifikasi/data/{id}', [DataController::class, 'updateStatusVerifikasiVer'])->name('dashboard_verifikator.update_status_verifikasi');
    Route::post('/verifikator/kembalikan-data', [DataController::class, 'kembalikanDataVer'])->name('dashboard_verifikator.kembalikan_data');

    Route::get('/verifikator/rekap', [RekapVeriController::class, 'index'])->name('rekap.index');
    Route::get('/export/rekap_veri', [RekapVeriController::class, 'exportRekap'])->name('export.rekap_veri');
    Route::get('/export/het_veri', [RekapVeriController::class, 'exportHet'])->name('export.het_veri');

    Route::get('/verifikator/unduh-pdf', [VerifikatorController::class, 'generatePdfveri'])->name('unduh_veri.pdf');
});






