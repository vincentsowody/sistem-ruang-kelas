# Sistem Penjadwalan & Reservasi Ruang Kelas

## Persyaratan
- PHP >= 8.1
- Composer
- MySQL >= 8.0
- Node.js >= 18 (untuk Tailwind)

---

## Langkah Instalasi

### 1. Buat project Laravel baru
```bash
composer create-project laravel/laravel sistem-ruang-kelas
cd sistem-ruang-kelas
```

### 2. Install dependensi frontend
```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

### 3. Konfigurasi Tailwind
Edit `tailwind.config.js`:
```js
content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
],
```

Edit `resources/css/app.css`:
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

### 4. Install Laravel UI (autentikasi)
```bash
composer require laravel/ui
php artisan ui bootstrap --auth
```

### 5. Konfigurasi database
Edit file `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_ruang_kelas
DB_USERNAME=root
DB_PASSWORD=
```

Buat database di MySQL:
```sql
CREATE DATABASE db_ruang_kelas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Salin semua file migration
Salin semua file dari folder `database/migrations/` ke folder migration Laravel kamu.

### 7. Salin semua file Model
Salin semua file dari folder `app/Models/` ke folder model Laravel kamu.

### 8. Jalankan migration & seeder
```bash
php artisan migrate --seed
```

### 9. Jalankan development server
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

### 10. Akun default (dari seeder)
| Role      | Email                  | Password |
|-----------|------------------------|----------|
| Admin     | admin@kampus.ac.id     | password |
| Dosen     | dosen@kampus.ac.id     | password |
| Mahasiswa | mahasiswa@kampus.ac.id | password |

---

## Struktur Database
- `users` — data pengguna + role
- `ruang_kelas` — data ruang beserta fasilitas & kapasitas
- `jadwal_tetap` — jadwal kuliah rutin per semester
- `reservasi` — pengajuan peminjaman ruang insidental
- `notifikasi` — log notifikasi yang dikirim ke pengguna
