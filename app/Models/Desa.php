<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desa extends Model
{
    use HasFactory;

    protected $table = 'desa';
    protected $primaryKey = 'kode';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];


    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kode_kecamatan', 'kode');
    }

    public function gunung()
    {
        return $this->hasMany(Gunung::class, 'kode_desa', 'kode');
    }

    public function rute()
    {
        return $this->hasMany(Rute::class, 'kode_desa', 'kode');
    }
}
