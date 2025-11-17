<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KabupatenKota extends Model
{
    use HasFactory;

    protected $table = 'kabupaten_kota';
    protected $primaryKey = 'kode';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public function gunung()
    {
        return $this->hasMany(Gunung::class, 'kode_kabupaten_kota', 'kode');
    }

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'kode_provinsi', 'kode');
    }

    public function kecamatan()
    {
        return $this->hasMany(Kecamatan::class, 'kode_kecamatan', 'kode');
    }
}
