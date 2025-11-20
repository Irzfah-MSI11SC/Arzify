<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AkunController;

// LOGIN & LOGOUT
Route::get('/',        [AuthController::class, 'loginPage'])->name('login');
Route::post('/login',  [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// AREA YANG HARUS LOGIN
Route::middleware(['kasir.auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data
    Route::resource('kategori', KategoriController::class)->except(['show']);
    Route::resource('produk',   ProdukController::class)->except(['show']);

    // Transaksi Baru / Keranjang
    Route::get('/transaksi/new',          [TransaksiController::class, 'new'])->name('transaksi.new');
    Route::post('/transaksi/add-item',    [TransaksiController::class, 'addItem'])->name('transaksi.addItem');
    Route::post('/transaksi/update-qty',  [TransaksiController::class, 'updateQty'])->name('transaksi.updateQty');
    Route::post('/transaksi/remove-item', [TransaksiController::class, 'removeItem'])->name('transaksi.removeItem');
    Route::post('/transaksi/simpan',      [TransaksiController::class, 'simpan'])->name('transaksi.simpan');

    // Riwayat transaksi & detail
    Route::get('/transaksi',      [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/{id}', [TransaksiController::class, 'show'])->name('transaksi.show');

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

    // Akun (ganti password)
    Route::get('/akun/password',  [AkunController::class, 'passwordPage'])->name('akun.password');
    Route::post('/akun/password', [AkunController::class, 'updatePassword'])->name('akun.password.update');

    
Route::post('/produk/{id}/tambah-stok', [ProdukController::class, 'tambahStok'])
    ->name('produk.tambah-stok');

});
