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
        if (!Schema::hasTable('klimatologi')) {
            Schema::create('klimatologi', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->float('suhu');
                $table->float('curah_hujan');
                $table->float('kelembaban');
                $table->float('radiasi')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klimatologi');
    }
};
