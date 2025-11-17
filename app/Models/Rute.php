<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Rute extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'rute';

    protected $fillable = [
        'gunung_id',
        'negara_id',
        'kode_desa',
        'lokasi',
        'nama',
        'slug',
        'deskripsi',
        'informasi',
        'aturan_dan_larangan',
        'is_verified',
        'rute',
        'is_cuaca_siap',
        'is_kalori_siap',
        'is_kriteria_jalur_siap',
        'segmentasi',
        'a_k',
        'b_k',
        'c_k',
        'd_k',
        'a_wt',
        'b_wt',
        'c_wt',
        'd_wt',
        'e_wt',
        'f_wt',
        'g_wt',
        'h_wt',
        'i_wt',
        'j_wt',
        'k_wt',
        'a_cps',
        'b_cps',
        'c_kr',
        'd_kr',
        'e_kr',
        'f_kr',
        'g_kr',
        'h_kr',
        'rute_tingkat_kesulitan_id',
        'comment_count',
        'comment_rating',
    ];

    protected $guarded = ['id'];

    protected $hidden = ['rute'];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('rute-image')
            ->singleFile()
            ->useFallbackUrl('/img/placeholder/image.png');
        $this
            ->addMediaCollection('rute-gallery')

            ->useFallbackUrl('/img/placeholder/image.png');
    }

    public function getImageUrl(): ?string
    {
        return $this->getFirstMediaUrl('rute-image');
    }

    public function getGalleryUrls()
    {
        return $this
            ->getMedia('rute-gallery')
            ->map(fn($media) => $media->getUrl())
            ->whenEmpty(fn() => collect(['/img/placeholder/image.png']));
    }

    public function point()
    {
        return $this->hasMany(Point::class, 'rute_id')->orderBy('nomor');
    }

    public function lastPoint()
    {
        return $this->hasOne(Point::class, 'rute_id')->orderBy('nomor', 'desc')->limit(1);
    }

    public function gunung()
    {
        return $this->belongsTo(Gunung::class, 'gunung_id');
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'kode_desa', 'kode');
    }

    public function negara()
    {
        return $this->belongsTo(Negara::class, 'negara_id', 'id');
    }

    public function rutePrediksiCuaca()
    {
        return $this->hasOne(RutePrediksiCuaca::class, 'rute_id');
    }

    public function ruteTingkatKesulitan()
    {
        return $this->belongsTo(RuteTingkatKesulitan::class, 'rute_tingkat_kesulitan_id');
    }

    public function comment()
    {
        return $this->hasMany(Comment::class, 'rute_id');
    }

    public function updatedAtId(): Attribute
    {
        return Attribute::make(
            fn() => Carbon::parse($this->updated_at)->locale('id')->isoFormat('dddd, D MMMM YYYY')
        );
    }
}

/**
 * USED VARIABLE
 * a_k, b_k, c_k, d_k
 * a_wt, b_wt, c_wt, d_wt, e_wt, f_wt
 * a_cps, b_cps,
 * c_kr, d_kr, e_kr, f_kr, g_kr, h_kr
 */
