#!/bin/bash

# Script untuk menjalankan Laravel Dusk Tests untuk Muncak ID (Pusher)
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

# Banner
echo -e "${BLUE}"
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║                                                           ║"
echo "║         MUNCAK ID - LARAVEL DUSK TESTING (PUSHER)        ║"
echo "║                                                           ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Check if .env.dusk.local exists
if [ ! -f .env.dusk.local ]; then
    echo -e "${YELLOW}⚠️  .env.dusk.local tidak ditemukan${NC}"
    echo -e "${BLUE}📝 Membuat .env.dusk.local dari .env.dusk.example...${NC}"
    cp .env.dusk.example .env.dusk.local
    echo -e "${GREEN}✅ .env.dusk.local berhasil dibuat${NC}"
    echo -e "${YELLOW}⚠️  Silakan edit .env.dusk.local dan isi Pusher credentials${NC}"
    exit 1
fi

# Function to run specific test
run_test() {
    local test_file=$1
    local test_name=$2
    
    echo -e "${BLUE}🧪 Menjalankan: ${test_name}${NC}"
    php artisan dusk "tests/Browser/${test_file}"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ ${test_name} - PASSED${NC}"
    else
        echo -e "${RED}❌ ${test_name} - FAILED${NC}"
        exit 1
    fi
    echo ""
}

# Menu
echo -e "${YELLOW}Pilih test yang ingin dijalankan:${NC}"
echo ""
echo "  1. TC-01: Admin Sign In"
echo "  2. TC-02: Membuat Live Stream Baru"
echo "  3. TC-03: Memulai Broadcasting"
echo "  4. TC-04: Menghentikan Broadcasting"
echo "  5. TC-05: Melihat Daftar Live Stream"
echo "  6. TC-06: Menonton Live Stream"
echo "  7. TC-07: Melihat Klasifikasi AI"
echo "  8. TC-08: Mengirim Chat Message"
echo "  9. TC-09: Menerima Chat Message"
echo " 10. TC-10: Update Viewer Count"
echo " 11. TC-11: Klasifikasi AI Otomatis"
echo " 12. Jalankan SEMUA Test"
echo " 13. Jalankan Test Admin (TC-01 sampai TC-04)"
echo " 14. Jalankan Test Viewer (TC-05 sampai TC-07)"
echo " 15. Jalankan Test Chat (TC-08 dan TC-09)"
echo "  0. Keluar"
echo ""
read -p "Masukkan pilihan (0-15): " choice

case $choice in
    1)
        run_test "AdminSignInTest.php" "TC-01: Admin Sign In"
        ;;
    2)
        run_test "CreateLiveStreamTest.php" "TC-02: Membuat Live Stream Baru"
        ;;
    3)
        run_test "StartBroadcastingTest.php" "TC-03: Memulai Broadcasting"
        ;;
    4)
        run_test "StopBroadcastingTest.php" "TC-04: Menghentikan Broadcasting"
        ;;
    5)
        run_test "ViewLiveStreamListTest.php" "TC-05: Melihat Daftar Live Stream"
        ;;
    6)
        run_test "WatchLiveStreamTest.php" "TC-06: Menonton Live Stream"
        ;;
    7)
        run_test "ViewAIClassificationTest.php" "TC-07: Melihat Klasifikasi AI"
        ;;
    8)
        run_test "SendChatMessageTest.php" "TC-08: Mengirim Chat Message"
        ;;
    9)
        run_test "ReceiveChatMessageTest.php" "TC-09: Menerima Chat Message"
        ;;
    10)
        run_test "UpdateViewerCountTest.php" "TC-10: Update Viewer Count"
        ;;
    11)
        run_test "AutomaticAIClassificationTest.php" "TC-11: Klasifikasi AI Otomatis"
        ;;
    12)
        echo -e "${BLUE}🚀 Menjalankan SEMUA Test...${NC}"
        echo ""
        php artisan dusk
        ;;
    13)
        echo -e "${BLUE}🚀 Menjalankan Test Admin (TC-01 sampai TC-04)...${NC}"
        echo ""
        run_test "AdminSignInTest.php" "TC-01: Admin Sign In"
        run_test "CreateLiveStreamTest.php" "TC-02: Membuat Live Stream Baru"
        run_test "StartBroadcastingTest.php" "TC-03: Memulai Broadcasting"
        run_test "StopBroadcastingTest.php" "TC-04: Menghentikan Broadcasting"
        echo -e "${GREEN}✅ Semua Test Admin PASSED${NC}"
        ;;
    14)
        echo -e "${BLUE}🚀 Menjalankan Test Viewer (TC-05 sampai TC-07)...${NC}"
        echo ""
        run_test "ViewLiveStreamListTest.php" "TC-05: Melihat Daftar Live Stream"
        run_test "WatchLiveStreamTest.php" "TC-06: Menonton Live Stream"
        run_test "ViewAIClassificationTest.php" "TC-07: Melihat Klasifikasi AI"
        echo -e "${GREEN}✅ Semua Test Viewer PASSED${NC}"
        ;;
    15)
        echo -e "${BLUE}🚀 Menjalankan Test Chat (TC-08 dan TC-09)...${NC}"
        echo ""
        run_test "SendChatMessageTest.php" "TC-08: Mengirim Chat Message"
        run_test "ReceiveChatMessageTest.php" "TC-09: Menerima Chat Message"
        echo -e "${GREEN}✅ Semua Test Chat PASSED${NC}"
        ;;
    0)
        echo -e "${YELLOW}👋 Keluar dari testing...${NC}"
        exit 0
        ;;
    *)
        echo -e "${RED}❌ Pilihan tidak valid!${NC}"
        exit 1
        ;;
esac

echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}║                  TESTING SELESAI! 🎉                      ║${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}📸 Screenshot tersimpan di: tests/Browser/screenshots/${NC}"
echo -e "${BLUE}📋 Console logs tersimpan di: tests/Browser/console/${NC}"
echo ""
