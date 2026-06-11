# =============================================================
# CHECKLIST DEPLOYMENT - Sistem Ruang Kelas
# Cara jalankan: .\deploy-check.ps1
# =============================================================

$PASS = 0
$FAIL = 0
$WARN = 0

function ok($msg)   { Write-Host "  [OK]  $msg" -ForegroundColor Green;  $script:PASS++ }
function fail($msg) { Write-Host "  [X]   $msg" -ForegroundColor Red;    $script:FAIL++ }
function warn($msg) { Write-Host "  [!]   $msg" -ForegroundColor Yellow; $script:WARN++ }

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "   DEPLOYMENT CHECKLIST - Ruang Kelas    " -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Bagian 1 - Konfigurasi .env
Write-Host "[1] Konfigurasi .env" -ForegroundColor Cyan

if (Test-Path ".env") {
    ok ".env ditemukan"
    $env_content = Get-Content ".env" -Raw

    if ($env_content -match "APP_DEBUG=false") {
        ok "APP_DEBUG=false"
    } else {
        fail "APP_DEBUG masih true - WAJIB ganti ke false di produksi"
    }

    if ($env_content -match "APP_ENV=production") {
        ok "APP_ENV=production"
    } else {
        warn "APP_ENV bukan production - sebaiknya ganti ke production"
    }

    if ($env_content -match "APP_KEY=base64:.{20,}") {
        ok "APP_KEY sudah di-set"
    } else {
        fail "APP_KEY kosong - jalankan: php artisan key:generate"
    }

    $dbPasswordLine = ($env_content | Select-String "DB_PASSWORD=(.*)").Matches
    if ($dbPasswordLine.Count -gt 0) {
        $dbpass = $dbPasswordLine[0].Groups[1].Value.Trim()
        if ($dbpass -eq "") {
            fail "DB_PASSWORD kosong - isi password database yang kuat"
        } else {
            ok "DB_PASSWORD sudah di-set"
        }
    } else {
        fail "DB_PASSWORD tidak ditemukan di .env"
    }

    if ($env_content -match "DB_USERNAME=root") {
        warn "DB_USERNAME=root - sebaiknya buat user MySQL tersendiri bukan root"
    } else {
        ok "DB_USERNAME bukan root"
    }

    if ($env_content -match "SESSION_SECURE_COOKIE=true") {
        ok "SESSION_SECURE_COOKIE=true"
    } else {
        warn "SESSION_SECURE_COOKIE belum true - set ke true jika pakai HTTPS"
    }

    $mailHostLine = ($env_content | Select-String "MAIL_HOST=(.*)").Matches
    if ($mailHostLine.Count -gt 0) {
        $mailHost = $mailHostLine[0].Groups[1].Value.Trim()
        if ($mailHost -eq "mailpit" -or $mailHost -eq "") {
            warn "MAIL_HOST masih '$mailHost' - konfigurasi SMTP untuk fitur email reset password"
        } else {
            ok "MAIL_HOST sudah dikonfigurasi: $mailHost"
        }
    }

} else {
    fail ".env tidak ditemukan - salin dari .env.production lalu isi semua nilainya"
}

Write-Host ""

# Bagian 2 - PHP dan Artisan
Write-Host "[2] PHP dan Artisan" -ForegroundColor Cyan

$phpCheck = & php --version 2>&1
if ($LASTEXITCODE -eq 0) {
    $phpVersionMatch = ($phpCheck[0] | Select-String "PHP (\d+\.\d+\.\d+)").Matches
    if ($phpVersionMatch.Count -gt 0) {
        $phpVersion = $phpVersionMatch[0].Groups[1].Value
        ok "PHP $phpVersion ditemukan"
    } else {
        ok "PHP ditemukan"
    }
} else {
    fail "PHP tidak ditemukan - pastikan XAMPP PHP ada di PATH"
}

if (Test-Path "artisan") {
    ok "artisan ditemukan"

    $migrateStatus = & php artisan migrate:status 2>&1
    $pendingCount = ($migrateStatus | Select-String "Pending").Count
    if ($pendingCount -gt 0) {
        warn "$pendingCount migration belum dijalankan - jalankan: php artisan migrate"
    } else {
        ok "Semua migration sudah dijalankan"
    }
} else {
    fail "artisan tidak ditemukan - pastikan Anda berada di root folder Laravel"
}

Write-Host ""

# Bagian 3 - File dan Folder
Write-Host "[3] File dan Folder Penting" -ForegroundColor Cyan

if (Test-Path "vendor") {
    ok "vendor/ ada (composer install sudah dijalankan)"
} else {
    fail "vendor/ tidak ada - jalankan: composer install"
}

if (Test-Path "public\.htaccess") {
    ok "public/.htaccess ada"
} else {
    warn "public/.htaccess tidak ada - diperlukan agar routing Laravel berjalan di Apache XAMPP"
}

if (Test-Path "public\build") {
    ok "public/build/ ada (npm run build sudah dijalankan)"
} else {
    warn "public/build/ tidak ada - jika pakai Vite jalankan: npm run build"
}

if (Test-Path "storage\logs") {
    ok "storage/logs/ ada"
} else {
    warn "storage/logs/ tidak ada - jalankan: php artisan storage:link"
}

$errorCodes = @("403", "404", "500", "419")
$missingViews = @()
foreach ($code in $errorCodes) {
    if (-not (Test-Path "resources\views\errors\$code.blade.php")) {
        $missingViews += $code
    }
}
if ($missingViews.Count -eq 0) {
    ok "Custom error pages (403/404/500/419) sudah ada"
} else {
    $missingStr = $missingViews -join ", "
    warn "Error pages belum ada: $missingStr - salin dari file output yang sudah dibuat"
}

Write-Host ""

# Bagian 4 - Keamanan
Write-Host "[4] Keamanan" -ForegroundColor Cyan

if (Test-Path ".gitignore") {
    $gitignore = Get-Content ".gitignore" -Raw
    if ($gitignore -match '(?m)^\.env$') {
        ok ".env ada di .gitignore"
    } else {
        fail ".env tidak ada di .gitignore - BERBAHAYA jika ter-push ke repository"
    }
} else {
    warn ".gitignore tidak ditemukan"
}

if (Test-Path "public\.htaccess") {
    $htaccess = Get-Content "public\.htaccess" -Raw
    if ($htaccess -match "\.env") {
        ok ".htaccess memblokir akses ke .env"
    } else {
        warn ".htaccess mungkin tidak memblokir .env - tambahkan proteksi manual"
    }
}

$emptyMigrations = Get-ChildItem "database\migrations" -Filter "*2026_05_18*" -ErrorAction SilentlyContinue
if ($null -ne $emptyMigrations -and $emptyMigrations.Count -gt 0) {
    warn "Migration duplikat kosong ditemukan ($($emptyMigrations.Count) file) - sebaiknya dihapus"
} else {
    ok "Tidak ada migration duplikat kosong"
}

Write-Host ""

# Hasil akhir
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  LULUS : $PASS  |  PERINGATAN : $WARN  |  GAGAL : $FAIL" -ForegroundColor White
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

if ($FAIL -gt 0) {
    Write-Host "  BELUM SIAP DEPLOY - Perbaiki $FAIL item yang gagal dulu" -ForegroundColor Red
    Write-Host ""
    Write-Host "  Langkah yang wajib diselesaikan:" -ForegroundColor Yellow
    Write-Host "    1. Edit .env: set APP_DEBUG=false dan APP_ENV=production" -ForegroundColor Yellow
    Write-Host "    2. Isi DB_PASSWORD dengan password yang kuat" -ForegroundColor Yellow
    Write-Host "    3. Jalankan: php artisan key:generate (jika APP_KEY kosong)" -ForegroundColor Yellow
    Write-Host "    4. Konfigurasi MAIL_HOST untuk fitur email" -ForegroundColor Yellow
} elseif ($WARN -gt 0) {
    Write-Host "  Ada $WARN peringatan - perhatikan sebelum deploy ke produksi" -ForegroundColor Yellow
} else {
    Write-Host "  Siap deploy!" -ForegroundColor Green
}

Write-Host ""