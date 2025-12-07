# Deployment Guide - Performance Optimizations

## Step-by-Step Deployment ke Production Server

### 1. Commit & Push Perubahan dari Local

```bash
# Di local machine
git add .
git commit -m "feat: optimize performance - switch to Redis cache/queue, add indexes, remove blocking operations

- Switch cache driver from database to Redis
- Configure Redis queue for async broadcasting
- Convert ShouldBroadcastNow to ShouldBroadcast (queued)
- Add pagination to chat history (limit 100 messages)
- Optimize database queries with caching
- Remove debug logging from broadcast channels
- Add database indexes for live_streams, chat_messages, trail_classifications
- Add response caching for expensive queries

Expected improvements:
- Error rate: 44.47% → <1%
- Latency: 6,187ms → <100ms (60x faster)
- Throughput: 23 req/s → 200+ req/s (8x increase)
- CPU usage: 314% → <100% (3x reduction)"

git push origin main
```

### 2. Install Redis di Server (jika belum ada)

```bash
# SSH ke server production
ssh user@pusher.muncak.id

# Install Redis
sudo apt update
sudo apt install redis-server -y

# Start dan enable Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Test Redis
redis-cli ping
# Output seharusnya: PONG

# Cek status
sudo systemctl status redis-server
```

### 3. Pull Perubahan di Server

```bash
# Di server production
cd /path/to/muncak-id-pusher

# Backup dulu (opsional tapi direkomendasikan)
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Pull latest code
git pull origin main

# Install dependencies (jika ada yang baru)
composer install --no-dev --optimize-autoloader
```

### 4. Update .env di Server

```bash
# Edit .env
nano .env

# Ubah baris berikut:
CACHE_STORE=redis
CACHE_PREFIX=muncak_
QUEUE_CONNECTION=redis

# Pastikan Redis config ada:
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
```

**Simpan file (Ctrl+O, Enter, Ctrl+X)**

### 5. Run Migrations

```bash
# Jalankan migration untuk menambahkan indexes
php artisan migrate

# Clear semua cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache config untuk production
php artisan config:cache
# Note: Skip route:cache karena ada duplicate route name issue
```

### 6. Setup Supervisor untuk Queue Worker

**PENTING**: Queue worker HARUS running agar broadcasting berfungsi!

#### a. Install Supervisor (jika belum ada)

```bash
sudo apt install supervisor -y
```

#### b. Buat Supervisor Config

```bash
# Buat file config
sudo nano /etc/supervisor/conf.d/muncak-queue.conf
```

**Paste konfigurasi ini** (sesuaikan path):

```ini
[program:muncak-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/muncak-id-pusher/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/muncak-id-pusher/storage/logs/queue-worker.log
stopwaitsecs=3600
startsecs=0
```

**⚠️ PENTING: Ganti `/var/www/muncak-id-pusher` dengan path sebenarnya!**

**Simpan file (Ctrl+O, Enter, Ctrl+X)**

#### c. Start Supervisor

```bash
# Reload supervisor config
sudo supervisorctl reread

# Update supervisor
sudo supervisorctl update

# Start queue workers
sudo supervisorctl start muncak-queue-worker:*

# Cek status
sudo supervisorctl status

# Output seharusnya:
# muncak-queue-worker:muncak-queue-worker_00   RUNNING   pid 12345, uptime 0:00:05
# muncak-queue-worker:muncak-queue-worker_01   RUNNING   pid 12346, uptime 0:00:05
```

### 7. Test Redis Connection

```bash
# Test cache
php artisan tinker
>>> Cache::put('test', 'hello');
>>> Cache::get('test');
# Output: "hello"
>>> exit

# Test queue
php artisan tinker
>>> dispatch(function() { \Log::info('Queue test works!'); });
>>> exit

# Cek log (tunggu beberapa detik)
tail -f storage/logs/laravel.log
# Seharusnya muncul: Queue test works!
```

### 8. Set Permissions (jika perlu)

```bash
# Pastikan www-data bisa write ke storage dan cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Untuk queue log
sudo touch storage/logs/queue-worker.log
sudo chown www-data:www-data storage/logs/queue-worker.log
```

### 9. Restart Services

```bash
# Restart PHP-FPM (sesuaikan dengan versi PHP Anda)
sudo systemctl restart php8.2-fpm

# Atau jika pakai PHP 8.3
sudo systemctl restart php8.3-fpm

# Restart Nginx/Apache
sudo systemctl restart nginx
# atau
sudo systemctl restart apache2

# Restart supervisor (untuk memastikan)
sudo systemctl restart supervisor
sudo supervisorctl restart muncak-queue-worker:*
```

### 10. Monitoring & Verification

```bash
# Monitor queue worker
sudo supervisorctl tail -f muncak-queue-worker:muncak-queue-worker_00 stdout

# Monitor Redis
redis-cli MONITOR

# Cek Redis memory usage
redis-cli INFO memory

# Cek jumlah cache keys
redis-cli DBSIZE

# Monitor Laravel logs
tail -f storage/logs/laravel.log

# Cek failed jobs (jika ada)
php artisan queue:failed
```

## Alternatif: Tanpa Supervisor (Development/Testing)

Jika Anda tidak ingin setup supervisor dulu (untuk testing):

```bash
# Option 1: Background worker (akan stop jika SSH disconnect)
nohup php artisan queue:work redis --sleep=3 --tries=3 > storage/logs/queue-worker.log 2>&1 &

# Option 2: Screen session (lebih baik dari nohup)
screen -S queue-worker
php artisan queue:work redis --sleep=3 --tries=3
# Press Ctrl+A then D to detach

# Untuk attach kembali:
screen -r queue-worker

# Option 3: Tmux (modern alternative)
tmux new -s queue-worker
php artisan queue:work redis --sleep=3 --tries=3
# Press Ctrl+B then D to detach

# Untuk attach kembali:
tmux attach -t queue-worker
```

**⚠️ PERINGATAN**: Method di atas TIDAK production-ready! Gunakan Supervisor untuk production.

## Troubleshooting

### Issue 1: Queue worker tidak jalan

```bash
# Cek status supervisor
sudo supervisorctl status

# Cek log error
sudo supervisorctl tail muncak-queue-worker:muncak-queue-worker_00 stderr

# Restart manual
sudo supervisorctl restart muncak-queue-worker:*
```

### Issue 2: Redis connection refused

```bash
# Cek Redis running
sudo systemctl status redis-server

# Cek Redis listening
sudo netstat -tulpn | grep 6379

# Test connection
redis-cli ping

# Restart Redis
sudo systemctl restart redis-server
```

### Issue 3: Permission denied untuk queue log

```bash
# Fix permissions
sudo chown www-data:www-data storage/logs/queue-worker.log
sudo chmod 664 storage/logs/queue-worker.log
```

### Issue 4: Broadcast masih lambat

```bash
# Pastikan queue worker running
ps aux | grep "queue:work"

# Cek queue jobs
php artisan queue:listen redis --queue=default

# Cek failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Issue 5: Cache tidak terhapus

```bash
# Flush Redis cache
redis-cli FLUSHDB

# Atau dari Laravel
php artisan cache:clear
```

## Supervisor Commands Cheatsheet

```bash
# Cek status semua workers
sudo supervisorctl status

# Start all workers
sudo supervisorctl start muncak-queue-worker:*

# Stop all workers
sudo supervisorctl stop muncak-queue-worker:*

# Restart all workers
sudo supervisorctl restart muncak-queue-worker:*

# Reload config setelah edit
sudo supervisorctl reread
sudo supervisorctl update

# View logs
sudo supervisorctl tail -f muncak-queue-worker:muncak-queue-worker_00 stdout
sudo supervisorctl tail -f muncak-queue-worker:muncak-queue-worker_00 stderr

# Clear log file
sudo supervisorctl clear muncak-queue-worker:muncak-queue-worker_00
```

## Post-Deployment Testing

### 1. Test Chat Functionality

```bash
# Buka browser, access live stream
# Kirim beberapa chat messages
# Pastikan muncul tanpa delay

# Monitor queue processing
tail -f storage/logs/queue-worker.log
```

### 2. Monitor Performance

```bash
# CPU usage (seharusnya turun drastis)
top

# Redis memory
redis-cli INFO memory

# Queue stats
php artisan queue:work redis --once --verbose
```

### 3. Check Database Indexes

```sql
# Login ke MySQL
mysql -u root -p muncak_id_db

# Check indexes
SHOW INDEX FROM live_streams;
SHOW INDEX FROM chat_messages;
SHOW INDEX FROM trail_classifications;
SHOW INDEX FROM stream_analytics;
```

## Rollback Plan (jika ada masalah)

```bash
# Stop queue workers
sudo supervisorctl stop muncak-queue-worker:*

# Restore .env backup
cp .env.backup.YYYYMMDD_HHMMSS .env

# Rollback code
git reset --hard HEAD~1

# Rollback migrations
php artisan migrate:rollback

# Clear cache
php artisan config:clear
php artisan cache:clear

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## Expected Performance Improvements

Setelah deployment sukses, Anda seharusnya melihat:

✅ **Error rate**: 44.47% → <1%
✅ **Median latency**: 6,187ms → <100ms (60x faster)
✅ **Throughput**: 23 req/s → 200+ req/s (8x increase)
✅ **CPU usage**: 314% → <100% (3x reduction)
✅ **Memory usage**: 11,244 MB → <2,000 MB

## Catatan Penting

1. **Queue worker adalah WAJIB** - Tanpa ini, broadcast tidak akan jalan!
2. **Supervisor adalah best practice** - Gunakan untuk production
3. **Monitor Redis memory** - Set maxmemory jika perlu
4. **Check logs regularly** - Terutama queue-worker.log
5. **Test sebelum peak hours** - Pastikan semua berfungsi sebelum traffic tinggi

## Need Help?

Jika ada masalah setelah deployment:

1. Check supervisor status: `sudo supervisorctl status`
2. Check Redis: `redis-cli ping`
3. Check logs: `tail -f storage/logs/laravel.log`
4. Check queue: `php artisan queue:failed`
5. Monitor real-time: `sudo supervisorctl tail -f muncak-queue-worker:muncak-queue-worker_00 stdout`
