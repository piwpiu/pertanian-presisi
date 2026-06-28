<?php

namespace App\Http\Controllers;

use App\Models\Klimatologi;
use App\Models\Prediksi;
use App\Services\OpenWeatherService;
use App\Services\RekomendasiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private const DASHBOARD_TIMEZONE = 'Asia/Jakarta';

    public function index(Request $request, OpenWeatherService $openWeather, RekomendasiService $rekomendasiService)
    {
        $data = null;
        $umur = null;
        $fase = null;
        $risiko = null;
        $warna = null;

        $tanggal = $request->tanggal_tanam ?? Carbon::now(self::DASHBOARD_TIMEZONE)->format('Y-m-d');
        $selectedDate = Carbon::parse($tanggal, self::DASHBOARD_TIMEZONE)->startOfDay();
        $today = Carbon::today(self::DASHBOARD_TIMEZONE);

        $tanggalTanam = $tanggal;
        $rekomendasiIklim = $rekomendasiService->hitung($tanggalTanam);
        $rekomendasiVarietas = $rekomendasiService->hitungRekomendasiVarietas($tanggalTanam);

        $grafikData = $this->buildGrafikData($request, $openWeather, $today);
        Log::info('Grafik Data Count: ' . count($grafikData['labels']));
        Log::info('Grafik Data: ' . json_encode($grafikData));

        $weatherData = $this->resolveWeatherForDate($selectedDate, $today, $openWeather);
        //dd($weatherData);

        $data = (object) [
            'suhu' => $weatherData['TAVG'],
            'curah_hujan' => $weatherData['RR'],
            'kelembaban' => $weatherData['RH_AVG'],
            'source' => $weatherData['source'],
            'source_label' => $this->sourceLabel($weatherData['source']),
            'updated_at_label' => $weatherData['updated_at_label'] ?? null,
        ];

        $tanggalTanam = Carbon::parse($tanggal, self::DASHBOARD_TIMEZONE);
        $umur = (int) $tanggalTanam->diffInDays($today);

        if ($umur <= 35) {
            $fase = 'Vegetatif Awal';
        } elseif ($umur <= 60) {
            $fase = 'Vegetatif Akhir';
        } elseif ($umur <= 90) {
            $fase = 'Generatif';
        } elseif ($umur <= 120) {
            $fase = 'Pematangan';
        } else {
            $fase = 'Periode Pascapanen';
        }

        if ($data->curah_hujan >= 50) {
            $risiko = 'Tinggi (Banjir)';
            $warna = 'red';
        } elseif ($data->curah_hujan >= 20) {
            $risiko = 'Waspada';
            $warna = 'yellow';
        } else {
            $risiko = 'Aman';
            $warna = 'green';
        }

        return view('dashboard', compact(
            'data',
            'umur',
            'fase',
            'risiko',
            'warna',
            'grafikData',
            'tanggalTanam',
            'rekomendasiIklim',
            'rekomendasiVarietas'
        ));
    }

    private function buildGrafikData(Request $request, OpenWeatherService $openWeather, Carbon $today): array
    {
        $grafikData = [
            'labels' => [],
            'suhu' => [],
            'curah_hujan' => [],
            'kelembaban' => [],
            'sources' => [],
            'source_labels' => [],
        ];

        if ($request->filled('tanggal_tanam')) {
            try {
                $startDate = Carbon::parse($request->tanggal_tanam, self::DASHBOARD_TIMEZONE)->startOfDay();
                
                // 3 hari sebelum dan 3 hari sesudah tanggal yang dipilih
                // for ($offset = -3; $offset <= 3; $offset++) {
                //     $date = $startDate->copy()->addDays($offset);

                // 7 hari sesudah tanggal yang dipilih
                for ($i = 0; $i < 7; $i++) {
                    $date = $startDate->copy()->addDays($i);
                    $resolved = $this->resolveWeatherForDate($date, $today, $openWeather);

                    $grafikData['labels'][] = $date->locale('id')->translatedFormat('d M');
                    $grafikData['suhu'][] = $resolved['TAVG'];
                    $grafikData['curah_hujan'][] = $resolved['RR'];
                    $grafikData['kelembaban'][] = $resolved['RH_AVG'];
                    $grafikData['sources'][] = $resolved['source'];
                    $grafikData['source_labels'][] = $this->sourceLabel($resolved['source']);
                }
            } catch (\Exception $e) {
                Log::warning('Tanggal input tidak valid untuk grafik: ' . $request->tanggal_tanam);
            }
        }

        if (!empty($grafikData['labels'])) {
            return $grafikData;
        }

        for ($offset = -3; $offset <= 3; $offset++) {
            $date = $today->copy()->addDays($offset);
            $resolved = $this->resolveWeatherForDate(
                $date,
                $today,
                $openWeather
            );

            $grafikData['labels'][] = $date->locale('id')->translatedFormat('d M');
            $grafikData['suhu'][] = $resolved['TAVG'];
            $grafikData['curah_hujan'][] = $resolved['RR'];
            $grafikData['kelembaban'][] = $resolved['RH_AVG'];
            $grafikData['sources'][] = $resolved['source'];
            $grafikData['source_labels'][] = $this->sourceLabel($resolved['source']);
        }

        return $grafikData;
    }

    // Menentukan data cuaca untuk tanggal tertentu. Jika tanggal adalah hari ini, mencoba menggunakan cache OpenWeather terlebih dahulu, jika tidak tersedia, menggunakan fallback prediksi. Jika tanggal di masa lalu, mencoba menggunakan data aktual dari database, jika tidak tersedia, menggunakan prediksi.
    private function resolveWeatherForDate(Carbon $date, Carbon $today, OpenWeatherService $openWeather): array
    {
        if ($date->isSameDay($today)) {
            $cachedWeather = $openWeather->cachedCurrentWeather();

            if ($cachedWeather) {
                return $this->normalizeWeatherData($cachedWeather, 'realtime');
            }

            Log::warning('Cache OpenWeather belum tersedia, menggunakan fallback prediksi.');

            return $this->normalizeWeatherData(
                $this->fetchPrediction($date->format('Y-m-d')),
                'fallback_realtime'
            );
        }

        if ($date->lt($today)) {
            $actual = Schema::hasTable('klimatologi')
                ? Klimatologi::whereDate('tanggal', $date->format('Y-m-d'))->first()
                : null;

            if ($actual) {
                return [
                    'TAVG' => $this->numberValue($actual->TAVG),
                    'RR' => $this->numberValue($actual->RR),
                    'RH_AVG' => $this->numberValue($actual->RH_AVG),
                    'source' => 'actual',
                ];
            }
        }

        // return $this->normalizeWeatherData(
        //     $this->fetchPrediction($date->format('Y-m-d')),
        //     'predicted'
        // );
        $prediction = Prediksi::whereDate('tanggal', $date->format('Y-m-d'))->first();

        if ($prediction) {
            return [
                'TAVG' => $this->numberValue($prediction->prediksi_suhu),
                'RR' => $this->numberValue($prediction->prediksi_curah_hujan),
                'RH_AVG' => $this->numberValue($prediction->prediksi_kelembaban),
                'source' => 'predicted',
            ];
        }

        return [
            'TAVG' => 0,
            'RR' => 0,
            'RH_AVG' => 0,
            'source' => 'prediction_not_available',
        ];
    }

    private function fetchPrediction(string $date): array
    {
        try {
            $response = Http::timeout(100)->get(config('services.lstm.predict_url'), [
                'TANGGAL' => $date,
            ]);

            if ($response->successful()) {
                Log::info('Tanggal prediksi: ' . $date);
                Log::info('Response prediksi: ', $response->json());

                return $response->json();
            }

            Log::warning('API prediksi gagal untuk tanggal ' . $date . ': HTTP ' . $response->status());
        } catch (\Throwable $e) {
            Log::warning('API prediksi gagal untuk tanggal ' . $date . ': ' . $e->getMessage());
        }

        return [
            'TAVG' => 0,
            'RR' => 0,
            'RH_AVG' => 0,
            'source' => 'predicted',
        ];
    }

    private function normalizeWeatherData(array $payload, string $defaultSource): array
    {
        return [
            'TAVG' => $this->numberValue($payload['TAVG'] ?? 0),
            'RR' => $this->numberValue($payload['RR'] ?? 0),
            'RH_AVG' => $this->numberValue($payload['RH_AVG'] ?? 0),
            'source' => $defaultSource,
            'updated_at_label' => $payload['updated_at_label'] ?? null,
        ];
    }

    private function sourceLabel(string $source): string
    {
        return match ($source) {
            'actual' => 'Data Aktual',
            'realtime' => 'Data Realtime (OpenWeatherMap)',
            //'fallback_realtime' => 'Prediksi (Fallback Realtime)',
            'prediction_not_available' => 'Prediksi Belum Tersedia',
            default => 'Data Prediksi',
        };
    }

    private function numberValue($value): float
    {
        return round((float) ($value ?? 0), 2);
    }
}
