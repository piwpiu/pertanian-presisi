<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediksi extends Model
{
    protected $table = 'prediksi';

    protected $fillable = [
        'tanggal',
        'prediksi_suhu',
        'prediksi_kelembaban',
        'prediksi_curah_hujan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];
}