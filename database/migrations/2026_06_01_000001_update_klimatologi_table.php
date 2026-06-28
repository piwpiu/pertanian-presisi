<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('klimatologi', function (Blueprint $table) {
            // Rename kolom yang sudah ada sesuai dengan field baru
            $table->float('TN')->nullable()->after('tanggal');
            $table->float('TX')->nullable()->after('TN');
            $table->float('TAVG')->nullable()->after('TX');
            $table->float('RH_AVG')->nullable()->after('TAVG');
            $table->float('RR')->nullable()->after('RH_AVG');
            $table->float('SS')->nullable()->after('RR');
            
            // Buat kolom suhu, curah_hujan, kelembaban nullable untuk backward compatibility
            $table->dropColumn(['suhu', 'curah_hujan', 'kelembaban']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('klimatologi', function (Blueprint $table) {
            $table->dropColumn(['TN', 'TX', 'TAVG', 'RH_AVG', 'RR', 'SS']);
            
            // Kembalikan kolom lama
            $table->float('suhu')->nullable();
            $table->float('curah_hujan')->nullable();
            $table->float('kelembaban')->nullable();
        });
    }
};
