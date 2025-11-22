#!/bin/bash
# Quick Reference - Artillery Load Testing Commands
# Simpan file ini untuk referensi cepat

echo "🧪 ARTILLERY LOAD TESTING - QUICK REFERENCE"
echo "=============================================="
echo ""

echo "📦 INSTALASI"
echo "  npm install                                    # Install semua dependencies"
echo ""

echo "🚀 MENJALANKAN TEST"
echo "  npm run test:pusher                            # Quick test (~20 menit)"
echo "  npm run test:pusher:full                       # Full test 1 jam"
echo "  npm run test:pusher:5x                         # Test 5x iterasi (~5.5 jam)"
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
