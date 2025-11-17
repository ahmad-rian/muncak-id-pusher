<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Point extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'point';

    protected $guarded = ['id'];

    protected $hidden = ['point'];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('point-gallery')
            ->onlyKeepLatest(2)
            ->useFallbackUrl('/img/placeholder/image.png');
    }

    public function getGalleryUrls()
    {
        return $this
            ->getMedia('point-gallery')
            ->map(fn($media) => $media->getUrl());
    }

    public function rute()
    {
        return $this->belongsTo(Rute::class, 'rute_id');
    }

    public function comment()
    {
        return $this->hasMany(Comment::class, 'point_id');
    }
}
