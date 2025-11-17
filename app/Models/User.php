<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'bio',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo-profile')->singleFile()->useFallbackUrl('/img/placeholder/image.png');
    }

    public function getAvatarUrl(): ?string
    {
        return $this->getFirstMediaUrl('photo-profile');
    }

    public function userProvider()
    {
        return $this->hasMany(UserProvider::class, 'user_id');
    }

    public function comment()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    public function blog()
    {
        return $this->hasMany(Blog::class, 'user_id');
    }

    public function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->roles[0]->name === 'admin'
        );
    }
}
