<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RutePrediksiCuaca extends Model
{
    use HasFactory;

    protected $table = 'rute_prediksi_cuaca';
    protected $guarded = ['id'];
    protected $casts = [
        'result' => 'json',
    ];

    public function rute()
    {
        return $this->belongsTo(Rute::class, 'rute_id');
    }
}
