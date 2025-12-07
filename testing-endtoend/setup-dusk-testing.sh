#!/bin/bash

# Script untuk setup database testing Laravel Dusk
# Author: Ahmad Rian
# Date: 2025-12-07

set -e

# Navigate to project root
cd "$(dirname "$0")/.."

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║                                                           ║"
echo "║         SETUP DATABASE TESTING - MUNCAK ID               ║"
echo "║                                                           ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Database configuration
DB_NAME="muncak_id_test"
DB_USER="root"

echo -e "${YELLOW}📋 Konfigurasi Database:${NC}"
echo -e "   Database: ${DB_NAME}"
echo -e "   User: ${DB_USER}"
echo ""

# Ask for password
read -sp "🔐 Masukkan password MySQL (kosongkan jika tidak ada): " DB_PASSWORD
echo ""
echo ""

# Create database
echo -e "${BLUE}🗄️  Membuat database testing...${NC}"

if [ -z "$DB_PASSWORD" ]; then
    mysql -u "$DB_USER" -e "DROP DATABASE IF EXISTS $DB_NAME;" 2>/dev/null || true
    mysql -u "$DB_USER" -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
else
    mysql -u "$DB_USER" -p"$DB_PASSWORD" -e "DROP DATABASE IF EXISTS $DB_NAME;" 2>/dev/null || true
    mysql -u "$DB_USER" -p"$DB_PASSWORD" -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
fi

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Database '$DB_NAME' berhasil dibuat${NC}"
else
    echo -e "${RED}❌ Gagal membuat database${NC}"
    exit 1
fi

# Update .env.dusk.local
echo ""
echo -e "${BLUE}📝 Mengupdate .env.dusk.local...${NC}"

if [ ! -f .env.dusk.local ]; then
    echo -e "${YELLOW}⚠️  .env.dusk.local tidak ditemukan, membuat dari .env.dusk.example...${NC}"
    cp .env.dusk.example .env.dusk.local
fi

# Update database configuration in .env.dusk.local
sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env.dusk.local
sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env.dusk.local
sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env.dusk.local
rm -f .env.dusk.local.bak

echo -e "${GREEN}✅ .env.dusk.local berhasil diupdate${NC}"

# Generate app key if not exists
echo ""
echo -e "${BLUE}🔑 Menggenerate application key...${NC}"
php artisan key:generate --env=dusk.local
echo -e "${GREEN}✅ Application key berhasil digenerate${NC}"

# Run migrations
echo ""
echo -e "${BLUE}🔄 Menjalankan migrations...${NC}"
php artisan migrate:fresh --env=dusk.local

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Migrations berhasil dijalankan${NC}"
else
    echo -e "${RED}❌ Gagal menjalankan migrations${NC}"
    exit 1
fi

# Run seeders
echo ""
echo -e "${BLUE}🌱 Menjalankan seeders...${NC}"
php artisan db:seed --env=dusk.local

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Seeders berhasil dijalankan${NC}"
else
    echo -e "${YELLOW}⚠️  Seeders gagal atau tidak ada${NC}"
fi

# Summary
echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}║              SETUP DATABASE SELESAI! 🎉                   ║${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📋 Langkah selanjutnya:${NC}"
echo -e "   1. Edit .env.dusk.local dan isi Pusher credentials"
echo -e "   2. Jalankan: ${GREEN}./run-dusk-tests.sh${NC}"
echo ""
echo -e "${YELLOW}⚠️  Catatan:${NC}"
echo -e "   - Pastikan aplikasi berjalan di http://localhost:8000"
echo -e "   - Pastikan Pusher credentials sudah benar"
echo -e "   - Pastikan ChromeDriver sudah terinstall"
echo ""
