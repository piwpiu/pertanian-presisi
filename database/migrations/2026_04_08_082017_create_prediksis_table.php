<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('prediksi')) {
            Schema::create('prediksi', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->float('prediksi_hujan');
                $table->float('prediksi_suhu');
                $table->float('prediksi_kelembaban');
                $table->float('kebutuhan_air')->nullable();
                $table->string('risiko')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediksi');
    }
};
