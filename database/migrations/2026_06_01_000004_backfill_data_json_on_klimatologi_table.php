<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('klimatologi')
            ->whereNull('data_json')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('klimatologi')
                        ->where('id', $row->id)
                        ->update([
                            'data_json' => json_encode([
                                'tanggal' => Carbon::parse($row->tanggal)->format('d-m-Y'),
                                'tn' => $row->TN !== null ? (float) $row->TN : null,
                                'tx' => $row->TX !== null ? (float) $row->TX : null,
                                'tavg' => $row->TAVG !== null ? (float) $row->TAVG : null,
                                'rh_avg' => $row->RH_AVG !== null ? (float) $row->RH_AVG : null,
                                'rr' => $row->RR !== null ? (float) $row->RR : null,
                                'ss' => $row->SS !== null ? (float) $row->SS : null,
                            ]),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Tidak dikembalikan ke NULL agar data yang sudah dilengkapi tetap aman.
    }
};
