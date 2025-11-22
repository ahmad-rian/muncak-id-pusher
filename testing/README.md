# 🧪 Panduan Load Testing - Pusher WebSocket Live Streaming

## 📋 Daftar Isi
1. [Persiapan](#persiapan)
2. [Instalasi Dependencies](#instalasi-dependencies)
3. [Konfigurasi](#konfigurasi)
4. [Menjalankan Testing](#menjalankan-testing)
5. [Jenis-Jenis Test](#jenis-jenis-test)
6. [Membaca Hasil Test](#membaca-hasil-test)
7. [Troubleshooting](#troubleshooting)

---

## 🔧 Persiapan

### Prerequisites
- ✅ Node.js >= 18.0.0
- ✅ npm >= 9.0.0
- ✅ Stream aktif di `https://pusher.muncak.id`
- ✅ Pusher credentials sudah dikonfigurasi

### Struktur File Testing
```
testing/
├── artillery.yaml              # Konfigurasi utama Artillery
├── test-processor.js           # Custom processor untuk metrics
├── generate-pdf-report.js      # Generator PDF report
├── run-full-test.sh           # Script test 1 jam
├── run-5x-tests.sh            # Script test 5x iterasi
└── results/                   # Folder hasil test (auto-created)
```

---

## 📦 Instalasi Dependencies

### 1. Install dari Root Project
```bash
# Install semua dependencies termasuk testing tools
npm install
```

### 2. Verifikasi Instalasi
```bash
# Cek Artillery terinstall
npx artillery --version

# Cek Node.js version
node --version
```

**Output yang diharapkan:**
```
Artillery: 2.0.16
Node: v18.x.x atau lebih tinggi
```

---

## ⚙️ Konfigurasi

### 1. Update Stream Slug di `artillery.yaml`

Buka file `testing/artillery.yaml` dan update baris 10:

```yaml
defaults:
  activeStreamSlug: "YOUR-ACTIVE-STREAM-SLUG-HERE"
```

**Cara mendapatkan Stream Slug:**
1. Buka halaman live stream di browser
2. URL akan seperti: `https://pusher.muncak.id/live-cam/quam-modi-dolor-...`
3. Copy bagian setelah `/live-cam/`

### 2. Verifikasi Pusher Config di `artillery.yaml`

Pastikan baris 49-50 sesuai dengan config Pusher Anda:

```yaml
variables:
  pusherKey: "81f8af2b6681fa8ada90"      # Sesuaikan dengan PUSHER_APP_KEY
  pusherCluster: "ap1"                    # Sesuaikan dengan PUSHER_APP_CLUSTER
```

---

## 🚀 Menjalankan Testing

### Test 1: Quick Test (Rekomendasi untuk Mulai)

**Test cepat untuk verifikasi setup:**

```bash
# Dari root project
npm run test:pusher

# Atau dari folder testing
cd testing
artillery run artillery.yaml
```

**Durasi:** ~20 menit  
**Load:** 5-500 concurrent users (bertahap)  
**Output:** JSON + HTML report

---

### Test 2: Full Test 1 Jam

**Test lengkap sesuai metodologi penelitian:**

```bash
# Dari root project
npm run test:pusher:full

# Atau dari folder testing
cd testing
bash run-full-test.sh
```

**Durasi:** 1 jam  
**Load:** Sesuai phases di artillery.yaml  
**Output:** 
- JSON report
- HTML report
- PDF report
- System monitoring log

**Fitur:**
- ✅ Monitoring CPU, Memory, Network
- ✅ Auto-generate PDF report
- ✅ Stream status checking
- ✅ Detailed metrics collection

---

### Test 3: 5x Iterasi Test (Untuk Penelitian)

**Menjalankan test 5 kali dengan cooling period:**

```bash
# Dari root project
npm run test:pusher:5x

# Atau dari folder testing
cd testing
bash run-5x-tests.sh pusher
```

**Durasi:** ~5.5 jam (termasuk cooling period)  
**Load:** 5 iterasi @ 1 jam each  
**Cooling Period:** 5 menit antar iterasi  
**Output:**
- 5 set hasil test terpisah
- Combined analysis (mean, median, stddev)
- Summary report
- Multiple PDF reports

**Struktur Output:**
```
results/5x-pusher/
├── iteration-1/
│   ├── pusher-test-xxx.json
│   ├── report-1.html
│   └── metrics-1.json
├── iteration-2/
│   └── ...
├── combined-analysis.json
└── summary-report.txt
```

---

## 📊 Jenis-Jenis Test

### Test Scenarios (Sesuai artillery.yaml)

#### 1. **Viewer - Watch Live Stream** (70% traffic)
- Load halaman live cam
- Establish Pusher WebSocket
- Subscribe ke stream channel
- Initialize WebRTC
- Monitor streaming selama ~90 detik
- Measure: latency, frame rate, connection quality

#### 2. **Viewer - Watch & Chat** (20% traffic)
- Semua dari scenario 1
- Subscribe ke chat channel
- Send 5 chat messages
- Watch selama ~30 menit
- Measure: chat latency

#### 3. **High Churn - Join/Leave** (8% traffic)
- Quick connect/disconnect
- Stay hanya 5-15 detik
- Measure: churn latency

#### 4. **Stream Metadata Monitor** (2% traffic)
- Monitor viewer count
- Monitor stream quality
- Duration: 60 detik

### Load Phases

```yaml
Phase 1: Baseline       - 5 users   (2 min)
Phase 2: Low Load       - 25 users  (3 min)
Phase 3: Medium Load    - 50 users  (4 min)
Phase 4: High Load      - 100 users (5 min)
Phase 5: Peak Load      - 200 users (4 min)
Phase 6: Stress Test    - 300-500 users (3 min)
```

---

## 📈 Membaca Hasil Test

### 1. HTML Report

**Lokasi:** `testing/results/pusher-performance-[timestamp].html`

**Cara membuka:**
```bash
# macOS
open testing/results/pusher-performance-*.html

# Linux
xdg-open testing/results/pusher-performance-*.html
```

**Metrics yang ditampilkan:**
- Request rate (req/sec)
- Response time (min, max, median, p95, p99)
- Error rate
- Scenarios completed
- Virtual users

### 2. PDF Report

**Lokasi:** `testing/results/pusher-report-[timestamp].pdf`

**Generate manual (jika belum auto-generate):**
```bash
npm run test:report:pdf

# Atau
cd testing
node generate-pdf-report.js results/pusher-pdf-data-[timestamp].json
```

**Isi PDF Report:**
1. **Cover Page** - Info test
2. **Executive Summary** - Ringkasan hasil
3. **Latency Metrics** - P50, P95, P99, dll
4. **Quality Metrics** - FPS, bitrate, dll
5. **Recommendations** - Saran optimasi

### 3. JSON Report

**Lokasi:** `testing/results/pusher-performance-[timestamp].json`

**Membaca dengan jq:**
```bash
# Summary
cat results/pusher-*.json | jq '.aggregate.counters'

# Latency stats
cat results/pusher-*.json | jq '.aggregate.latency'

# Error rate
cat results/pusher-*.json | jq '.aggregate.counters["errors.total"]'
```

### 4. CSV Metrics

**Lokasi:** `testing/results/pusher-metrics-[timestamp].csv`

**Format:**
```csv
timestamp,type,metric,value
1234567890,latency,video,1250
1234567891,quality,fps,30
```

**Import ke Excel/Google Sheets untuk analisis lebih lanjut**

---

## 🎯 Metrics yang Diukur

### 1. Latency Metrics
- `websocket.connection.latency` - Waktu establish WebSocket (ms)
- `websocket.subscribe.latency` - Waktu subscribe channel (ms)
- `video.end_to_end.latency` - Latency video end-to-end (ms)
- `chat.message.latency` - Round-trip chat message (ms)

### 2. Throughput Metrics
- `websocket.messages.per_second` - Throughput pesan (msg/s)
- `video.bitrate.average` - Average bitrate (kbps)
- `video.frames.per_second` - Frame rate (fps)

### 3. Resource Usage
- `client.cpu.usage` - CPU usage (%)
- `client.memory.usage` - Memory usage (MB)
- `network.bandwidth.usage` - Bandwidth (Mbps)

### 4. Connection Metrics
- `concurrent.connections.active` - Concurrent connections
- `connections.established.total` - Total connections
- `connections.failed.total` - Failed connections

### 5. Quality Metrics
- `video.quality.resolution` - Video resolution
- `video.packets.lost` - Packet loss (%)
- `stream.stability.score` - Stability score (0-100)

### 6. Error Metrics
- `websocket.errors.total` - Total WebSocket errors
- `stream.errors.total` - Total streaming errors
- `disconnections.unexpected` - Unexpected disconnects

---

## 🎓 SLA & Expectations

### Target Performance (Sesuai Penelitian)

#### Latency Requirements
- ✅ WebSocket connection < 500ms (p95)
- ✅ Subscribe latency < 200ms (p95)
- ✅ Video latency < 3000ms (p95)
- ✅ Chat latency < 500ms (p95)

#### Throughput Requirements
- ✅ Handle 100+ messages/sec
- ✅ Maintain 30+ fps

#### Connection Requirements
- ✅ Support 200+ concurrent viewers
- ✅ Error rate < 1%

#### Quality Requirements
- ✅ Packet loss < 2%
- ✅ Stream stability > 95%

---

## 🔍 Troubleshooting

### Problem 1: Artillery not found
```bash
Error: artillery: command not found
```

**Solution:**
```bash
# Install globally
npm install -g artillery

# Atau gunakan npx
npx artillery run artillery.yaml
```

### Problem 2: Stream not accessible
```bash
✗ Stream is not accessible (HTTP 404)
```

**Solution:**
1. Pastikan stream sedang live
2. Update `activeStreamSlug` di artillery.yaml
3. Test manual: `curl https://pusher.muncak.id/live-cam/YOUR-SLUG`

### Problem 3: Pusher connection failed
```bash
Pusher connection error: ...
```

**Solution:**
1. Verifikasi `pusherKey` dan `pusherCluster` di artillery.yaml
2. Cek Pusher dashboard untuk quota/limits
3. Test Pusher connection manual

### Problem 4: PDF generation failed
```bash
Error generating PDF: Cannot find module 'pdfkit'
```

**Solution:**
```bash
# Install pdfkit
npm install pdfkit

# Atau reinstall semua
npm install
```

### Problem 5: Permission denied (shell scripts)
```bash
bash: permission denied: ./run-full-test.sh
```

**Solution:**
```bash
# Add execute permission
chmod +x testing/run-full-test.sh
chmod +x testing/run-5x-tests.sh

# Then run
bash testing/run-full-test.sh
```

### Problem 6: Out of memory
```bash
FATAL ERROR: ... JavaScript heap out of memory
```

**Solution:**
```bash
# Increase Node.js memory limit
export NODE_OPTIONS="--max-old-space-size=4096"

# Then run test
npm run test:pusher:full
```

---

## 📝 Best Practices

### 1. Sebelum Testing
- ✅ Pastikan stream aktif dan stabil
- ✅ Tutup aplikasi lain yang berat
- ✅ Gunakan koneksi internet stabil
- ✅ Backup hasil test sebelumnya

### 2. Selama Testing
- ❌ Jangan stop test di tengah jalan
- ❌ Jangan buka terlalu banyak tab browser
- ✅ Monitor system resources
- ✅ Catat anomali yang terjadi

### 3. Setelah Testing
- ✅ Backup semua hasil ke folder terpisah
- ✅ Generate PDF report
- ✅ Analisis metrics
- ✅ Dokumentasikan findings

---

## 📞 Support

Jika menemukan masalah:

1. **Check logs:** `testing/results/*.log`
2. **Check Artillery docs:** https://www.artillery.io/docs
3. **Check Pusher status:** https://status.pusher.com/

---

## 🎉 Quick Start Checklist

- [ ] Install dependencies: `npm install`
- [ ] Update stream slug di `artillery.yaml`
- [ ] Verifikasi Pusher config
- [ ] Pastikan stream aktif
- [ ] Run quick test: `npm run test:pusher`
- [ ] Check HTML report
- [ ] Generate PDF report
- [ ] Analisis hasil

---

**Happy Testing! 🚀**
