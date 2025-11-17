<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Comment extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'comment';

    protected $guarded = ['id'];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('comment-gallery')
            ->onlyKeepLatest(3);
    }

    public function getGalleryUrls()
    {
        return $this
            ->getMedia('comment-gallery')
            ->map(fn($media) => $media->getUrl());
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rute()
    {
        return $this->belongsTo(Rute::class, 'rute_id');
    }

    public function point()
    {
        return $this->belongsTo(Point::class, 'point_id');
    }
}
