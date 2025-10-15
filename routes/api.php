<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route untuk mendapatkan data komoditas
Route::get('/get-komoditas-data', [ApiController::class, 'getKomoditasData'])->name('api.komoditas.data');

Route::post('/insert_data', [ApiController::class, 'insertFromMobile']) ->name('api.insert.data');

// Route untuk mengambil data komoditas
Route::get('/datakomoditas', [ApiController::class, 'getDataKomoditas']);

// Route untuk memperbarui harga hari ini
Route::put('komoditas/harga-hari-ini/{id}', [ApiController::class, 'updateHargaHariIni']);