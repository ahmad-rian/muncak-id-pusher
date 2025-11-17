<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Blog extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'blog';

    protected $guarded = ['id'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('blog')->singleFile()->useFallbackUrl('/img/placeholder/image.png');
    }

    public function getImageUrl(): ?string
    {
        return $this->getFirstMediaUrl('blog');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
