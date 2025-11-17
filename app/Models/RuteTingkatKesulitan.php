<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuteTingkatKesulitan extends Model
{
    use HasFactory;

    protected $table = 'rute_tingkat_kesulitan';

    protected $guarded = ['id'];

    public function rute()
    {
        return $this->hasMany(Rute::class, 'rute_id');
    }
}
