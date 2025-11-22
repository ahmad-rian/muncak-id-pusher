#!/bin/bash
# Quick Reference - Artillery Load Testing Commands
# Simpan file ini untuk referensi cepat

echo "🧪 ARTILLERY LOAD TESTING - QUICK REFERENCE"
echo "=============================================="
echo ""

echo "📦 INSTALASI"
echo "  npm install                                    # Install semua dependencies"
echo ""

echo "🚀 MENJALANKAN TEST (RECOMMENDED STRATEGY)"
echo "  npm run test:pusher                            # Quick test (~6 menit) - USE THIS!"
echo ""
echo "  STRATEGI UNTUK SKRIPSI (5 Iterasi):"
echo "  1. Buat 5 Pusher apps di dashboard.pusher.com"
echo "  2. Run 'npm run test:pusher' 5x dengan app berbeda"
echo "  3. Setiap iteration: update pusherKey di artillery.yaml"
echo "  4. Total waktu: 6 menit × 5 = 30 menit"
echo ""
echo "  TIDAK PERLU:"
echo "  npm run test:pusher:full                       # Sama hasilnya, tapi 1 jam (SKIP!)"
echo "  npm run test:pusher:5x                         # Akan over quota (SKIP!)"
echo ""

echo "📊 GENERATE REPORTS"
echo "  npm run test:report:pdf                        # Generate PDF report"
echo "  cd testing && artillery report results/*.json  # Generate HTML dari JSON"
echo ""

echo "🔍 CEK HASIL"
echo "  open testing/results/*.html                    # Buka HTML report"
echo "  open testing/results/*.pdf                     # Buka PDF report"
echo "  cat testing/results/*.json | jq .aggregate     # Lihat summary JSON"
echo ""

echo "⚙️ KONFIGURASI"
echo "  File: testing/artillery.yaml"
echo "  - Line 10: activeStreamSlug (update dengan stream aktif)"
echo "  - Line 49-50: pusherKey & pusherCluster"
echo ""

echo "📁 LOKASI FILE PENTING"
echo "  testing/artillery.yaml              # Konfigurasi test"
echo "  testing/test-processor.js           # Custom metrics processor"
echo "  testing/generate-pdf-report.js      # PDF generator"
echo "  testing/results/                    # Folder hasil test"
echo "  testing/README.md                   # Dokumentasi lengkap"
echo ""

echo "🎯 METRICS UTAMA"
echo "  - websocket.connection.latency      # Waktu koneksi WebSocket"
echo "  - video.end_to_end.latency          # Latency video"
echo "  - video.frames.per_second           # Frame rate"
echo "  - concurrent.connections.active     # Concurrent users"
echo "  - websocket.errors.total            # Total errors"
echo ""

echo "✅ QUICK START"
echo "  1. npm install"
echo "  2. Update stream slug di testing/artillery.yaml (line 10)"
echo "  3. npm run test:pusher"
echo "  4. open testing/results/*.html"
echo ""

echo "📖 Dokumentasi lengkap: testing/README.md"
echo ""
