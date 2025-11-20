<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrailClassification extends Model
{
    protected $fillable = [
        'live_stream_id',
        'hiking_trail_id',
        'weather',
        'crowd',
        'visibility',
        'weather_confidence',
        'crowd_confidence',
        'visibility_confidence',
        'image_path',
        'stream_delay_ms',
        'classified_at',
        'status',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'classified_at' => 'datetime',
        'weather_confidence' => 'decimal:2',
        'crowd_confidence' => 'decimal:2',
        'visibility_confidence' => 'decimal:2',
        'stream_delay_ms' => 'integer',
        'retry_count' => 'integer',
    ];

    public function liveStream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class);
    }

    public function hikingTrail(): BelongsTo
    {
        return $this->belongsTo(Rute::class, 'hiking_trail_id');
    }

    /**
     * Get latest classification for a live stream
     */
    public static function getLatestForStream($streamId)
    {
        return self::where('live_stream_id', $streamId)
            ->where('status', 'completed')
            ->orderBy('classified_at', 'desc')
            ->first();
    }

    /**
     * Get human-readable weather
     */
    public function getWeatherLabelAttribute(): string
    {
        return match($this->weather) {
            'cerah' => 'Cerah',
            'berawan' => 'Berawan/Berkabut',
            'hujan' => 'Hujan',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get human-readable crowd
     */
    public function getCrowdLabelAttribute(): string
    {
        return match($this->crowd) {
            'sepi' => 'Sepi (0-2 orang)',
            'sedang' => 'Sedang (3-10 orang)',
            'ramai' => 'Ramai (>10 orang)',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get human-readable visibility
     */
    public function getVisibilityLabelAttribute(): string
    {
        return match($this->visibility) {
            'jelas' => 'Jelas',
            'kabut_sedang' => 'Kabut Sedang',
            'kabut_tebal' => 'Tertutup Kabut',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get recommendation based on conditions
     */
    public function getRecommendationAttribute(): string
    {
        // Perfect conditions
        if ($this->weather === 'cerah' && $this->visibility === 'jelas') {
            return 'Kondisi sangat bagus untuk mendaki!';
        }

        // Good conditions but crowded
        if ($this->weather === 'cerah' && $this->crowd === 'ramai') {
            return 'Cuaca bagus tapi cukup ramai.';
        }

        // Rainy
        if ($this->weather === 'hujan') {
            return 'Hujan - Perhatikan keselamatan!';
        }

        // Heavy fog
        if ($this->visibility === 'kabut_tebal') {
            return 'Kabut tebal - Visibilitas terbatas!';
        }

        // Moderate conditions
        if ($this->weather === 'berawan' && $this->visibility === 'jelas') {
            return 'Kondisi cukup baik untuk mendaki.';
        }

        return 'Perhatikan kondisi cuaca.';
    }
}
