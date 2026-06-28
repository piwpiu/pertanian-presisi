<?php

namespace Database\Seeders;

use App\Models\Klimatologi;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KlimatologiSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('lstm-api/data/Data_Kota_Bogor_clean.xlsx');

        if (!file_exists($path)) {
            $this->command->error("File not found: $path");
            return;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            $this->command->error('File tidak berisi data.');
            return;
        }

        $headers = [];
        foreach ($rows[1] as $column => $value) {
            $normalized = strtolower(trim((string) $value));
            $normalized = preg_replace('/\s+/', '_', $normalized);
            $normalized = str_replace(['-', '/', '\\'], '_', $normalized);
            $headers[$column] = $normalized;
        }

        $requiredDateColumns = ['tanggal', 'tgl', 'date', 'tanggal_tanam', 'tanggal_input'];
        $dateHeader = null;
        foreach ($headers as $column => $normalized) {
            if (in_array($normalized, $requiredDateColumns, true)) {
                $dateHeader = $column;
                break;
            }
        }

        if ($dateHeader === null) {
            $this->command->error("Kolom tanggal tidak ditemukan di file Excel.");
            return;
        }

        $aliasKeys = [
            'TN' => ['tn'],
            'TX' => ['tx'],
            'TAVG' => ['tavg', 'suhu', 'temperature', 'avg_temp'],
            'RH_AVG' => ['rh_avg', 'rh', 'kelembaban', 'humidity'],
            'RR' => ['rr', 'curah_hujan', 'rain', 'rainfall'],
            'SS' => ['ss', 'radiasi', 'rad', 'radiation'],
        ];

        $imported = 0;

        foreach (array_slice($rows, 1) as $row) {
            $tanggalCell = $row[$dateHeader] ?? null;
            if (empty($tanggalCell)) {
                continue;
            }

            try {
                $tanggal = is_numeric($tanggalCell)
                    ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalCell))->format('Y-m-d')
                    : Carbon::createFromFormat('d-m-Y', trim((string) $tanggalCell))->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    $tanggal = Carbon::parse($tanggalCell)->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }

            $record = [
                'tanggal' => $tanggal,
                'data_json' => [],
                'TN' => null,
                'TX' => null,
                'TAVG' => null,
                'RH_AVG' => null,
                'RR' => null,
                'SS' => null,
            ];

            foreach ($headers as $column => $normalized) {
                $record['data_json'][$normalized] = $row[$column] ?? null;
            }

            foreach ($aliasKeys as $field => $names) {
                foreach ($headers as $column => $normalized) {
                    if (in_array($normalized, $names, true) && isset($row[$column])) {
                        $value = $row[$column];
                        $record[$field] = is_numeric($value) ? (float) $value : null;
                        break;
                    }
                }
            }

            Klimatologi::updateOrCreate(
                ['tanggal' => $tanggal],
                $record
            );

            $imported++;
        }

        $this->command->info("Berhasil mengimpor $imported baris ke tabel klimatologi.");
    }
}
