<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamAnalytic extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'live_stream_id',
        'timestamp',
        'viewer_count',
        'quality_level',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'viewer_count' => 'integer',
    ];

    public function liveStream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class);
    }
}
