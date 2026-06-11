<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RuangKelasController;
use App\Http\Controllers\JadwalTetapController;
use App\Http\Controllers\JadwalImportController;
use App\Http\Controllers\DosenImportController;
use App\Http\Controllers\ReservasiController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GreedyLogController;

/*
|--------------------------------------------------------------------------
| Redirect Awal
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::view('/profile', 'profile.edit')->name('profile.edit');
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'admin'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | RUANG
        |--------------------------------------------------------------------------
        */

        Route::resource('ruang', RuangKelasController::class);

        /*
        |--------------------------------------------------------------------------
        | JADWAL
        |--------------------------------------------------------------------------
        */

        Route::resource('jadwal', JadwalTetapController::class);

        Route::get('/jadwal-alokasi', [JadwalTetapController::class, 'formAlokasi'])
            ->name('jadwal.alokasi');

        Route::post('/jadwal-alokasi', [JadwalTetapController::class, 'prosesAlokasi'])
            ->name('jadwal.proses-alokasi');

        Route::get('/jadwal-import', [JadwalTetapController::class, 'formImport'])
            ->name('jadwal.import');

        Route::post('/jadwal-import', [JadwalTetapController::class, 'prosesImport'])
            ->name('jadwal.proses-import');

        Route::get('/jadwal-template', [JadwalTetapController::class, 'downloadTemplate'])
            ->name('jadwal.template');

        /*
        |--------------------------------------------------------------------------
        | IMPORT JADWAL EXCEL
        |--------------------------------------------------------------------------
        */

        Route::get('/jadwal-excel-import', [JadwalImportController::class, 'formImport'])
            ->name('jadwal.excel-import');

        Route::post('/jadwal-excel-import', [JadwalImportController::class, 'prosesImport'])
            ->name('jadwal.proses-excel-import');

        Route::get('/jadwal-excel-template', [JadwalImportController::class, 'downloadTemplate'])
            ->name('jadwal.excel-template');

        /*
        |--------------------------------------------------------------------------
        | IMPORT DOSEN
        |--------------------------------------------------------------------------
        */

        Route::get('/dosen-import', [DosenImportController::class, 'form'])
            ->name('dosen-import.form');

        Route::get('/dosen-import/manual', [DosenImportController::class, 'formManual'])
            ->name('dosen-import.manual');

        Route::post('/dosen-import/scan', [DosenImportController::class, 'scan'])
            ->name('dosen-import.scan');

        Route::post('/dosen-import/simpan', [DosenImportController::class, 'simpan'])
            ->name('dosen-import.simpan');

        /*
        |--------------------------------------------------------------------------
        | RESERVASI ADMIN
        |--------------------------------------------------------------------------
        */

        Route::get('/reservasi', [ReservasiController::class, 'adminIndex'])
            ->name('reservasi.index');

        Route::get('/reservasi/{reservasi}', [ReservasiController::class, 'adminShow'])
            ->name('reservasi.show');

        Route::post('/reservasi/{reservasi}/setujui', [ReservasiController::class, 'setujui'])
            ->name('reservasi.setujui');

        Route::post('/reservasi/{reservasi}/tolak', [ReservasiController::class, 'tolak'])
            ->name('reservasi.tolak');

        /*
        |--------------------------------------------------------------------------
        | LAPORAN
        |--------------------------------------------------------------------------
        */

        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('laporan.index');

        Route::get('/laporan/jadwal', [LaporanController::class, 'jadwalSemester'])
            ->name('laporan.jadwal');

        Route::get('/laporan/reservasi', [LaporanController::class, 'reservasi'])
            ->name('laporan.reservasi');

        Route::get('/laporan/utilisasi', [LaporanController::class, 'utilisasiRuang'])
            ->name('laporan.utilisasi');

        /*
        |--------------------------------------------------------------------------
        | VISUALISASI ALGORITMA GREEDY
        |--------------------------------------------------------------------------
        */

        Route::match(['get', 'post'], '/greedy-log', [GreedyLogController::class, 'index'])
            ->name('greedy.log');

        Route::get('/greedy-api', [GreedyLogController::class, 'api'])
            ->name('greedy.api');

        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */

        Route::resource('users', UserController::class);

        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');

        Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password');
    });

/*
|--------------------------------------------------------------------------
| DOSEN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:dosen'])
    ->prefix('dosen')
    ->name('dosen.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'dosen'])
            ->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| MAHASISWA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:mahasiswa'])
    ->prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'mahasiswa'])
            ->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| RESERVASI USER
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('reservasi')
    ->name('reservasi.')
    ->group(function () {

        Route::get('/', [ReservasiController::class, 'myReservasi'])
            ->name('index');

        Route::get('/buat', [ReservasiController::class, 'create'])
            ->name('create');

        Route::post('/', [ReservasiController::class, 'store'])
            ->name('store');

        Route::get('/{reservasi}', [ReservasiController::class, 'show'])
            ->name('show');

        Route::patch('/{reservasi}/batalkan', [ReservasiController::class, 'batalkan'])
            ->name('batalkan');
    });

/*
|--------------------------------------------------------------------------
| KALENDER
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('kalender')
    ->name('kalender.')
    ->group(function () {

        Route::get('/', [KalenderController::class, 'index'])
            ->name('index');

        Route::get('/ruang/{ruang}', [KalenderController::class, 'ruang'])
            ->name('ruang');
    });

/*
|--------------------------------------------------------------------------
| NOTIFIKASI
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('notifikasi')
    ->name('notifikasi.')
    ->group(function () {

        Route::get('/', [NotifikasiController::class, 'index'])
            ->name('index');

        Route::delete('/', [NotifikasiController::class, 'hapusSemua'])
            ->name('hapus-semua');

        Route::delete('/{notifikasi}', [NotifikasiController::class, 'destroy'])
            ->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('api')
    ->name('api.')
    ->group(function () {

        Route::get('/ruang/ketersediaan', [RuangKelasController::class, 'cekKetersediaan'])
            ->name('ruang.ketersediaan');

        Route::get('/jadwal/cek-konflik', [JadwalTetapController::class, 'apiCekKonflik'])
            ->name('jadwal.cek-konflik');

        Route::post('/reservasi/cek', [ReservasiController::class, 'apiCekKetersediaan'])
            ->name('reservasi.cek');

        Route::get('/kalender/events', [KalenderController::class, 'apiEvents'])
            ->name('kalender.events');

        Route::get('/notifikasi/jumlah', [NotifikasiController::class, 'apiJumlah'])
            ->name('notifikasi.jumlah');
    });