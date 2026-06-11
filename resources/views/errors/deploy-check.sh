#!/bin/bash
# =============================================================
# CHECKLIST DEPLOYMENT — Sistem Ruang Kelas
# Jalankan script ini sebelum deployment ke server produksi:
#   bash deploy-check.sh
# =============================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

PASS=0
FAIL=0
WARN=0

ok()   { echo -e "  ${GREEN}✓${NC} $1"; ((PASS++)); }
fail() { echo -e "  ${RED}✗${NC} $1"; ((FAIL++)); }
warn() { echo -e "  ${YELLOW}⚠${NC} $1"; ((WARN++)); }

echo ""
echo -e "${CYAN}══════════════════════════════════════════${NC}"
echo -e "${CYAN}   DEPLOYMENT CHECKLIST — Ruang Kelas     ${NC}"
echo -e "${CYAN}══════════════════════════════════════════${NC}"
echo ""

# ── 1. File .env ──────────────────────────────────────────
echo -e "${CYAN}[1] Konfigurasi .env${NC}"

if [ -f ".env" ]; then
    ok ".env ditemukan"
else
    fail ".env tidak ditemukan — salin dari .env.production lalu isi"
fi

APP_DEBUG=$(grep "^APP_DEBUG=" .env 2>/dev/null | cut -d= -f2 | tr -d ' ')
if [ "$APP_DEBUG" = "false" ]; then
    ok "APP_DEBUG=false"
else
    fail "APP_DEBUG masih '$APP_DEBUG' — WAJIB false di produksi"
fi

APP_ENV=$(grep "^APP_ENV=" .env 2>/dev/null | cut -d= -f2 | tr -d ' ')
if [ "$APP_ENV" = "production" ]; then
    ok "APP_ENV=production"
else
    warn "APP_ENV='$APP_ENV' — sebaiknya 'production'"
fi

APP_KEY=$(grep "^APP_KEY=" .env 2>/dev/null | cut -d= -f2 | tr -d ' ')
if [[ "$APP_KEY" == base64:* ]] && [ ${#APP_KEY} -gt 10 ]; then
    ok "APP_KEY sudah di-set"
else
    fail "APP_KEY kosong — jalankan: php artisan key:generate"
fi

DB_PASSWORD=$(grep "^DB_PASSWORD=" .env 2>/dev/null | cut -d= -f2 | tr -d ' ')
if [ -n "$DB_PASSWORD" ]; then
    ok "DB_PASSWORD sudah di-set"
else
    fail "DB_PASSWORD kosong — isi password database yang kuat"
fi

DB_USERNAME=$(grep "^DB_USERNAME=" .env 2>/dev/null | cut -d= -f2 | tr -d ' ')
if [ "$DB_USERNAME" = "root" ]; then
    warn "DB_USERNAME=root — sebaiknya gunakan user MySQL tersendiri bukan root"
else
    ok "DB_USERNAME bukan root"
fi

SESSION_SECURE=$(grep "^SESSION_SECURE_COOKIE=" .env 2>/dev/null | cut -d= -f2 | tr -d ' ')
if [ "$SESSION_SECURE" = "true" ]; then
    ok "SESSION_SECURE_COOKIE=true"
else
    warn "SESSION_SECURE_COOKIE belum true — set ke true jika pakai HTTPS"
fi

MAIL_HOST=$(grep "^MAIL_HOST=" .env 2>/dev/null | cut -d= -f2 | tr -d ' ')
if [ "$MAIL_HOST" = "mailpit" ] || [ -z "$MAIL_HOST" ]; then
    warn "MAIL_HOST masih '$MAIL_HOST' — konfigurasi SMTP untuk fitur email reset password"
else
    ok "MAIL_HOST=$MAIL_HOST"
fi

echo ""

# ── 2. Artisan Commands ───────────────────────────────────
echo -e "${CYAN}[2] Laravel Artisan${NC}"

if php artisan --version &>/dev/null; then
    ok "PHP & Artisan berjalan"

    # Cek config cache
    if php artisan config:cache &>/dev/null; then
        ok "Config cache berhasil dibuat"
    else
        fail "Config cache gagal — cek error di atas"
    fi

    # Cek route cache
    if php artisan route:cache &>/dev/null; then
        ok "Route cache berhasil dibuat"
    else
        fail "Route cache gagal — cek error"
    fi

    # Cek view cache
    if php artisan view:cache &>/dev/null; then
        ok "View cache berhasil dibuat"
    else
        fail "View cache gagal — cek error"
    fi

    # Cek migration status
    PENDING=$(php artisan migrate:status 2>/dev/null | grep "Pending" | wc -l)
    if [ "$PENDING" -gt 0 ]; then
        warn "$PENDING migration belum dijalankan — jalankan: php artisan migrate"
    else
        ok "Semua migration sudah dijalankan"
    fi
else
    fail "PHP atau Artisan tidak bisa dijalankan"
fi

echo ""

# ── 3. File & Folder ──────────────────────────────────────
echo -e "${CYAN}[3] File & Folder${NC}"

if [ ! -f "public/.htaccess" ]; then
    fail "public/.htaccess tidak ditemukan"
else
    ok "public/.htaccess ada"
fi

if [ -d "vendor" ]; then
    ok "vendor/ ada (composer install sudah dijalankan)"
else
    fail "vendor/ tidak ada — jalankan: composer install --no-dev --optimize-autoloader"
fi

if [ -d "public/build" ]; then
    ok "public/build/ ada (assets sudah di-build)"
else
    fail "public/build/ tidak ada — jalankan: npm run build"
fi

# Cek permission storage
if [ -w "storage" ]; then
    ok "storage/ writable"
else
    fail "storage/ tidak writable — jalankan: chmod -R 775 storage bootstrap/cache"
fi

# Pastikan .env tidak ter-expose via web (hanya bisa dicek jika ada .htaccess yang benar)
if grep -q "\.env" public/.htaccess 2>/dev/null; then
    ok ".htaccess memblokir akses ke .env"
else
    warn ".htaccess mungkin tidak memblokir .env — pastikan file .env tidak bisa diakses via browser"
fi

echo ""

# ── 4. Keamanan .gitignore ────────────────────────────────
echo -e "${CYAN}[4] Git & Keamanan${NC}"

if grep -q "^\.env$" .gitignore 2>/dev/null; then
    ok ".env ada di .gitignore"
else
    fail ".env tidak ada di .gitignore — SANGAT BERBAHAYA jika ter-push ke repo"
fi

if git status 2>/dev/null | grep -q "\.env"; then
    fail ".env terdeteksi di git tracking — hapus dengan: git rm --cached .env"
else
    ok ".env tidak di-track git"
fi

echo ""

# ── HASIL ─────────────────────────────────────────────────
echo -e "${CYAN}══════════════════════════════════════════${NC}"
echo -e "  ${GREEN}LULUS: $PASS${NC}  |  ${YELLOW}PERINGATAN: $WARN${NC}  |  ${RED}GAGAL: $FAIL${NC}"
echo -e "${CYAN}══════════════════════════════════════════${NC}"

if [ "$FAIL" -gt 0 ]; then
    echo -e "\n  ${RED}✗ BELUM SIAP DEPLOY — Perbaiki $FAIL item yang gagal dulu${NC}\n"
    exit 1
elif [ "$WARN" -gt 0 ]; then
    echo -e "\n  ${YELLOW}⚠ Perhatikan $WARN peringatan sebelum deploy ke produksi${NC}\n"
    exit 0
else
    echo -e "\n  ${GREEN}✓ Siap deploy!${NC}\n"
    exit 0
fi
