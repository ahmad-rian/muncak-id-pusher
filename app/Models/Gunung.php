<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Gunung extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'gunung';

    protected $fillable = [
        'negara_id',
        'kode_kabupaten_kota',
        'lokasi',
        'nama',
        'slug',
        'deskripsi',
        'long',
        'lat',
        'elev',
        'point',
    ];

    protected $guarded = ['id'];

    protected $hidden = ['point'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('mountain')->singleFile()->useFallbackUrl('/img/placeholder/image.png');

        $this->addMediaCollection('gallery')->onlyKeepLatest(2);
    }

    public function getImageUrl(): ?string
    {
        return $this->getFirstMediaUrl('mountain');
    }

    public function rute()
    {
        return $this->hasMany(Rute::class, 'gunung_id');
    }

    public function kabupatenKota()
    {
        return $this->belongsTo(KabupatenKota::class, 'kode_kabupaten_kota', 'kode');
    }

    public function negara()
    {
        return $this->belongsTo(Negara::class, 'negara_id', 'id');
    }
}
