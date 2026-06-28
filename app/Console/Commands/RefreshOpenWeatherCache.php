<?php

namespace App\Console\Commands;

use App\Services\OpenWeatherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

// This command is intended to be scheduled to run every 15 minutes to keep the OpenWeather cache fresh.
// Untuk mengambil data terbaru dari OpenWeather.
// Tujuannya untuk memastikan bahwa data cuaca yang ditampilkan di dashboard selalu terupdate, dengan batas maksimal 15 menit.
class RefreshOpenWeatherCache extends Command
{
    protected $signature = 'weather:refresh-openweather';
    protected $description = 'Refresh the shared OpenWeather cache for today weather data.';

    public function handle(OpenWeatherService $openWeather): int
    {
        try {
            // Jika berhasil, cache akan diperbarui dengan data terbaru.
            $weather = $openWeather->refreshCurrentWeatherCache();

            $this->info('OpenWeather cache refreshed. Observed at: ' . ($weather['updated_at_label'] ?? '-'));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            // Jika gagal, akan dicatat di log dan cache lama tetap digunakan (jika ada).
            Log::warning('OpenWeather refresh failed: ' . $e->getMessage());

            if ($openWeather->cachedCurrentWeather()) {
                $this->warn('OpenWeather refresh failed. Existing cache is still available.');

                return self::SUCCESS;
            }

            $this->error('OpenWeather refresh failed and no cache is available.');

            return self::FAILURE;
        }
    }
}
