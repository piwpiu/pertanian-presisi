<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klimatologi extends Model
{
    protected $table = 'klimatologi';

    protected $fillable = [
        'tanggal',
        'TN',
        'TX',
        'TAVG',
        'RH_AVG',
        'RR',
        'SS',
        'data_json',
    ];

    protected $casts = [
        'data_json' => 'array',
        'tanggal' => 'date',
    ];
}
