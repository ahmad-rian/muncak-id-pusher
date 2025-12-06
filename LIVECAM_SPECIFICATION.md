# 📹 Spesifikasi Modul LiveCam - Muncak.id

## 📋 Daftar Isi
- [Overview](#overview)
- [Arsitektur Sistem](#arsitektur-sistem)
- [Teknologi & Package](#teknologi--package)
- [Database Schema](#database-schema)
- [Fitur User (Public)](#fitur-user-public)
- [Fitur Admin](#fitur-admin)
- [Events & Broadcasting](#events--broadcasting)
- [API Endpoints](#api-endpoints)
- [JavaScript Modules](#javascript-modules)
- [Performance Testing](#performance-testing)

---

## 🎯 Overview

Modul **LiveCam** adalah fitur live streaming real-time yang memungkinkan broadcaster untuk melakukan siaran langsung dari jalur pendakian, dengan fitur tambahan **klasifikasi trail otomatis menggunakan AI** untuk memberikan informasi kondisi jalur secara real-time kepada viewer.

### Tujuan Utama
1. **Live Streaming**: Siaran langsung dari jalur pendakian dengan kualitas adaptif
2. **Trail Classification**: Klasifikasi otomatis kondisi jalur (cuaca, keramaian, visibilitas) menggunakan AI
3. **Real-time Interaction**: Chat real-time antara broadcaster dan viewers
4. **Analytics**: Tracking viewer count, engagement, dan stream analytics

---

## 🏗️ Arsitektur Sistem

### Technology Stack

```
┌─────────────────────────────────────────────────────────────┐
│                    Frontend Layer                            │
├─────────────────────────────────────────────────────────────┤
│  • Blade Templates (Laravel)                                 │
│  • Vanilla JavaScript (ES6+)                                 │
│  • TailwindCSS + DaisyUI                                     │
│  • LiveKit Client SDK                                        │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    Backend Layer                             │
├─────────────────────────────────────────────────────────────┤
│  • Laravel 12 (PHP 8.2)                                      │
│  • Laravel Reverb (WebSocket Server)                         │
│  • LiveKit Server SDK                                        │
│  • Redis (Caching & Queue)                                   │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                 Media & Streaming Layer                      │
├─────────────────────────────────────────────────────────────┤
│  • LiveKit Cloud (Media Server)                              │
│  • WebRTC (Peer-to-Peer Connection)                          │
│  • Adaptive Bitrate Streaming                                │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    AI Classification                         │
├─────────────────────────────────────────────────────────────┤
│  • TensorFlow.js / Custom AI Model                           │
│  • Real-time Image Processing                                │
│  • Classification: Weather, Crowd, Visibility                │
└─────────────────────────────────────────────────────────────┘
```

---

## 📦 Teknologi & Package

### Backend Dependencies (composer.json)

```json
{
  "php": "^8.2",
  "laravel/framework": "^12.0",
  "agence104/livekit-server-sdk": "^1.3",
  "laravel/reverb": "^1.0",
  "pusher/pusher-php-server": "^7.2",
  "intervention/image-laravel": "^1.3",
  "spatie/laravel-medialibrary": "^11.10",
  "spatie/laravel-permission": "^6.10"
}
```

**Penjelasan Package:**
- **agence104/livekit-server-sdk**: SDK untuk integrasi dengan LiveKit media server
- **laravel/reverb**: WebSocket server untuk real-time communication
- **pusher/pusher-php-server**: Fallback untuk broadcasting (legacy support)
- **intervention/image-laravel**: Image processing untuk thumbnail
- **spatie/laravel-medialibrary**: Media management untuk upload gambar
- **spatie/laravel-permission**: Role & permission management

### Frontend Dependencies (package.json)

```json
{
  "dependencies": {
    "livekit-client": "^2.16.0",
    "laravel-echo": "^2.2.6",
    "pusher-js": "^8.4.0",
    "hls.js": "^1.6.15",
    "simple-peer": "^9.11.1"
  },
  "devDependencies": {
    "tailwindcss": "^3.4.15",
    "daisyui": "^4.12.14",
    "vite": "^5.0",
    "artillery": "^2.0.16"
  }
}
```

**Penjelasan Package:**
- **livekit-client**: Client SDK untuk LiveKit streaming
- **laravel-echo**: Real-time event broadcasting client
- **pusher-js**: WebSocket client untuk Pusher/Reverb
- **hls.js**: HLS video player (fallback)
- **simple-peer**: WebRTC peer connection wrapper
- **artillery**: Load testing tool untuk performance testing

---

## 🗄️ Database Schema

### 1. Table: `live_streams`

**Deskripsi**: Menyimpan informasi stream live

```sql
CREATE TABLE live_streams (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    hiking_trail_id BIGINT UNSIGNED NULL,
    location VARCHAR(255) NULL,
    broadcaster_id BIGINT UNSIGNED NULL,
    status ENUM('live', 'offline', 'scheduled') DEFAULT 'offline',
    current_quality ENUM('360p', '720p', '1080p') DEFAULT '720p',
    viewer_count INT DEFAULT 0,
    total_views INT DEFAULT 0,
    stream_key VARCHAR(255) UNIQUE NOT NULL,
    pusher_channel_id VARCHAR(255) NULL,
    thumbnail_url VARCHAR(255) NULL,
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (hiking_trail_id) REFERENCES rute(id) ON DELETE SET NULL,
    FOREIGN KEY (broadcaster_id) REFERENCES users(id) ON DELETE SET NULL
);
```

**Field Explanation:**
- `slug`: URL-friendly identifier (auto-generated dari title + random string)
- `stream_key`: Unique key untuk autentikasi broadcaster
- `pusher_channel_id`: Channel ID untuk Reverb/Pusher broadcasting
- `status`: Status stream (live/offline/scheduled)
- `current_quality`: Kualitas stream saat ini (adaptif berdasarkan viewer count)
- `viewer_count`: Jumlah viewer real-time
- `total_views`: Total views sepanjang stream

### 2. Table: `chat_messages`

**Deskripsi**: Menyimpan chat messages dalam stream

```sql
CREATE TABLE chat_messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    live_stream_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    username VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (live_stream_id) REFERENCES live_streams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

### 3. Table: `stream_analytics`

**Deskripsi**: Menyimpan analytics data stream

```sql
CREATE TABLE stream_analytics (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    live_stream_id BIGINT UNSIGNED NOT NULL,
    metric_type VARCHAR(50) NOT NULL,
    value DECIMAL(10, 2) NOT NULL,
    recorded_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (live_stream_id) REFERENCES live_streams(id) ON DELETE CASCADE,
    INDEX idx_stream_metric (live_stream_id, metric_type, recorded_at)
);
```

**Metric Types:**
- `viewer_count`: Jumlah viewer pada waktu tertentu
- `quality_change`: Perubahan kualitas stream
- `bandwidth`: Bandwidth usage
- `latency`: Stream latency

### 4. Table: `trail_classifications`

**Deskripsi**: Menyimpan hasil klasifikasi AI kondisi jalur

```sql
CREATE TABLE trail_classifications (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    live_stream_id BIGINT UNSIGNED NOT NULL,
    hiking_trail_id BIGINT UNSIGNED NOT NULL,
    
    -- Classification Results
    weather VARCHAR(50) NULL,              -- cerah, berawan, hujan
    crowd VARCHAR(50) NULL,                -- sepi, sedang, ramai
    visibility VARCHAR(50) NULL,           -- jelas, kabut_sedang, kabut_tebal
    
    -- Confidence Scores (0.0 - 1.0)
    weather_confidence DECIMAL(3, 2) NULL,
    crowd_confidence DECIMAL(3, 2) NULL,
    visibility_confidence DECIMAL(3, 2) NULL,
    
    -- Image & Metadata
    image_path VARCHAR(255) NULL,
    stream_delay_ms INT DEFAULT 0,
    classified_at TIMESTAMP NULL,
    
    -- Status Tracking
    status ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
    error_message TEXT NULL,
    retry_count INT DEFAULT 0,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (live_stream_id) REFERENCES live_streams(id) ON DELETE CASCADE,
    FOREIGN KEY (hiking_trail_id) REFERENCES rute(id) ON DELETE CASCADE,
    INDEX idx_stream_classified (live_stream_id, classified_at),
    INDEX idx_trail (hiking_trail_id)
);
```

**Classification Categories:**

1. **Weather (Cuaca)**:
   - `cerah`: Cuaca cerah
   - `berawan`: Berawan
   - `hujan`: Hujan

2. **Crowd (Keramaian)**:
   - `sepi`: Jalur sepi
   - `sedang`: Keramaian sedang
   - `ramai`: Jalur ramai

3. **Visibility (Visibilitas)**:
   - `jelas`: Visibilitas jelas
   - `kabut_sedang`: Kabut sedang
   - `kabut_tebal`: Kabut tebal

---

## 👥 Fitur User (Public)

### 1. Halaman Index Live Streams (`/live-cam`)

**File**: `resources/views/live-cam/index.blade.php`

**Fitur:**
- ✅ Menampilkan daftar semua live streams
- ✅ Filter berdasarkan status (Live, Scheduled, Offline)
- ✅ Thumbnail preview untuk setiap stream
- ✅ Informasi viewer count real-time
- ✅ Informasi jalur pendakian terkait
- ✅ Quick access ke stream room

**Sections:**
1. **Live Streams Section**: Menampilkan stream yang sedang live
2. **Scheduled Streams Section**: Menampilkan stream yang dijadwalkan
3. **Recent Streams Section**: Menampilkan stream yang baru selesai

**Data yang ditampilkan:**
```php
- Stream Title
- Broadcaster Name
- Hiking Trail Name & Location
- Viewer Count (real-time)
- Stream Status (Live/Offline/Scheduled)
- Thumbnail Image
- Started At / Scheduled At
```

### 2. Halaman Stream Room (`/live-cam/{slug}`)

**File**: `resources/views/live-cam/show.blade.php`

**Fitur:**

#### A. Video Player Section
- ✅ Live video player menggunakan LiveKit
- ✅ Adaptive quality (360p, 720p, 1080p)
- ✅ Full-screen mode
- ✅ Volume control
- ✅ Connection status indicator
- ✅ Latency indicator

#### B. Trail Classification Section (Real-time AI)
**Lokasi**: Sidebar kanan / Bottom panel

**Informasi yang ditampilkan:**
```javascript
{
  "weather": {
    "status": "cerah",
    "confidence": 0.95,
    "icon": "☀️",
    "label": "Cuaca Cerah"
  },
  "crowd": {
    "status": "sedang",
    "confidence": 0.87,
    "icon": "👥",
    "label": "Keramaian Sedang"
  },
  "visibility": {
    "status": "jelas",
    "confidence": 0.92,
    "icon": "👁️",
    "label": "Visibilitas Jelas"
  },
  "last_updated": "2 menit yang lalu"
}
```

**Update Mechanism:**
- Klasifikasi dilakukan setiap 30 detik
- Update otomatis via WebSocket
- Menampilkan confidence score untuk transparansi
- History klasifikasi tersimpan di database

#### C. Stream Information Section
```
- Stream Title
- Description
- Hiking Trail Details:
  * Trail Name
  * Mountain Name
  * Location (Provinsi, Kabupaten)
  * Difficulty Level
  * Elevation
- Broadcaster Information
- Current Viewer Count
- Stream Quality
- Stream Duration
```

#### D. Chat Section
**Fitur:**
- ✅ Real-time chat menggunakan Laravel Reverb
- ✅ User authentication (login required untuk chat)
- ✅ Guest dapat melihat chat (read-only)
- ✅ Auto-scroll ke pesan terbaru
- ✅ Timestamp untuk setiap pesan
- ✅ User avatar & username
- ✅ Chat history (load previous messages)
- ✅ Rate limiting (max 5 pesan per menit)

**Chat Message Format:**
```javascript
{
  "id": 123,
  "username": "John Doe",
  "message": "Pemandangannya bagus!",
  "created_at": "2025-12-03 14:30:00",
  "user_avatar": "https://..."
}
```

### 3. Responsive Design

**Breakpoints:**
- **Desktop (≥1024px)**: 
  - Video player: 70% width
  - Sidebar (Classification + Chat): 30% width
  
- **Tablet (768px - 1023px)**:
  - Video player: 100% width
  - Classification: Collapsible panel
  - Chat: Bottom drawer

- **Mobile (<768px)**:
  - Video player: Full width
  - Classification: Tabs
  - Chat: Modal overlay

---

## 🔐 Fitur Admin

### 1. Admin Dashboard (`/admin/live-stream`)

**File**: `resources/views/admin/live-stream/index.blade.php`

**Fitur:**
- ✅ Daftar semua streams (Live, Scheduled, Offline)
- ✅ Quick stats: Total Streams, Live Streams, Total Views
- ✅ Filter & Search streams
- ✅ Bulk actions (Delete, Change Status)
- ✅ Export stream analytics

**Table Columns:**
```
- ID
- Thumbnail
- Title
- Broadcaster
- Hiking Trail
- Status
- Viewer Count
- Total Views
- Started At
- Actions (Edit, Delete, View Analytics)
```

### 2. Create Stream (`/admin/live-stream/create`)

**File**: `resources/views/admin/live-stream/create.blade.php`

**Form Fields:**
```php
- Title (required, max: 255)
- Description (optional, textarea)
- Hiking Trail (select, from rute table)
- Location (optional, text)
- Status (select: offline, scheduled)
- Scheduled At (datetime, if status = scheduled)
- Thumbnail (image upload, optional)
```

**Validasi:**
```php
'title' => 'required|string|max:255',
'description' => 'nullable|string',
'hiking_trail_id' => 'nullable|exists:rute,id',
'location' => 'nullable|string|max:255',
'status' => 'required|in:offline,scheduled',
'scheduled_at' => 'required_if:status,scheduled|date|after:now',
'thumbnail' => 'nullable|image|max:2048'
```

**Auto-generated Fields:**
- `slug`: Auto-generated dari title + random string (6 chars)
- `stream_key`: Random 32 characters
- `pusher_channel_id`: 'live-stream.' + random 16 characters
- `broadcaster_id`: Current authenticated admin user

### 3. Broadcast Dashboard (`/admin/live-stream/{slug}/broadcast`)

**File**: `resources/views/admin/live-stream/broadcast.blade.php`

**Sections:**

#### A. Broadcaster Controls
```
┌─────────────────────────────────────────┐
│  📹 Camera Preview                      │
│  ┌───────────────────────────────────┐  │
│  │                                   │  │
│  │      Live Camera Feed             │  │
│  │                                   │  │
│  └───────────────────────────────────┘  │
│                                         │
│  🎥 Controls:                           │
│  [Start Stream] [Stop Stream]           │
│  [Mirror Camera] [Change Quality]       │
│                                         │
│  📊 Stats:                              │
│  - Viewer Count: 45                     │
│  - Stream Duration: 15:30               │
│  - Current Quality: 720p                │
│  - Connection: Stable                   │
└─────────────────────────────────────────┘
```

**Features:**
- ✅ Camera selection (front/back)
- ✅ Microphone selection
- ✅ Mirror camera toggle
- ✅ Quality selection (360p, 720p, 1080p)
- ✅ Start/Stop stream
- ✅ Real-time viewer count
- ✅ Stream duration timer
- ✅ Connection quality indicator
- ✅ Thumbnail auto-capture (saat start stream)

#### B. Trail Classification Monitor
```
┌─────────────────────────────────────────┐
│  🤖 AI Classification (Real-time)       │
│                                         │
│  ☀️ Weather: Cerah (95%)                │
│  👥 Crowd: Sedang (87%)                 │
│  👁️ Visibility: Jelas (92%)             │
│                                         │
│  Last Update: 30 seconds ago            │
│  [Force Classify Now]                   │
└─────────────────────────────────────────┘
```

#### C. Chat Monitor
- ✅ View all chat messages
- ✅ Moderate chat (delete inappropriate messages)
- ✅ Ban users (optional)
- ✅ Send broadcaster messages (highlighted)

### 4. Stream Analytics (`/admin/live-stream/{slug}/analytics`)

**Metrics Displayed:**
```
1. Viewer Metrics:
   - Peak Viewers
   - Average Viewers
   - Total Unique Viewers
   - Viewer Retention Rate

2. Engagement Metrics:
   - Total Chat Messages
   - Messages per Minute
   - Active Chatters

3. Technical Metrics:
   - Average Latency
   - Quality Changes Count
   - Connection Drops
   - Average Bandwidth

4. Classification Metrics:
   - Total Classifications
   - Average Confidence Score
   - Weather Distribution
   - Crowd Distribution
   - Visibility Distribution

5. Stream Duration:
   - Total Duration
   - Live Time
   - Downtime
```

**Export Options:**
- PDF Report
- CSV Export
- JSON Export

### 5. CRUD Operations

#### Create
- **Route**: `POST /admin/live-stream`
- **Controller**: `LiveCamController@store`
- **Permissions**: `admin`, `broadcaster`

#### Read
- **Route**: `GET /admin/live-stream/{slug}`
- **Controller**: `LiveCamController@show`
- **Permissions**: `public`

#### Update
- **Route**: `PUT /admin/live-stream/{slug}`
- **Controller**: `LiveCamController@update`
- **Permissions**: `admin`, `owner`

#### Delete
- **Route**: `DELETE /admin/live-stream/{slug}`
- **Controller**: `LiveCamController@destroy`
- **Permissions**: `admin`

**Cascade Delete:**
- Chat messages
- Stream analytics
- Trail classifications
- Thumbnail file

---

## 📡 Events & Broadcasting

### Laravel Events

#### 1. StreamStarted

**File**: `app/Events/StreamStarted.php`

```php
class StreamStarted implements ShouldBroadcast
{
    public $liveStream;
    
    public function broadcastOn(): array
    {
        return [new Channel('stream.' . $this->liveStream->id)];
    }
    
    public function broadcastAs(): string
    {
        return 'stream-started';
    }
    
    public function broadcastWith(): array
    {
        return [
            'stream_id' => $this->liveStream->id,
            'title' => $this->liveStream->title,
            'status' => $this->liveStream->status,
            'started_at' => $this->liveStream->started_at,
        ];
    }
}
```

**Triggered When**: Broadcaster starts stream
**Listeners**: All viewers on index page, stream room

#### 2. StreamEnded

```php
class StreamEnded implements ShouldBroadcast
{
    public function broadcastWith(): array
    {
        return [
            'stream_id' => $this->liveStream->id,
            'ended_at' => $this->liveStream->ended_at,
            'total_views' => $this->liveStream->total_views,
            'duration' => $this->calculateDuration(),
        ];
    }
}
```

**Triggered When**: Broadcaster stops stream
**Listeners**: All viewers in stream room

#### 3. ViewerCountUpdated

```php
class ViewerCountUpdated implements ShouldBroadcast
{
    public function broadcastWith(): array
    {
        return [
            'stream_id' => $this->liveStream->id,
            'viewer_count' => $this->viewerCount,
        ];
    }
}
```

**Triggered When**: Viewer joins/leaves stream
**Listeners**: Broadcaster dashboard, all viewers

#### 4. ChatMessageSent

```php
class ChatMessageSent implements ShouldBroadcast
{
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'username' => $this->message->username,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at,
        ];
    }
}
```

**Triggered When**: User sends chat message
**Listeners**: All viewers in stream room

#### 5. QualityChanged

```php
class QualityChanged implements ShouldBroadcast
{
    public function broadcastWith(): array
    {
        return [
            'stream_id' => $this->liveStream->id,
            'quality' => $this->quality,
            'reason' => $this->reason, // 'manual' or 'auto'
        ];
    }
}
```

**Triggered When**: Stream quality changes
**Listeners**: All viewers in stream room

#### 6. TrailClassified (Custom Event)

```php
class TrailClassified implements ShouldBroadcast
{
    public function broadcastWith(): array
    {
        return [
            'stream_id' => $this->classification->live_stream_id,
            'weather' => $this->classification->weather,
            'weather_confidence' => $this->classification->weather_confidence,
            'crowd' => $this->classification->crowd,
            'crowd_confidence' => $this->classification->crowd_confidence,
            'visibility' => $this->classification->visibility,
            'visibility_confidence' => $this->classification->visibility_confidence,
            'classified_at' => $this->classification->classified_at,
        ];
    }
}
```

**Triggered When**: AI classification completes
**Listeners**: All viewers in stream room, broadcaster dashboard

### Broadcasting Channels

```php
// Public Channel (anyone can listen)
'stream.{streamId}' => StreamStarted, StreamEnded, ViewerCountUpdated

// Private Channel (authenticated users only)
'private-stream.{streamId}.chat' => ChatMessageSent

// Presence Channel (track online users)
'presence-stream.{streamId}' => ViewerJoined, ViewerLeft
```

---

## 🔌 API Endpoints

### Public Endpoints

#### 1. Get All Streams
```http
GET /live-cam
```

**Response:**
```json
{
  "live_streams": [...],
  "scheduled_streams": [...],
  "recent_streams": [...]
}
```

#### 2. Get Stream Details
```http
GET /live-cam/{slug}
```

**Response:**
```json
{
  "id": 1,
  "title": "Pendakian Gunung Semeru",
  "slug": "pendakian-gunung-semeru-abc123",
  "description": "...",
  "hiking_trail": {
    "id": 5,
    "name": "Jalur Ranu Pani",
    "mountain": "Gunung Semeru",
    "difficulty": "Sulit"
  },
  "broadcaster": {
    "id": 10,
    "name": "John Doe"
  },
  "status": "live",
  "viewer_count": 45,
  "current_quality": "720p",
  "started_at": "2025-12-03 14:00:00",
  "latest_classification": {
    "weather": "cerah",
    "weather_confidence": 0.95,
    "crowd": "sedang",
    "crowd_confidence": 0.87,
    "visibility": "jelas",
    "visibility_confidence": 0.92,
    "classified_at": "2025-12-03 14:28:00"
  }
}
```

#### 3. Get Chat History
```http
GET /live-cam/{slug}/chat-history
```

**Response:**
```json
{
  "messages": [
    {
      "id": 123,
      "username": "John Doe",
      "message": "Pemandangannya bagus!",
      "created_at": "2025-12-03 14:30:00"
    }
  ]
}
```

#### 4. Get LiveKit Viewer Token
```http
GET /live-cam/{slug}/livekit/token
```

**Response:**
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "url": "wss://livekit.muncak.id"
}
```

### Authenticated Endpoints

#### 5. Send Chat Message
```http
POST /live-cam/{slug}/chat
Content-Type: application/json

{
  "message": "Hello from viewer!"
}
```

**Response:**
```json
{
  "success": true,
  "message": {
    "id": 124,
    "username": "Current User",
    "message": "Hello from viewer!",
    "created_at": "2025-12-03 14:31:00"
  }
}
```

#### 6. Update Viewer Count
```http
POST /live-cam/{slug}/viewer-count
Content-Type: application/json

{
  "action": "join" // or "leave"
}
```

**Response:**
```json
{
  "success": true,
  "viewer_count": 46
}
```

### Broadcaster Endpoints

#### 7. Start Stream
```http
POST /live-cam/{slug}/start
Content-Type: application/json

{
  "quality": "720p"
}
```

**Response:**
```json
{
  "success": true,
  "stream": {
    "id": 1,
    "status": "live",
    "started_at": "2025-12-03 14:00:00",
    "livekit_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

#### 8. Stop Stream
```http
POST /live-cam/{slug}/stop
```

**Response:**
```json
{
  "success": true,
  "stream": {
    "id": 1,
    "status": "offline",
    "ended_at": "2025-12-03 15:30:00",
    "duration": "1:30:00",
    "total_views": 150
  }
}
```

#### 9. Change Quality
```http
POST /live-cam/{slug}/quality
Content-Type: application/json

{
  "quality": "1080p"
}
```

#### 10. Upload Thumbnail
```http
POST /live-cam/{slug}/thumbnail
Content-Type: multipart/form-data

thumbnail: <image file>
```

#### 11. Update Mirror State
```http
POST /live-cam/{slug}/mirror-state
Content-Type: application/json

{
  "mirrored": true
}
```

---

## 💻 JavaScript Modules

### 1. Broadcaster Module

**File**: `resources/js/livecam/broadcaster-livekit.js`

**Main Functions:**

```javascript
class LiveKitBroadcaster {
  constructor(streamSlug, livekitUrl, token) {
    this.streamSlug = streamSlug;
    this.room = new Room();
    this.localVideoTrack = null;
    this.localAudioTrack = null;
  }

  async initialize() {
    // Initialize LiveKit room
    // Get user media (camera + microphone)
    // Connect to LiveKit server
  }

  async startBroadcast(quality = '720p') {
    // Publish video and audio tracks
    // Start thumbnail capture
    // Notify server (POST /start)
    // Start trail classification
  }

  async stopBroadcast() {
    // Unpublish tracks
    // Disconnect from room
    // Notify server (POST /stop)
    // Stop classification
  }

  async changeQuality(quality) {
    // Update video track constraints
    // Notify viewers
  }

  toggleMirror() {
    // Mirror video element
    // Update server state
  }

  captureThumbnail() {
    // Capture frame from video
    // Convert to base64
    // Upload to server
  }
}
```

**Events Listened:**
```javascript
- 'participantConnected': New viewer joined
- 'participantDisconnected': Viewer left
- 'connectionStateChanged': Connection status changed
- 'trackPublished': Track published successfully
- 'trackUnpublished': Track unpublished
```

### 2. Viewer Module

**File**: `resources/js/livecam/viewer-livekit.js`

**Main Functions:**

```javascript
class LiveKitViewer {
  constructor(streamSlug, livekitUrl, token) {
    this.streamSlug = streamSlug;
    this.room = new Room();
  }

  async initialize() {
    // Connect to LiveKit room
    // Subscribe to broadcaster tracks
    // Setup event listeners
  }

  handleTrackSubscribed(track, publication, participant) {
    // Attach video/audio track to DOM
    // Update UI
  }

  handleTrackUnsubscribed(track) {
    // Detach track
    // Show offline message
  }

  updateViewerCount(action = 'join') {
    // POST /viewer-count
    // Update UI
  }

  sendChatMessage(message) {
    // POST /chat
    // Append to chat UI
  }

  listenForClassificationUpdates() {
    // Listen to 'TrailClassified' event
    // Update classification UI
  }
}
```

### 3. Trail Classifier Module

**File**: `resources/js/livecam/trail-classifier.js`

**Main Functions:**

```javascript
class TrailClassifier {
  constructor(streamId, hikingTrailId) {
    this.streamId = streamId;
    this.hikingTrailId = hikingTrailId;
    this.model = null;
    this.classificationInterval = null;
  }

  async loadModel() {
    // Load TensorFlow.js model
    // Or use external API
  }

  async startClassification(videoElement) {
    // Capture frame every 30 seconds
    // Run classification
    // Send results to server
    this.classificationInterval = setInterval(() => {
      this.classifyFrame(videoElement);
    }, 30000);
  }

  async classifyFrame(videoElement) {
    // Capture current frame
    const imageData = this.captureFrame(videoElement);
    
    // Run classification
    const results = await this.classify(imageData);
    
    // Send to server
    await this.saveClassification(results);
  }

  async classify(imageData) {
    // Run AI model
    // Return classification results
    return {
      weather: 'cerah',
      weather_confidence: 0.95,
      crowd: 'sedang',
      crowd_confidence: 0.87,
      visibility: 'jelas',
      visibility_confidence: 0.92
    };
  }

  async saveClassification(results) {
    // POST /api/trail-classification
    // Broadcast to viewers
  }

  stopClassification() {
    clearInterval(this.classificationInterval);
  }
}
```

**Classification Flow:**
```
1. Capture frame from video stream (every 30s)
2. Preprocess image (resize, normalize)
3. Run through AI model
4. Get predictions with confidence scores
5. Save to database (trail_classifications table)
6. Broadcast to all viewers via WebSocket
7. Update UI in real-time
```

### 4. Echo Configuration

**File**: `resources/js/bootstrap.js`

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Listen to stream events
window.Echo.channel(`stream.${streamId}`)
    .listen('StreamStarted', (e) => {
        console.log('Stream started:', e);
    })
    .listen('StreamEnded', (e) => {
        console.log('Stream ended:', e);
    })
    .listen('ViewerCountUpdated', (e) => {
        updateViewerCount(e.viewer_count);
    })
    .listen('TrailClassified', (e) => {
        updateClassification(e);
    });

// Listen to chat
window.Echo.private(`stream.${streamId}.chat`)
    .listen('ChatMessageSent', (e) => {
        appendChatMessage(e);
    });
```

---

## 🧪 Performance Testing

### Artillery Configuration

**File**: `testing/performance-test.yml`

```yaml
config:
  target: "https://reverb.muncak.id"
  phases:
    - duration: 30
      arrivalRate: 2
      name: "Warm-up"
    - duration: 60
      arrivalRate: 5
      rampTo: 10
      name: "Light Load"
    - duration: 90
      arrivalRate: 10
      rampTo: 25
      name: "Medium Load"
    - duration: 120
      arrivalRate: 25
      rampTo: 50
      name: "Heavy Load"
    - duration: 60
      arrivalRate: 75
      name: "Spike Test"
    - duration: 30
      arrivalRate: 5
      name: "Cool-down"

scenarios:
  - name: "Viewer Watching Stream"
    weight: 70
    flow:
      - get:
          url: "/live-cam/{{ streamSlug }}"
      - think: 2
      - post:
          url: "/live-cam/{{ streamSlug }}/viewer-count"
          json:
            action: "join"
      - think: 60
      - post:
          url: "/live-cam/{{ streamSlug }}/viewer-count"
          json:
            action: "leave"

  - name: "Heavy Chat Activity"
    weight: 20
    flow:
      - post:
          url: "/live-cam/{{ streamSlug }}/chat"
          json:
            message: "{{ $randomString() }}"
      - think: 5

  - name: "Quick Viewer (Bounce)"
    weight: 10
    flow:
      - get:
          url: "/live-cam/{{ streamSlug }}"
      - think: 3
```

### Test Metrics

**Measured Parameters:**
1. **Latency**: HTTP request latency, WebSocket connection time
2. **Throughput**: Requests per second (RPS)
3. **CPU Usage**: Server CPU utilization
4. **Memory Usage**: Server memory consumption
5. **Concurrent Connections**: Max concurrent WebSocket connections
6. **Error Rate**: Percentage of failed requests
7. **Video Quality Stability**: Quality changes during load

**Test Results Location:**
```
testing/results/{timestamp}/
  ├── performance-report.html  (Visual report)
  ├── performance-report.pdf   (PDF export)
  ├── report.json              (Raw Artillery data)
  ├── system_metrics.csv       (CPU/Memory metrics)
  ├── server_metrics.csv       (Response times)
  └── summary.txt              (Text summary)
```

**Running Tests:**
```bash
# Run performance test
cd testing
bash run-performance-test.sh

# Generate reports
bash generate-reports.sh

# View HTML report
open results/latest/performance-report.html
```

---

## 📝 Environment Variables

```env
# LiveKit Configuration
LIVEKIT_API_KEY=your_api_key
LIVEKIT_API_SECRET=your_api_secret
LIVEKIT_URL=wss://livekit.muncak.id

# Reverb Configuration
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=reverb.muncak.id
REVERB_PORT=443
REVERB_SCHEME=https

# Broadcasting
BROADCAST_DRIVER=reverb

# Queue
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 🚀 Deployment Checklist

### Production Requirements

1. **Server Requirements:**
   - PHP 8.2+
   - MySQL 8.0+
   - Redis 6.0+
   - Node.js 18+
   - Nginx/Apache with WebSocket support

2. **Laravel Reverb:**
   ```bash
   # Install Reverb
   php artisan reverb:install
   
   # Start Reverb server
   php artisan reverb:start --host=0.0.0.0 --port=8080
   
   # Or use Supervisor for production
   ```

3. **LiveKit Setup:**
   - Create LiveKit Cloud account
   - Get API credentials
   - Configure CORS for your domain

4. **Queue Workers:**
   ```bash
   # Start queue worker
   php artisan queue:work --tries=3 --timeout=90
   ```

5. **Cron Jobs:**
   ```bash
   # Add to crontab
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

6. **SSL Certificate:**
   - Required for WebRTC and WebSocket
   - Use Let's Encrypt or commercial SSL

---

## 📚 Additional Resources

### Documentation Links
- [LiveKit Documentation](https://docs.livekit.io/)
- [Laravel Reverb Documentation](https://laravel.com/docs/reverb)
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [WebRTC Basics](https://webrtc.org/getting-started/overview)

### Code Examples
- Broadcaster implementation: `resources/js/livecam/broadcaster-livekit.js`
- Viewer implementation: `resources/js/livecam/viewer-livekit.js`
- Trail classifier: `resources/js/livecam/trail-classifier.js`
- Controller: `app/Http/Controllers/LiveCamController.php`

---

## 🐛 Troubleshooting

### Common Issues

1. **WebSocket Connection Failed**
   - Check Reverb server is running
   - Verify REVERB_HOST and REVERB_PORT in .env
   - Check firewall rules for WebSocket port

2. **LiveKit Connection Failed**
   - Verify LIVEKIT_URL is correct
   - Check API credentials
   - Ensure SSL certificate is valid

3. **Video Not Playing**
   - Check browser permissions (camera/microphone)
   - Verify LiveKit token is valid
   - Check network connectivity

4. **Chat Not Working**
   - Verify user is authenticated
   - Check Reverb connection
   - Verify chat channel subscription

---

## 📊 Future Enhancements

1. **Advanced AI Features:**
   - Object detection (wildlife, landmarks)
   - Weather prediction
   - Trail condition assessment
   - Safety alerts

2. **Social Features:**
   - Stream reactions (like, love, wow)
   - Viewer polls
   - Q&A sessions
   - Stream highlights/clips

3. **Monetization:**
   - Paid streams
   - Donations/tips
   - Subscription tiers
   - Ad integration

4. **Analytics:**
   - Viewer demographics
   - Engagement heatmaps
   - Retention analysis
   - Revenue reports

---

**Dokumentasi ini dibuat pada**: 3 Desember 2025  
**Versi**: 1.0  
**Author**: Muncak.id Development Team
