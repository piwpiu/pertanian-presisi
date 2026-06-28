<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('klimatologi', 'radiasi')) {
            return;
        }

        if (Schema::hasColumn('klimatologi', 'SS')) {
            DB::table('klimatologi')
                ->whereNull('SS')
                ->whereNotNull('radiasi')
                ->update(['SS' => DB::raw('radiasi')]);
        }

        Schema::table('klimatologi', function (Blueprint $table) {
            $table->dropColumn('radiasi');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('klimatologi', 'radiasi')) {
            return;
        }

        Schema::table('klimatologi', function (Blueprint $table) {
            $table->float('radiasi')->nullable()->after('SS');
        });

        if (Schema::hasColumn('klimatologi', 'SS')) {
            DB::table('klimatologi')
                ->whereNull('radiasi')
                ->whereNotNull('SS')
                ->update(['radiasi' => DB::raw('SS')]);
        }
    }
};
