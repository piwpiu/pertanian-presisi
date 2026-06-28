<?php

namespace App\Services;

use App\Models\Prediksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PredictionGenerationService
{
    public function generate(): int
    {
        DB::table('prediksi')->truncate();

        $response = Http::timeout(3000)->get(config('services.lstm.generate_url'));

        if (! $response->successful()) {
            throw new \Exception('API Python gagal dipanggil. Status: ' . $response->status());
        }

        $payload = $response->json();

        if (! isset($payload['data']) || ! is_array($payload['data'])) {
            throw new \Exception('Format response API Python tidak valid.');
        }

        $rows = [];

        foreach ($payload['data'] as $item) {
            $rows[] = [
                'tanggal' => $item['tanggal'],
                'prediksi_suhu' => $item['prediksi_suhu'],
                'prediksi_kelembaban' => $item['prediksi_kelembaban'],
                'prediksi_curah_hujan' => $item['prediksi_curah_hujan'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Prediksi::insert($chunk);
        }

        Log::info('Generate prediksi selesai. Total data: ' . count($rows));

        return count($rows);
    }
}