<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory;

    protected $table = 'provinsi';
    protected $primaryKey = 'kode';
    protected $keyType = 'string';
    public $incrementing = false;


    protected $guarded = ['id'];

    public function gunung()
    {
        return $this->hasMany(Gunung::class, 'kode_provinsi', 'kode');
    }

    public function kabupatenKota()
    {
        return $this->hasMany(KabupatenKota::class, 'kode_provinsi', 'kode');
    }

    public function negara()
    {
        return $this->belongsTo(Negara::class, 'negara_id', 'id');
    }
}
