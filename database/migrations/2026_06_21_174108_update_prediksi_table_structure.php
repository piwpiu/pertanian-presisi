<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('prediksi');

        Schema::create('prediksi', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->float('prediksi_suhu')->nullable();
            $table->float('prediksi_kelembaban')->nullable();
            $table->float('prediksi_curah_hujan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediksi');
    }
};