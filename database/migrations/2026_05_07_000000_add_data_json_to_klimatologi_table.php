<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klimatologi', function (Blueprint $table) {
            if (!Schema::hasColumn('klimatologi', 'data_json')) {
                $table->json('data_json')->nullable()->after('radiasi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('klimatologi', function (Blueprint $table) {
            $table->dropColumn('data_json');
        });
    }
};
