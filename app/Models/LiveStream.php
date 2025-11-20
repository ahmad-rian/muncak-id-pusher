<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class LiveStream extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'hiking_trail_id',
        'location',
        'broadcaster_id',
        'status',
        'current_quality',
        'viewer_count',
        'total_views',
        'stream_key',
        'pusher_channel_id',
        'thumbnail_url',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'viewer_count' => 'integer',
        'total_views' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stream) {
            if (empty($stream->stream_key)) {
                $stream->stream_key = Str::random(32);
            }
            if (empty($stream->pusher_channel_id)) {
                $stream->pusher_channel_id = 'live-stream.' . Str::random(16);
            }
            if (empty($stream->slug)) {
                $stream->slug = Str::slug($stream->title) . '-' . Str::random(6);
            }
        });

        static::updating(function ($stream) {
            // Update slug when title changes
            if ($stream->isDirty('title') && !$stream->isDirty('slug')) {
                $stream->slug = Str::slug($stream->title) . '-' . Str::random(6);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function hikingTrail(): BelongsTo
    {
        return $this->belongsTo(Rute::class, 'hiking_trail_id');
    }

    public function broadcaster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broadcaster_id');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(StreamAnalytic::class);
    }

    public function classifications(): HasMany
    {
        return $this->hasMany(TrailClassification::class);
    }

    public function latestClassification(): HasOne
    {
        return $this->hasOne(TrailClassification::class)
            ->where('status', 'completed')
            ->latestOfMany('classified_at');
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function isOffline(): bool
    {
        return $this->status === 'offline';
    }

    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }
}
