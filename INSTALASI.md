# Instalasi & Setup Import Excel

## Langkah 1 — Install PhpSpreadsheet

Jalankan di terminal dalam folder project Laravel:
```bash
composer require phpoffice/phpspreadsheet
```

Tunggu sampai selesai (sekitar 1-2 menit).

---

## Langkah 2 — Salin file-file berikut

| File sumber (dari ZIP)                                    | Tujuan di Laravel                                              |
|-----------------------------------------------------------|----------------------------------------------------------------|
| `app/Services/SlotWaktuMapper.php`                        | `app/Services/SlotWaktuMapper.php`                             |
| `app/Http/Controllers/JadwalImportController.php`         | `app/Http/Controllers/JadwalImportController.php`              |
| `resources/views/admin/jadwal/import-excel.blade.php`     | `resources/views/admin/jadwal/import-excel.blade.php`          |

---

## Langkah 3 — Tambahkan routes ke web.php

Buka `routes/web.php`, di dalam group `middleware(['auth', 'role:admin'])`,
tambahkan baris berikut SEBELUM `Route::resource('jadwal', ...)`:

```php
use App\Http\Controllers\JadwalImportController;

// Import Excel
Route::get('/jadwal-excel-import',   [JadwalImportController::class, 'formImport'])      ->name('jadwal.excel-import');
Route::post('/jadwal-excel-import',  [JadwalImportController::class, 'prosesImport'])     ->name('jadwal.proses-excel-import');
Route::get('/jadwal-excel-template', [JadwalImportController::class, 'downloadTemplate']) ->name('jadwal.excel-template');
```

---

## Langkah 4 — Tambahkan link di navbar/halaman jadwal

Di `resources/views/admin/jadwal/index.blade.php`, tambahkan tombol Import Excel:

```html
<a href="{{ route('admin.jadwal.excel-import') }}"
   class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2.5 rounded-xl transition text-sm">
    <i class="fa-solid fa-file-excel"></i> Import Excel
</a>
```

---

## Langkah 5 — Sesuaikan Slot Waktu

Edit file `app/Services/SlotWaktuMapper.php` bagian `$slots`
sesuai dengan jam perkuliahan kampus Anda:

```php
private static array $slots = [
    1  => ['mulai' => '07:30', 'selesai' => '08:20'],
    2  => ['mulai' => '08:20', 'selesai' => '09:10'],
    // dst...
];
```

---

## Langkah 6 — Test

1. Buka halaman Admin → Jadwal → klik **Import Excel**
2. Unduh template `.xlsx`
3. Isi data sesuai format, simpan
4. Upload dan klik **Import Sekarang**

---

## Format kolom yang wajib ada di Excel

| Kolom          | Contoh                         | Keterangan                          |
|----------------|--------------------------------|-------------------------------------|
| Kode MK        | TIK1021                        | Opsional                            |
| Nama MK        | Pancasila                      | WAJIB                               |
| SKS            | 2                              | Angka                               |
| Semester       | 1                              | Angka 1-8                           |
| Kelas          | A                              | Huruf                               |
| Pengajar       | Dr. Budi / Siti Rahayu         | Pisahkan 2 dosen dengan /           |
| Hari           | KAMIS                          | Huruf besar/kecil boleh             |
| Slot Waktu     | 4 - 5                          | Format: "X - Y" atau "X-Y"          |
| Ruang          | JTE-04                         | Harus sama dengan kode di database  |
| Program Studi  | Teknik Informatika             | Opsional jika diisi di form         |
