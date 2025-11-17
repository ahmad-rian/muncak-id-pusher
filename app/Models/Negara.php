<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Negara extends Model
{
    protected $table = 'negara';

    protected $guarded = ['id'];
    protected $fillable = ['nama', 'nama_lain', 'slug', 'kode'];

    public function provinsi()
    {
        return $this->hasMany(Provinsi::class, 'negara_id', 'id');
    }
}
