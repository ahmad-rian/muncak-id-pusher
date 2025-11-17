<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    use HasFactory;

    protected $table = 'kecamatan';
    protected $primaryKey = 'kode';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public function gunung()
    {
        return $this->hasMany(Gunung::class, 'kode_kecamatan', 'kode');
    }

    public function kabupatenKota()
    {
        return $this->belongsTo(KabupatenKota::class, 'kode_kabupaten_kota', 'kode');
    }

    public function desa()
    {
        return $this->hasMany(Desa::class, 'kode_kecamatan', 'kode');
    }
}
