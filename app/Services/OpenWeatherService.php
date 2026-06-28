<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenWeatherService
{
    public const CACHE_KEY = 'openweather.current_weather';
    public const CACHE_TTL_MINUTES = 15;
    private const TIMEZONE = 'Asia/Jakarta';

    // Mengambil data cuaca saat ini dari cache jika tersedia dan masih valid. Jika tidak, mengambil data terbaru dari OpenWeather dan memperbarui cache.
    public function cachedCurrentWeather(): ?array
    {
        $weather = Cache::get(self::CACHE_KEY);

        return is_array($weather) ? $weather : null;
    }

    // Refresh cache dengan data terbaru dari OpenWeather. Jika berhasil, cache akan diperbarui. Jika gagal, cache lama tetap digunakan (jika ada).
    public function refreshCurrentWeatherCache(): array
    {
        $weather = $this->currentWeather();
        $refreshedAt = Carbon::now(self::TIMEZONE);

        $weather['cache_refreshed_at'] = $refreshedAt->toIso8601String();
        $weather['cache_expires_at'] = $refreshedAt->copy()
            ->addMinutes(self::CACHE_TTL_MINUTES)
            ->toIso8601String();

        Cache::forever(self::CACHE_KEY, $weather);

        return $weather;
    }

    // Mengambil data cuaca saat ini langsung dari OpenWeather. Jika terjadi kesalahan, akan dilemparkan exception.
    public function currentWeather(): array
    {
        $config = config('services.openweather');
        $apiKey = $config['key'] ?? null;

        if (empty($apiKey)) {
            throw new RuntimeException('OpenWeather API key is not configured.');
        }

        // Menambahkan retry agar dapat mencoba mengulang hingga 3 kali dengan jeda 1 detik jika terjadi kegagalan. 
        $response = Http::retry(3, 1000)
        ->timeout($config['timeout'] ?? 5)
        ->get($config['url'], [
            'lat' => $config['latitude'],
            'lon' => $config['longitude'],
            'appid' => $apiKey,
            'units' => 'metric',
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('OpenWeather request failed.');
        }

        // Memastikan response mengandung timestamp yang valid.
        $payload = $response->json();
        $timestamp = (int) data_get($payload, 'dt', 0);

        if ($timestamp <= 0) {
            throw new RuntimeException('OpenWeather response does not include a valid dt timestamp.');
        }

        $observedAt = Carbon::createFromTimestamp($timestamp, 'UTC')->setTimezone(self::TIMEZONE);

        // Memastikan format yang konsisten, termasuk sumber data dan label waktu pembaruan.
        return [
            'TAVG' => round((float) data_get($payload, 'main.temp', 0), 2),
            'RH_AVG' => round((float) data_get($payload, 'main.humidity', 0), 2),
            'RR' => round((float) (data_get($payload, 'rain.1h') ?? data_get($payload, 'rain.3h') ?? 0), 2),
            'source' => 'realtime',
            'openweather_dt' => $timestamp,
            'updated_at' => $observedAt->toIso8601String(),
            'updated_at_label' => $observedAt->locale('id')->translatedFormat('H:i') . ' WIB',
        ];
    }
}
