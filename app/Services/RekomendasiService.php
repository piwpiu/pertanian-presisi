<?php

namespace App\Services;

use App\Models\Klimatologi;
use App\Models\Prediksi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RekomendasiService
{
    private const HORIZON_DAYS = 120;

    private const SUHU_TRANSISI_MIN = 19;
    private const SUHU_OPTIMAL_MIN = 22;
    private const SUHU_OPTIMAL_MAX = 27;
    private const SUHU_TRANSISI_MAX = 30;

    private const KELEMBABAN_TRANSISI_MIN = 60;
    private const KELEMBABAN_OPTIMAL_MIN = 63;
    private const KELEMBABAN_OPTIMAL_MAX = 80;
    private const KELEMBABAN_TRANSISI_MAX = 83;

    private const HUJAN_120_TRANSISI_MIN = 480; //480 (4mm/hari menjadi 480 per 120hari) || 493 (1500/tahun menjadi 493 per 120hari)
    private const HUJAN_120_OPTIMAL_MIN = 600; //600
    private const HUJAN_120_OPTIMAL_MAX = 800; //800 || 657 (2000/tahun menjadi 657 per 120hari)
    private const HUJAN_120_TRANSISI_MAX = 960; //960 (8mm/hari menjadi 960 per 120hari)

    private const BATAS_HUJAN_LEBAT = 50;
    private const BATAS_HARI_KERING_BERTURUT = 5;

    public function hitung(?string $tanggalAcuan = null): array
    {
        $tanggalMulai = $tanggalAcuan
            ? Carbon::parse($tanggalAcuan)->startOfDay()
            : now()->startOfDay();

        $tanggalSelesai = $tanggalMulai->copy()->addDays(self::HORIZON_DAYS - 1);

        $hasil = $this->ambilDataPeriode($tanggalMulai, self::HORIZON_DAYS);

        $dataAnalisis = $hasil['data'];
        $jumlahAktual = $hasil['jumlah_aktual'];
        $jumlahPrediksi = $hasil['jumlah_prediksi'];
        $jumlahTidakTersedia = $hasil['jumlah_tidak_tersedia'];

        $dataTersedia = collect($dataAnalisis)->filter(function ($item) {
            return $item['suhu'] !== null
                && $item['kelembaban'] !== null
                && $item['curah_hujan'] !== null;
        });

        if ($dataTersedia->count() < self::HORIZON_DAYS) {
            return [
                'valid' => false,
                'status' => 'Data Belum Lengkap',
                'periode' => $tanggalMulai->format('d M Y') . ' – ' . $tanggalSelesai->format('d M Y'),
                'rata_suhu' => null,
                'rata_kelembaban' => null,
                'total_curah_hujan' => null,
                'jumlah_hari_hujan' => null,
                'jumlah_hari_hujan_lebat' => null,
                'hari_kering_terpanjang' => null,
                'jumlah_aktual' => $jumlahAktual,
                'jumlah_prediksi' => $jumlahPrediksi,
                'jumlah_tidak_tersedia' => $jumlahTidakTersedia,
                'skor' => null,
                'skor_fuzzy' => null,
                'derajat_suhu' => null,
                'derajat_kelembaban' => null,
                'derajat_curah_hujan' => null,
                'tingkat_kesesuaian' => null,
                'alasan_fuzzy' => null,
                'alasan' => 'Data iklim selama 120 hari belum lengkap. Rekomendasi belum dapat dihitung dengan akurat.',
                'saran' => 'Silakan lakukan generate prediksi melalui halaman admin atau lengkapi data klimatologi terlebih dahulu.',
                'detail_harian' => $dataAnalisis,
                'risiko_iklim' => [],
                'kebutuhan_air' => $this->hitungKebutuhanAir(null, false),
            ];
        }

        $rataSuhu = round($dataTersedia->avg('suhu'), 2);
        $rataKelembaban = round($dataTersedia->avg('kelembaban'), 2);
        $totalCurahHujan = round($dataTersedia->sum('curah_hujan'), 2);

        $jumlahHariHujan = $dataTersedia->filter(fn ($item) => (float) $item['curah_hujan'] > 0)->count();
        $hariHujanLebat = $dataTersedia->filter(fn ($item) => (float) $item['curah_hujan'] > self::BATAS_HUJAN_LEBAT)->values();

        $periodeKering = $this->deteksiPeriodeKering($dataTersedia->values()->all());
        $risikoIklim = $this->buatRisikoIklim($hariHujanLebat, $periodeKering);

        $derajatSuhu = $this->fuzzySuhu($rataSuhu);
        $derajatKelembaban = $this->fuzzyKelembaban($rataKelembaban);
        $derajatCurahHujan = $this->fuzzyCurahHujan($totalCurahHujan);

        $skorFuzzy = $this->hitungSkorFuzzyTanam(
            $rataSuhu,
            $rataKelembaban,
            $totalCurahHujan,
            $hariHujanLebat->count(),
            $periodeKering['durasi']
        );
        $status = $this->statusTanamDariSkor($skorFuzzy);
        $tingkatKesesuaian = $this->tingkatKesesuaianDariSkor($skorFuzzy);
        $alasan = $this->buatAlasanAnalisis($derajatSuhu, $derajatKelembaban, $derajatCurahHujan, $rataSuhu, $rataKelembaban, $totalCurahHujan);
        $saran = $this->buatSaranTanamFuzzy($skorFuzzy, $derajatCurahHujan, $hariHujanLebat->count(), $periodeKering['durasi'], count($risikoIklim));

        return [
            'valid' => true,
            'status' => $status,
            'periode' => $tanggalMulai->format('d M Y') . ' – ' . $tanggalSelesai->format('d M Y'),
            'rata_suhu' => $rataSuhu,
            'rata_kelembaban' => $rataKelembaban,
            'total_curah_hujan' => $totalCurahHujan,
            'jumlah_hari_hujan' => $jumlahHariHujan,
            'jumlah_hari_hujan_lebat' => $hariHujanLebat->count(),
            'hari_kering_terpanjang' => $periodeKering['durasi'],
            'jumlah_aktual' => $jumlahAktual,
            'jumlah_prediksi' => $jumlahPrediksi,
            'jumlah_tidak_tersedia' => $jumlahTidakTersedia,
            'skor' => $skorFuzzy,
            'skor_fuzzy' => $skorFuzzy,
            'derajat_suhu' => $derajatSuhu,
            'derajat_kelembaban' => $derajatKelembaban,
            'derajat_curah_hujan' => $derajatCurahHujan,
            'tingkat_kesesuaian' => $tingkatKesesuaian,
            'alasan_fuzzy' => $alasan,
            'alasan' => $alasan,
            'saran' => $saran,
            'detail_harian' => $dataAnalisis,
            'risiko_iklim' => $risikoIklim,
            'kebutuhan_air' => $this->hitungKebutuhanAir($totalCurahHujan, true, $derajatCurahHujan),
        ];
    }

    public function hitungRekomendasiVarietas(?string $tanggalAcuan = null): array
    {
        $tanggalMulai = $tanggalAcuan
            ? Carbon::parse($tanggalAcuan)->startOfDay()
            : now()->startOfDay();

        $tanggalSelesai = $tanggalMulai->copy()->addDays(self::HORIZON_DAYS - 1);

        $hasil = $this->ambilDataPeriode($tanggalMulai, self::HORIZON_DAYS);

        $dataTersedia = collect($hasil['data'])->filter(function ($item) {
            return $item['curah_hujan'] !== null;
        });

        $totalCurahHujan = round($dataTersedia->sum('curah_hujan'), 2);

        if ($hasil['jumlah_tidak_tersedia'] > 0 || $dataTersedia->count() < self::HORIZON_DAYS) {
            return [
                'valid' => false,
                'kategori' => 'Belum Dapat Ditentukan',
                'kategori_tampilan' => 'Data Belum Lengkap',
                'periode' => $tanggalMulai->format('d M Y') . ' – ' . $tanggalSelesai->format('d M Y'),
                'total_curah_hujan' => $totalCurahHujan,
                'jumlah_aktual' => $hasil['jumlah_aktual'],
                'jumlah_prediksi' => $hasil['jumlah_prediksi'],
                'jumlah_tidak_tersedia' => $hasil['jumlah_tidak_tersedia'],
                'varietas_utama' => [],
                'varietas_alternatif' => [],
                'kesimpulan' => 'Data curah hujan 120 hari belum lengkap, sehingga sistem belum dapat menentukan rekomendasi varietas padi.',
                'penjelasan' => 'Lengkapi data klimatologi atau lakukan generate prediksi agar sistem dapat menghitung rekomendasi varietas padi berdasarkan periode 120 hari.',
            ];
        }

        $derajatCurahHujan = $this->fuzzyCurahHujan($totalCurahHujan);
        if ($totalCurahHujan < self::HUJAN_120_OPTIMAL_MIN) {
            $kondisiAirDominan = 'rendah';
        } elseif ($totalCurahHujan <= self::HUJAN_120_TRANSISI_MAX) {
            $kondisiAirDominan = 'optimal';
        } else {
            $kondisiAirDominan = 'tinggi';
        }

        if ($kondisiAirDominan === 'rendah') {
            return [
                'valid' => true,
                'kategori' => 'Varietas Tahan Kekeringan',
                'kategori_tampilan' => 'Varietas Tahan Kekeringan',
                'periode' => $tanggalMulai->format('d M Y') . ' – ' . $tanggalSelesai->format('d M Y'),
                'total_curah_hujan' => $totalCurahHujan,
                'jumlah_aktual' => $hasil['jumlah_aktual'],
                'jumlah_prediksi' => $hasil['jumlah_prediksi'],
                'jumlah_tidak_tersedia' => $hasil['jumlah_tidak_tersedia'],
                'varietas_utama' => [
                    'Inpago 8',
                    'Inpago 9',
                    'Situ Bagendit',
                ],
                'varietas_alternatif' => [
                    'Inpari 38 Tadah Hujan Agritan',
                    'Inpari 39 Tadah Hujan Agritan',
                    'Inpari 40 Tadah Hujan Agritan',
                    'Inpari 41 Tadah Hujan Agritan',
                    'Inpago 4',
                    'Inpago 5',
                    'Inpago 6',
                    'Inpago 7',
                    'Inpago 10',
                    'Inpago 11 Agritan',
                    'Inpago 12 Agritan',
                    'Inpago Lipigo 4',
                    'Rindang 1 Agritan',
                    'Rindang 2 Agritan',
                    'Luhur 1',
                    'Luhur 2',
                    'Buyung',
                    'Inpari 39',
                ],
                'kesimpulan' => 'Curah hujan 120 hari berada di bawah kebutuhan air untuk tanaman padi, sehingga varietas tahan kekeringan lebih disarankan untuk mengurangi risiko kekurangan air.',
                'penjelasan' => 'Total curah hujan selama 120 hari berada di bawah batas minimum kecukupan air tanaman padi, yaitu 600 mm per 120 hari. Kondisi ini menunjukkan potensi kekurangan air sehingga varietas tahan kekeringan atau varietas yang lebih toleran terhadap kondisi kering lebih sesuai untuk digunakan.',
                'derajat_curah_hujan' => $derajatCurahHujan,
            ];
        }

        if ($kondisiAirDominan === 'optimal') {
            return [
                'valid' => true,
                'kategori' => 'Varietas Umum',
                'kategori_tampilan' => 'Varietas Umum',
                'periode' => $tanggalMulai->format('d M Y') . ' – ' . $tanggalSelesai->format('d M Y'),
                'total_curah_hujan' => $totalCurahHujan,
                'jumlah_aktual' => $hasil['jumlah_aktual'],
                'jumlah_prediksi' => $hasil['jumlah_prediksi'],
                'jumlah_tidak_tersedia' => $hasil['jumlah_tidak_tersedia'],
                'varietas_utama' => [
                    'Inpari 32',
                    'Inpari 48',
                    'IR 64',
                ],
                'varietas_alternatif' => [
                    'Ciherang',
                    'Situ Bagendit',
                    'Inpara 3',
                    'Inpara 4',
                    'Inpari 39 Tadah Hujan Agritan',
                    'Inpari 43 Agritan GSR',
                    'Inpari 47 WBC',
                    'Inpari IR Nutri Zinc',
                    'Cakrabuana Agritan',
                    'Pamelen',
                    'Baroma',
                    'Mekongga',
                    'Siliwangi Agritan',
                    'Munawacita Agritan',
                    'Mustaban Agritan',
                ],
                'kesimpulan' => 'Curah hujan 120 hari berada pada rentang kebutuhan air untuk tanaman padi, sehingga varietas untuk kondisi air cukup dapat dipertimbangkan untuk periode ini.',
                'penjelasan' => 'Total curah hujan selama 120 hari berada dalam batas kecukupan air untuk tanaman padi, yaitu 600 – 960 mm per 120 hari. Jika curah hujan berada pada 600 – 800 mm, kondisi tergolong optimal. Jika berada di atas 800 mm hingga 960 mm, air masih tercukupi tetapi pemantauan drainase tetap perlu diperhatikan.',
                'derajat_curah_hujan' => $derajatCurahHujan,
            ];
        }

        return [
            'valid' => true,
            'kategori' => 'Varietas Toleran Genangan',
            'kategori_tampilan' => 'Varietas Toleran Genangan',
            'periode' => $tanggalMulai->format('d M Y') . ' – ' . $tanggalSelesai->format('d M Y'),
            'total_curah_hujan' => $totalCurahHujan,
            'jumlah_aktual' => $hasil['jumlah_aktual'],
            'jumlah_prediksi' => $hasil['jumlah_prediksi'],
            'jumlah_tidak_tersedia' => $hasil['jumlah_tidak_tersedia'],
            'varietas_utama' => [
                'Inpari 30 Ciherang Sub 1',
                'Inpari 29 Rendaman',
                'Inpara 5',
            ],
            'varietas_alternatif' => [
                'Inpara 3',
                'Inpara 4',
                'Ciherang',
            ],
            'kesimpulan' => 'Curah hujan 120 hari melebihi kebutuhan air untuk tanaman padi, sehingga varietas tahan genangan atau banjir lebih disarankan untuk mengurangi risiko genangan.',
            'penjelasan' => 'Total curah hujan selama 120 hari melebihi batas kecukupan air tanaman padi, yaitu 960 mm per 120 hari. Kondisi ini menunjukkan potensi kelebihan air, genangan, atau banjir sehingga varietas yang lebih toleran terhadap rendaman lebih sesuai untuk digunakan.',
            'derajat_curah_hujan' => $derajatCurahHujan,
        ];
    }

    private function ambilDataPeriode(Carbon $tanggalMulai, int $jumlahHari): array
    {
        $tanggalSelesai = $tanggalMulai->copy()->addDays($jumlahHari - 1);
        $periode = CarbonPeriod::create($tanggalMulai, $tanggalSelesai);

        $dataAnalisis = [];
        $jumlahAktual = 0;
        $jumlahPrediksi = 0;
        $jumlahTidakTersedia = 0;

        foreach ($periode as $tanggal) {
            $tanggalString = $tanggal->format('Y-m-d');

            $aktual = Klimatologi::whereDate('tanggal', $tanggalString)->first();

            if ($aktual) {
                $dataAnalisis[] = [
                    'tanggal' => $tanggalString,
                    'sumber' => 'aktual',
                    'suhu' => $aktual->tavg ?? $aktual->TAVG ?? null,
                    'kelembaban' => $aktual->rh_avg ?? $aktual->RH_AVG ?? null,
                    'curah_hujan' => $aktual->rr ?? $aktual->RR ?? null,
                ];

                $jumlahAktual++;
                continue;
            }

            $prediksi = Prediksi::whereDate('tanggal', $tanggalString)->first();

            if ($prediksi) {
                $dataAnalisis[] = [
                    'tanggal' => $tanggalString,
                    'sumber' => 'prediksi',
                    'suhu' => $prediksi->prediksi_suhu,
                    'kelembaban' => $prediksi->prediksi_kelembaban,
                    'curah_hujan' => $prediksi->prediksi_curah_hujan,
                ];

                $jumlahPrediksi++;
                continue;
            }

            $dataAnalisis[] = [
                'tanggal' => $tanggalString,
                'sumber' => 'tidak tersedia',
                'suhu' => null,
                'kelembaban' => null,
                'curah_hujan' => null,
            ];

            $jumlahTidakTersedia++;
        }

        return [
            'data' => $dataAnalisis,
            'jumlah_aktual' => $jumlahAktual,
            'jumlah_prediksi' => $jumlahPrediksi,
            'jumlah_tidak_tersedia' => $jumlahTidakTersedia,
        ];
    }

    private function deteksiPeriodeKering(array $data): array
    {
        $terpanjang = [
            'mulai' => null,
            'selesai' => null,
            'durasi' => 0,
        ];

        $sementaraMulai = null;
        $sementaraDurasi = 0;

        foreach ($data as $item) {
            $curahHujan = (float) ($item['curah_hujan'] ?? 0);

            if ($curahHujan <= 0) {
                if ($sementaraMulai === null) {
                    $sementaraMulai = $item['tanggal'];
                }

                $sementaraDurasi++;
            } else {
                if ($sementaraDurasi > $terpanjang['durasi']) {
                    $terpanjang = [
                        'mulai' => $sementaraMulai,
                        'selesai' => Carbon::parse($item['tanggal'])->subDay()->format('Y-m-d'),
                        'durasi' => $sementaraDurasi,
                    ];
                }

                $sementaraMulai = null;
                $sementaraDurasi = 0;
            }
        }

        if ($sementaraDurasi > $terpanjang['durasi']) {
            $terpanjang = [
                'mulai' => $sementaraMulai,
                'selesai' => collect($data)->last()['tanggal'] ?? null,
                'durasi' => $sementaraDurasi,
            ];
        }

        return $terpanjang;
    }

    private function buatRisikoIklim($hariHujanLebat, array $periodeKering): array
    {
        $risiko = [];

        foreach ($hariHujanLebat as $item) {
            $tanggal = Carbon::parse($item['tanggal'])->locale('id')->translatedFormat('d F Y');
            $curahHujan = round((float) $item['curah_hujan'], 2);

            $risiko[] = [
                'jenis' => 'Hujan Lebat',
                'tingkat' => 'Waspada',
                'pesan' => "Waspada hujan lebat diprediksi terjadi pada tanggal {$tanggal} dengan curah hujan sebesar {$curahHujan} mm.",
                'saran' => 'Disarankan menyiapkan saluran drainase atau pengelolaan air yang baik untuk mengurangi risiko genangan.',
            ];
        }

        if ($periodeKering['durasi'] >= self::BATAS_HARI_KERING_BERTURUT) {
            $mulai = Carbon::parse($periodeKering['mulai'])->locale('id')->translatedFormat('d F Y');
            $selesai = Carbon::parse($periodeKering['selesai'])->locale('id')->translatedFormat('d F Y');

            $risiko[] = [
                'jenis' => 'Periode Kering',
                'tingkat' => 'Waspada',
                'pesan' => "Waspada periode kering diprediksi terjadi pada tanggal {$mulai} sampai {$selesai} selama {$periodeKering['durasi']} hari berturut-turut tanpa hujan.",
                'saran' => 'Disarankan menyiapkan sumber air atau irigasi tambahan untuk menjaga kebutuhan air pada tanaman padi.',
            ];
        }

        return $risiko;
    }

    private function hitungKebutuhanAir(?float $totalCurahHujan, bool $valid = true, ?array $derajatCurahHujan = null): array
    {
        $kebutuhanMinimum = self::HUJAN_120_OPTIMAL_MIN;
        $kebutuhanMaksimum = self::HUJAN_120_TRANSISI_MAX;

        if (!$valid || $totalCurahHujan === null) {
            return [
                'valid' => false,
                'status' => 'Belum Dapat Dihitung',
                'kebutuhan_minimum' => $kebutuhanMinimum,
                'kebutuhan_maksimum' => $kebutuhanMaksimum,
                'total_curah_hujan' => null,
                'estimasi_kekurangan_air' => null,
                'kelebihan_air' => null,
                'rumus' => 'Kebutuhan air minimum - total curah hujan 120 hari',
                'kesimpulan' => 'Estimasi kebutuhan air belum dapat dihitung karena data curah hujan 120 hari belum lengkap.',
                'saran' => 'Lengkapi data klimatologi atau lakukan generate prediksi terlebih dahulu agar sistem dapat menghitung estimasi kebutuhan air.',
                'derajat_curah_hujan' => $derajatCurahHujan,
            ];
        }

        $estimasiKekuranganAir = max(0, round($kebutuhanMinimum - $totalCurahHujan, 2));
        $kelebihanAir = max(0, round($totalCurahHujan - $kebutuhanMaksimum, 2));
        $derajatCurahHujan = $derajatCurahHujan ?? $this->fuzzyCurahHujan($totalCurahHujan);

        if ($totalCurahHujan < $kebutuhanMinimum) {
            return [
                'valid' => true,
                'status' => 'Kekurangan Air',
                'kebutuhan_minimum' => $kebutuhanMinimum,
                'kebutuhan_maksimum' => $kebutuhanMaksimum,
                'total_curah_hujan' => $totalCurahHujan,
                'estimasi_kekurangan_air' => $estimasiKekuranganAir,
                'kelebihan_air' => 0,
                'rumus' => "{$kebutuhanMinimum} - {$totalCurahHujan} = {$estimasiKekuranganAir} mm",
                'kesimpulan' => "Total curah hujan 120 hari masih berada di bawah kebutuhan minimum air untuk tanaman padi. Diperkirakan terdapat kekurangan air sekitar {$estimasiKekuranganAir} mm selama periode yang dipilih.",
                'saran' => 'Siapkan sumber air atau irigasi tambahan untuk membantu menjaga ketersediaan air pada periode yang dipilih.',
                'derajat_curah_hujan' => $derajatCurahHujan,
            ];
        }

        if ($totalCurahHujan <= $kebutuhanMaksimum) {
            $kesimpulan = $totalCurahHujan <= self::HUJAN_120_OPTIMAL_MAX
                ? 'Total curah hujan 120 hari berada pada rentang optimal kebutuhan air tanaman padi, sehingga kebutuhan air pada periode ini relatif tercukupi.'
                : 'Total curah hujan 120 hari masih berada dalam batas ketersediaan air yang dapat mencukupi kebutuhan tanaman padi. Namun, karena nilainya telah melewati rentang optimal, pemantauan drainase tetap perlu dilakukan.';

            $saran = $totalCurahHujan <= self::HUJAN_120_OPTIMAL_MAX
                ? 'Tetap lakukan pemantauan kondisi lahan dan ketersediaan air secara berkala.'
                : 'Kebutuhan air masih tercukupi, tetapi pastikan saluran drainase tetap berfungsi baik untuk mengantisipasi peningkatan curah hujan.';

            return [
                'valid' => true,
                'status' => 'Air Tercukupi',
                'kebutuhan_minimum' => $kebutuhanMinimum,
                'kebutuhan_maksimum' => $kebutuhanMaksimum,
                'total_curah_hujan' => $totalCurahHujan,
                'estimasi_kekurangan_air' => 0,
                'kelebihan_air' => 0,
                'rumus' => "Curah hujan masih berada pada batas kecukupan air {$kebutuhanMinimum} – {$kebutuhanMaksimum} mm/120 hari",
                'kesimpulan' => $kesimpulan,
                'saran' => $saran,
                'derajat_curah_hujan' => $derajatCurahHujan,
            ];
        }

        return [
            'valid' => true,
            'status' => 'Potensi Air Berlebih',
            'kebutuhan_minimum' => $kebutuhanMinimum,
            'kebutuhan_maksimum' => $kebutuhanMaksimum,
            'total_curah_hujan' => $totalCurahHujan,
            'estimasi_kekurangan_air' => 0,
            'kelebihan_air' => $kelebihanAir,
            'rumus' => "{$totalCurahHujan} - {$kebutuhanMaksimum} = {$kelebihanAir} mm",
            'kesimpulan' => "Total curah hujan 120 hari melebihi batas kecukupan air untuk tanaman padi. Terdapat potensi kelebihan air sekitar {$kelebihanAir} mm.",
            'saran' => 'Pastikan saluran air atau drainase berfungsi dengan baik untuk mengurangi risiko genangan pada lahan.',
            'derajat_curah_hujan' => $derajatCurahHujan,
        ];
    }

    private function buatAlasanAnalisis(
        array $derajatSuhu,
        array $derajatKelembaban,
        array $derajatCurahHujan,
        float $rataSuhu,
        float $rataKelembaban,
        float $totalCurahHujan
    ): string {
        $alasan = [];

        $suhuMendukung = $rataSuhu >= 22 && $rataSuhu <= 30;
        $kelembabanMendukung = $rataKelembaban >= 63 && $rataKelembaban <= 83;
        $curahHujanMendukung = $totalCurahHujan >= 600 && $totalCurahHujan <= 800;

        $alasan[] = $suhuMendukung
            ? "Rata-rata suhu {$rataSuhu}°C berada pada rentang mendukung 22 – 30°C."
            : "Rata-rata suhu {$rataSuhu}°C berada di luar rentang mendukung 22 – 30°C.";

        $alasan[] = $kelembabanMendukung
            ? "Rata-rata kelembaban {$rataKelembaban}% berada pada rentang mendukung 63 – 83%."
            : "Rata-rata kelembaban {$rataKelembaban}% berada di luar rentang mendukung 63 – 83%.";

        $alasan[] = $curahHujanMendukung
            ? "Total curah hujan {$totalCurahHujan} mm selama 120 hari berada pada rentang mendukung 600 – 800 mm."
            : "Total curah hujan {$totalCurahHujan} mm selama 120 hari berada di luar rentang mendukung 600 – 800 mm.";

        return implode(' ', $alasan);
    }

    private function naik(float $x, float $a, float $b): float
    {
        if ($x <= $a) {
            return 0.0;
        }

        if ($x >= $b) {
            return 1.0;
        }

        return round(($x - $a) / ($b - $a), 4);
    }

    private function turun(float $x, float $a, float $b): float
    {
        if ($x <= $a) {
            return 1.0;
        }

        if ($x >= $b) {
            return 0.0;
        }

        return round(($b - $x) / ($b - $a), 4);
    }

    private function trapesium(float $x, float $a, float $b, float $c, float $d): float
    {
        if ($x <= $a || $x >= $d) {
            return 0.0;
        }

        if ($x >= $b && $x <= $c) {
            return 1.0;
        }

        if ($x > $a && $x < $b) {
            return round(($x - $a) / ($b - $a), 4);
        }

        return round(($d - $x) / ($d - $c), 4);
    }

    private function segitiga(float $x, float $a, float $b, float $c): float
    {
        if ($x <= $a || $x >= $c) {
            return 0.0;
        }

        if ($x === $b) {
            return 1.0;
        }

        if ($x < $b) {
            return round(($x - $a) / ($b - $a), 4);
        }

        return round(($c - $x) / ($c - $b), 4);
    }

    private function fuzzySuhu(float $x): array
    {
        return [
            'rendah' => $this->turun($x, self::SUHU_TRANSISI_MIN, self::SUHU_OPTIMAL_MIN),
            'optimal' => $this->trapesium($x, self::SUHU_TRANSISI_MIN, self::SUHU_OPTIMAL_MIN, self::SUHU_OPTIMAL_MAX, self::SUHU_TRANSISI_MAX),
            'tinggi' => $this->naik($x, self::SUHU_OPTIMAL_MAX, self::SUHU_TRANSISI_MAX),
        ];
    }

    private function fuzzyKelembaban(float $x): array
    {
        return [
            'rendah' => $this->turun($x, self::KELEMBABAN_TRANSISI_MIN, self::KELEMBABAN_OPTIMAL_MIN),
            'optimal' => $this->trapesium($x, self::KELEMBABAN_TRANSISI_MIN, self::KELEMBABAN_OPTIMAL_MIN, self::KELEMBABAN_OPTIMAL_MAX, self::KELEMBABAN_TRANSISI_MAX),
            'tinggi' => $this->naik($x, self::KELEMBABAN_OPTIMAL_MAX, self::KELEMBABAN_TRANSISI_MAX),
        ];
    }

    private function fuzzyCurahHujan(float $x): array
    {
        return [
            'rendah' => $this->turun($x, self::HUJAN_120_TRANSISI_MIN, self::HUJAN_120_OPTIMAL_MIN),
            'optimal' => $this->trapesium($x, self::HUJAN_120_TRANSISI_MIN, self::HUJAN_120_OPTIMAL_MIN, self::HUJAN_120_OPTIMAL_MAX, self::HUJAN_120_TRANSISI_MAX),
            'tinggi' => $this->naik($x, self::HUJAN_120_OPTIMAL_MAX, self::HUJAN_120_TRANSISI_MAX),
        ];
    }

    private function hitungSkorFuzzyTanam(
        float $rataSuhu,
        float $rataKelembaban,
        float $totalCurahHujan,
        int $jumlahHariHujanLebat,
        int $hariKeringTerpanjang
    ): int {
        $skorSuhu = $this->skorParameterSugeno($this->fuzzySuhu($rataSuhu), 25, 25);
        $skorKelembaban = $this->skorParameterSugeno($this->fuzzyKelembaban($rataKelembaban), 25, 25);
        $skorCurahHujan = $this->skorParameterSugeno($this->fuzzyCurahHujan($totalCurahHujan), 25, 25);

        $penaltiHujanLebat = min(25, $jumlahHariHujanLebat * 5);
        $penaltiKering = $hariKeringTerpanjang >= self::BATAS_HARI_KERING_BERTURUT
            ? min(30, 5 + (($hariKeringTerpanjang - self::BATAS_HARI_KERING_BERTURUT) * 3))
            : 0;
        $skorRisiko = max(0, 100 - $penaltiHujanLebat - $penaltiKering);

        return (int) round(($skorSuhu * 0.25) + ($skorKelembaban * 0.25) + ($skorCurahHujan * 0.40) + ($skorRisiko * 0.10));
    }

    private function skorParameterSugeno(array $derajat, int $skorRendah = 25, int $skorTinggi = 25): float
    {
        $pembilang = ($derajat['rendah'] * $skorRendah) + ($derajat['optimal'] * 100) + ($derajat['tinggi'] * $skorTinggi);
        $penyebut = array_sum($derajat);

        if ($penyebut <= 0) {
            return min($skorRendah, $skorTinggi);
        }

        return $pembilang / $penyebut;
    }

    private function statusTanamDariSkor(int $skor): string
    {
        if ($skor >= 80) {
            return 'Direkomendasikan';
        }

        if ($skor >= 50) {
            return 'Direkomendasikan dengan Waspada';
        }

        return 'Tidak Direkomendasikan';
    }

    private function tingkatKesesuaianDariSkor(int $skor): string
    {
        if ($skor >= 80) {
            return 'tingkat kesesuaian tinggi';
        }

        if ($skor >= 50) {
            return 'tingkat kesesuaian sedang';
        }

        return 'tingkat kesesuaian rendah';
    }

    private function kondisiAirDominan(array $derajatCurahHujan): string
    {
        arsort($derajatCurahHujan);

        return array_key_first($derajatCurahHujan) ?: 'optimal';
    }

    private function frasaDerajatFuzzy(array $derajat, string $konteks = 'iklim'): string
    {
        $dominan = $this->kondisiAirDominan($derajat);
        $optimal = round(($derajat['optimal'] ?? 0) * 100);
        $nilaiDominan = round(($derajat[$dominan] ?? 0) * 100);

        if ($dominan === 'optimal' && $nilaiDominan >= 80) {
            return 'tingkat kesesuaian tinggi';
        }

        if ($optimal >= 40) {
            return 'tingkat kesesuaian sedang dan masih mendekati kondisi optimal';
        }

        if ($konteks === 'air') {
            return "tingkat kesesuaian rendah dengan kondisi air dominan {$dominan}, sehingga perlu pengelolaan irigasi/drainase";
        }

        return "tingkat kesesuaian rendah dengan kecenderungan {$dominan}";
    }

    private function buatSaranTanamFuzzy(
        int $skorFuzzy,
        array $derajatCurahHujan,
        int $jumlahHariHujanLebat,
        int $hariKeringTerpanjang,
        int $jumlahRisikoIklim
    ): string {
        if ($skorFuzzy >= 80) {
            $saran = 'Kondisi iklim 120 hari mendukung untuk memulai tanam padi. Petani tetap disarankan melakukan pemantauan rutin terhadap kondisi lahan.';

            if ($jumlahRisikoIklim > 0) {
                $saran = 'Kondisi iklim 120 hari secara umum mendukung untuk memulai tanam padi. Namun, tetap perlu memperhatikan peringatan risiko iklim yang terdeteksi selama periode analisis.';
            }
        } elseif ($skorFuzzy >= 50) {
            $saran = 'Tanam masih dapat dipertimbangkan, tetapi terdapat parameter iklim yang belum berada pada rentang mendukung sehingga perlu dilakukan antisipasi.';
        } else {
            $saran = 'Kondisi iklim 120 hari belum mendukung. Sebaiknya menunda waktu tanam atau menunggu kondisi iklim yang lebih sesuai.';
        }

        return $saran;
    }
}
