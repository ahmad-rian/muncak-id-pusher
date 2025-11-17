# Muncak.id API Documentation

## Base URL

```
http://127.0.0.1:8000/api/v1
```

## Authentication

Currently, no authentication is required for these endpoints.

## Response Format

All API responses follow this standard format:

```json
{
  "success": true,
  "message": "message",
  "data": {}
}
```

## Endpoints

### Mountains (Gunung)

#### List Mountains

```http
GET /api/v1/gunung
```

**Features:**

- Paginated results (default: 15 per page)
- Default relationship loading (negara, kabupatenKota)
- Filtering by any field including relationships
- Sorting by any field
- Route counting and media URL inclusion

**Query Parameters:**

| Parameter                 | Type    | Description                              | Example                 |
| ------------------------- | ------- | ---------------------------------------- | ----------------------- |
| `page`                    | integer | Page number                              | `?page=2`               |
| `per_page`                | integer | Items per page (max: 100)                | `?per_page=10`          |
| `filter[field]`           | string  | Filter by field value                    | `?filter[nama]=Rinjani` |
| `filter[field][operator]` | string  | Filter with operator                     | `?filter[elev][>]=2000` |
| `sort`                    | string  | Sort by field (prefix with `-` for desc) | `?sort=-elev,nama`      |

**Available Filters:**

- `nama` - Mountain name
- `elev` - Elevation in meters
- `lokasi` - Location description
- `negara.nama` - Country name
- `kabupatenKota.nama` - Regency/City name

**Available Sorts:**

- `nama` - Mountain name
- `elev` - Elevation
- `created_at` - Creation date
- `updated_at` - Last update

**Example Request:**

```bash
curl "http://127.0.0.1:8000/api/v1/gunung?filter[elev][>]=2000&sort=-elev&per_page=5"
```

**Example Response:**

```json
{
  "success": true,
  "message": "Mountains retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "nama": "Gunung Rinjani",
        "slug": "gunung-rinjani",
        "elev": 3726,
        "lat": -8.411,
        "long": 116.457,
        "lokasi": "Lombok, Nusa Tenggara Barat",
        "deskripsi": "Gunung berapi aktif tertinggi kedua di Indonesia",
        "negara_id": 1,
        "kode_kabupaten_kota": "5201",
        "image_url": "http://127.0.0.1:8000/storage/gunung/rinjani.jpg",
        "rute_count": 3,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "media": [],
        "negara": {
          "id": 1,
          "nama": "Indonesia",
          "kode": "ID"
        },
        "kabupaten_kota": {
          "id": 1,
          "nama": "Lombok Timur",
          "kode": "5201"
        }
      }
    ],
    "current_page": 1,
    "first_page_url": "http://127.0.0.1:8000/api/v1/gunung?page=1",
    "from": 1,
    "next_page_url": "http://127.0.0.1:8000/api/v1/gunung?page=2",
    "path": "http://127.0.0.1:8000/api/v1/gunung",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 25
  }
}
```

#### Get Single Mountain

```http
GET /api/v1/gunung/{id}
```

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|--------------|
| `id` | integer | Mountain ID |

**Example Request:**

```bash
curl "http://127.0.0.1:8000/api/v1/gunung/1"
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "nama": "Gunung Rinjani",
    "slug": "gunung-rinjani",
    "elev": 3726,
    "lat": -8.411,
    "long": 116.457,
    "lokasi": "Lombok, Nusa Tenggara Barat",
    "deskripsi": "Gunung berapi aktif tertinggi kedua di Indonesia",
    "negara_id": 1,
    "kode_kabupaten_kota": "5201",
    "image_url": "http://127.0.0.1:8000/storage/gunung/rinjani.jpg",
    "rute_count": 3,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "media": [],
    "negara": {
      "id": 1,
      "nama": "Indonesia",
      "kode": "ID"
    },
    "kabupaten_kota": {
      "id": 1,
      "nama": "Lombok Timur",
      "kode": "5201"
    }
  }
}
```

### Routes (Rute)

#### List Routes

```http
GET /api/v1/rute
```

**Features:**

- Paginated results (default: 20 per page)
- Default relationship loading (gunung, negara, desa with complete hierarchy)
- Filtering by any field including relationships
- Sorting by any field
- Image URL inclusion (excludes gallery URLs)
- Excludes constant variables (a_k, b_k, etc.) and boolean geometry flags

**Query Parameters:**

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------||
| `page` | integer | Page number | `?page=2` |
| `per_page` | integer | Items per page (max: 100) | `?per_page=10` |
| `filter[field]` | string | Filter by field value | `?filter[nama]=Sembalun` |
| `sort` | string | Sort by field (prefix with `-` for desc) | `?sort=-comment_rating,nama` |

**Available Filters:**

- `nama` - Route name
- `lokasi` - Location description
- `deskripsi` - Route description
- `gunung_id` - Mountain ID
- `negara_id` - Country ID
- `kode_desa` - Village code
- `gunung.nama` - Mountain name
- `negara.nama` - Country name
- `desa.nama` - Village name

**Available Sorts:**

- `nama` - Route name
- `created_at` - Creation date
- `updated_at` - Last update
- `comment_count` - Number of comments
- `comment_rating` - Average rating
- `gunung.nama` - Mountain name

**Example Request:**

```bash
curl "http://127.0.0.1:8000/api/v1/rute?filter[gunung.nama]=Rinjani&sort=-comment_rating&per_page=5"
```

**Example Response:**

```json
{
  "success": true,
  "message": "Routes retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "gunung_id": 1,
        "negara_id": 1,
        "kode_desa": "52.71.01.2001",
        "lokasi": "Sembalun, Lombok Timur",
        "nama": "Jalur Sembalun",
        "slug": "jalur-sembalun",
        "deskripsi": "Jalur pendakian populer dengan pemandangan savana",
        "informasi": "Jalur terpopuler untuk pendakian Rinjani",
        "aturan_dan_larangan": "Dilarang membawa sampah plastik",
        "segmentasi": "Basecamp - Pos 1 - Pos 2 - Plawangan",
        "rute_tingkat_kesulitan_id": 3,
        "comment_count": 25,
        "comment_rating": 4.5,
        "image_url": "http://127.0.0.1:8000/storage/rute/sembalun.jpg",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "created_at_human": "3 months ago",
        "gunung": {
          "id": 1,
          "nama": "Gunung Rinjani",
          "slug": "gunung-rinjani",
          "elev": 3726
        },
        "negara": {
          "id": 1,
          "nama": "Indonesia"
        },
        "desa": {
          "kode": "52.71.01.2001",
          "nama": "Sembalun Lawang",
          "nama_lain": null,
          "kode_kecamatan": "52.71.01",
          "kecamatan": {
            "kode": "52.71.01",
            "nama": "Sembalun",
            "nama_lain": null,
            "kode_kabupaten_kota": "52.71",
            "kabupaten_kota": {
              "kode": "52.71",
              "nama": "Lombok Timur",
              "nama_lain": null,
              "kode_provinsi": "52",
              "provinsi": {
                "kode": "52",
                "nama": "Nusa Tenggara Barat",
                "nama_lain": "NTB"
              }
            }
          }
        },
        "rute_tingkat_kesulitan": {
          "id": 3,
          "nama": "Sedang",
          "deskripsi": "Memerlukan pengalaman pendakian dasar"
        }
      }
    ],
    "current_page": 1,
    "first_page_url": "http://127.0.0.1:8000/api/v1/rute?page=1",
    "from": 1,
    "next_page_url": "http://127.0.0.1:8000/api/v1/rute?page=2",
    "path": "http://127.0.0.1:8000/api/v1/rute",
    "per_page": 20,
    "prev_page_url": null,
    "to": 20
  }
}
```

#### Get Single Route

```http
GET /api/v1/rute/{id}
```

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Route ID |

**Example Request:**

```bash
curl "http://127.0.0.1:8000/api/v1/rute/1"
```

**Example Response:**

```json
{
  "success": true,
  "message": "Route retrieved successfully",
  "data": {
    "id": 1,
    "gunung_id": 1,
    "negara_id": 1,
    "kode_desa": "52.71.01.2001",
    "lokasi": "Sembalun, Lombok Timur",
    "nama": "Jalur Sembalun",
    "slug": "jalur-sembalun",
    "deskripsi": "Jalur pendakian populer dengan pemandangan savana",
    "informasi": "Jalur terpopuler untuk pendakian Rinjani",
    "aturan_dan_larangan": "Dilarang membawa sampah plastik",
    "segmentasi": 1,
    "rute_tingkat_kesulitan_id": 3,
    "comment_count": 25,
    "comment_rating": 4.5,
    "image_url": "http://127.0.0.1:8000/storage/rute/sembalun.jpg",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "created_at_human": "3 months ago",
    "gunung": {
      "id": 1,
      "nama": "Gunung Rinjani",
      "slug": "gunung-rinjani",
      "elev": 3726
    },
    "negara": {
      "id": 1,
      "nama": "Indonesia"
    },
    "desa": {
      "kode": "52.71.01.2001",
      "nama": "Sembalun Lawang",
      "nama_lain": null,
      "kode_kecamatan": "52.71.01",
      "kecamatan": {
        "kode": "52.71.01",
        "nama": "Sembalun",
        "nama_lain": null,
        "kode_kabupaten_kota": "52.71",
        "kabupaten_kota": {
          "kode": "52.71",
          "nama": "Lombok Timur",
          "nama_lain": null,
          "kode_provinsi": "52",
          "provinsi": {
            "kode": "52",
            "nama": "Nusa Tenggara Barat",
            "nama_lain": "NTB"
          }
        }
      }
    },
    "rute_tingkat_kesulitan": {
      "id": 3,
      "nama": "Sedang",
      "deskripsi": "Memerlukan pengalaman pendakian dasar"
    }
  }
}
```

#### Get Route Geometry (GeoJSON)

```http
GET /api/v1/rute/{id}.geojson
```

**Description:**
Returns the route's geometry data in GeoJSON LineString format, suitable for mapping and visualization purposes.

**Path Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Route ID |

**Response Headers:**
- `Content-Type: application/geo+json`

**Example Request:**

```bash
curl "http://127.0.0.1:8000/api/v1/rute/1.geojson"
```

**Example Response:**

```json
{
  "type": "LineString",
  "coordinates": [
    [110.01553, -7.319079],
    [110.014058, -7.317939],
    [110.014087, -7.316711],
    [110.013108, -7.316201],
    [110.013558, -7.314412]
  ]
}
```

**Error Responses:**

- **404 Not Found** - Route not found or geometry not available

```json
{
  "success": false,
  "message": "Route not found"
}
```

```json
{
  "success": false,
  "message": "Route geometry not available"
}
```

## Error Responses

### 404 Not Found

```json
{
  "success": false,
  "data": null
}
```

### 422 Validation Error

```json
{
  "success": false,
  "data": {
    "errors": {
      "field_name": ["Error message"]
    }
  }
}
```

### 500 Server Error

```json
{
  "success": false,
  "data": null
}
```

## Usage Examples

### Search Mountains by Name

```bash
curl "http://127.0.0.1:8000/api/v1/gunung?filter[nama]=Rinjani"
```

### Get Mountains Above 3000m Elevation

```bash
curl "http://127.0.0.1:8000/api/v1/gunung?filter[elev][>]=3000"
```

### Get Mountains in Indonesia

```bash
curl "http://127.0.0.1:8000/api/v1/gunung?filter[negara.nama]=Indonesia"
```

### Sort by Elevation (Highest First)

```bash
curl "http://127.0.0.1:8000/api/v1/gunung?sort=-elev"
```

### Search Routes by Mountain Name

```bash
curl "http://127.0.0.1:8000/api/v1/rute?filter[gunung.nama]=Rinjani"
```

### Get Highly Rated Routes

```bash
curl "http://127.0.0.1:8000/api/v1/rute?filter[comment_rating][>]=4.0&sort=-comment_rating"
```

### Get Routes by Location

```bash
curl "http://127.0.0.1:8000/api/v1/rute?filter[lokasi]=Lombok"
```

### Paginated Results

```bash
curl "http://127.0.0.1:8000/api/v1/gunung?per_page=10&page=2"
```

### Complex Query

```bash
curl "http://127.0.0.1:8000/api/v1/gunung?filter[elev][>]=2000&filter[negara.nama]=Indonesia&sort=-elev&per_page=5"
```

## Data Fields

### Mountain (Gunung) Fields

- `id` - Unique identifier
- `nama` - Mountain name
- `slug` - URL-friendly name
- `elev` - Elevation in meters
- `lat` - Latitude coordinate
- `long` - Longitude coordinate
- `lokasi` - Location description
- `deskripsi` - Mountain description
- `negara_id` - Country ID
- `kode_kabupaten_kota` - Regency/City code
- `image_url` - Main image URL
- `rute_count` - Number of associated routes
- `media` - Associated media files
- `negara` - Country relationship data
- `kabupaten_kota` - Regency/City relationship data
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

### Route (Rute) Fields

- `id` - Unique identifier
- `gunung_id` - Associated mountain ID
- `negara_id` - Country ID
- `kode_desa` - Village code
- `lokasi` - Location description
- `nama` - Route name
- `slug` - URL-friendly name
- `deskripsi` - Route description
- `informasi` - Additional information
- `aturan_dan_larangan` - Rules and restrictions
- `segmentasi` - Route segmentation/waypoints
- `rute_tingkat_kesulitan_id` - Difficulty level ID
- `comment_count` - Number of comments
- `comment_rating` - Average rating
- `image_url` - Main image URL (excludes gallery)
- `created_at_human` - Human-readable creation time
- `gunung` - Mountain relationship data
- `negara` - Country relationship data
- `desa` - Village relationship data (includes hierarchy to provinsi)
- `rute_tingkat_kesulitan` - Difficulty level relationship data
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

**Excluded Fields:**

- Constant variables: `a_k`, `b_k`, `c_k`, `d_k`, `a_wt`, `b_wt`, `c_wt`, `d_wt`, `e_wt`, `f_wt`, `g_wt`, `h_wt`, `i_wt`, `j_wt`, `k_wt`, `a_cps`, `b_cps`, `c_kr`, `d_kr`, `e_kr`, `f_kr`, `g_kr`, `h_kr`
- Boolean geometry flags: `is_verified`, `is_cuaca_siap`, `is_kalori_siap`, `is_kriteria_jalur_siap`
- Geometry data: `rute` (contains route coordinates)

## Rate Limiting

Currently, no rate limiting is implemented.

## Changelog

### v1.1.0 (Current)

- Added Routes (Rute) API endpoints
- Implemented route listing with filtering, sorting, and pagination
- Added route detail endpoint with comprehensive relationship data
- Added route geometry endpoint returning GeoJSON LineString format
- Excluded constant variables and boolean geometry flags from route responses
- Included full location hierarchy (desa → kecamatan → kabupaten_kota → provinsi)
- Added image URL support for routes (excluding gallery URLs)
- Updated pagination to use simple page numbers instead of nested page objects
- Removed includes and field selection parameters, relationships loaded by default

### v1.0.0

- Initial API release with Gunung endpoints
- Mountain (Gunung) endpoints with filtering, sorting, and pagination
- Comprehensive relationship data including country and regency information
- Image URL support for mountain media
