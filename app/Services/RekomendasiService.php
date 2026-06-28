<?php

namespace App\Services;

use App\Models\Klimatologi;
use App\Models\Prediksi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RekomendasiService
{
    private const HORIZON_DAYS = 30;

    private const SUHU_MIN = 24;
    private const SUHU_MAX = 32;

    private const KELEMBABAN_MIN = 60;
    private const KELEMBABAN_MAX = 90;

    private const HUJAN_30_MIN = 150;
    private const HUJAN_30_MAX = 200;

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
                'alasan' => 'Data iklim selama 30 hari belum lengkap. Rekomendasi belum dapat dihitung dengan akurat.',
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

        $suhuMendukung = $rataSuhu >= self::SUHU_MIN && $rataSuhu <= self::SUHU_MAX;
        $kelembabanMendukung = $rataKelembaban >= self::KELEMBABAN_MIN && $rataKelembaban <= self::KELEMBABAN_MAX;
        $curahHujanMendukung = $totalCurahHujan >= self::HUJAN_30_MIN && $totalCurahHujan <= self::HUJAN_30_MAX;

        $skor = 0;

        if ($suhuMendukung) {
            $skor++;
        }

        if ($kelembabanMendukung) {
            $skor++;
        }

        if ($curahHujanMendukung) {
            $skor++;
        }

        if ($skor === 3) {
            $status = 'Direkomendasikan';

            if (count($risikoIklim) > 0) {
                $saran = 'Kondisi iklim 30 hari secara umum mendukung untuk memulai tanam padi. Namun, tetap perlu memperhatikan peringatan risiko iklim yang terdeteksi selama periode analisis.';
            } else {
                $saran = 'Kondisi iklim 30 hari mendukung untuk memulai tanam padi. Petani tetap disarankan melakukan pemantauan rutin terhadap kondisi lahan.';
            }
        } elseif ($skor === 2) {
            $status = 'Direkomendasikan dengan Waspada';
            $saran = 'Tanam masih dapat dipertimbangkan, tetapi terdapat parameter iklim yang belum berada pada rentang mendukung sehingga perlu dilakukan antisipasi.';
        } else {
            $status = 'Tidak Direkomendasikan';
            $saran = 'Kondisi iklim 30 hari belum mendukung. Sebaiknya menunda waktu tanam atau menunggu kondisi iklim yang lebih sesuai.';
        }

        $alasan = $this->buatAlasan30Hari(
            $suhuMendukung,
            $kelembabanMendukung,
            $curahHujanMendukung,
            $rataSuhu,
            $rataKelembaban,
            $totalCurahHujan
        );

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
            'skor' => $skor,
            'alasan' => $alasan,
            'saran' => $saran,
            'detail_harian' => $dataAnalisis,
            'risiko_iklim' => $risikoIklim,
            'kebutuhan_air' => $this->hitungKebutuhanAir($totalCurahHujan, true),
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
                'kesimpulan' => 'Data curah hujan 30 hari belum lengkap, sehingga sistem belum dapat menentukan rekomendasi varietas padi.',
                'penjelasan' => 'Lengkapi data klimatologi atau lakukan generate prediksi agar sistem dapat menghitung rekomendasi varietas padi berdasarkan periode 30 hari.',
            ];
        }

        if ($totalCurahHujan < self::HUJAN_30_MIN) {
            return [
                'valid' => true,
                'kategori' => 'Potensi Kekeringan',
                'kategori_tampilan' => 'Kondisi Air Rendah',
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
                'kesimpulan' => 'Curah hujan 30 hari berada di bawah kebutuhan air padi, sehingga varietas tadah hujan lebih disarankan untuk mengurangi risiko kekurangan air.',
                'penjelasan' => 'Total curah hujan selama 30 hari berada di bawah kebutuhan air yang dianjurkan, yaitu 150–200 mm per 30 hari. Kondisi ini menunjukkan potensi kekurangan air sehingga varietas tadah hujan atau varietas yang lebih toleran terhadap kondisi kering lebih sesuai digunakan.',
            ];
        }

        if ($totalCurahHujan <= self::HUJAN_30_MAX) {
            return [
                'valid' => true,
                'kategori' => 'Kondisi Air Cukup',
                'kategori_tampilan' => 'Kondisi Air Cukup',
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
                'kesimpulan' => 'Curah hujan 30 hari berada pada rentang kebutuhan air padi, sehingga varietas padi umum dapat dipertimbangkan untuk periode ini.',
                'penjelasan' => 'Total curah hujan selama 30 hari berada pada rentang kebutuhan air padi, yaitu 150–200 mm per 30 hari. Kondisi ini menunjukkan bahwa ketersediaan air relatif cukup untuk mendukung budidaya padi secara umum.',
            ];
        }

        return [
            'valid' => true,
            'kategori' => 'Potensi Banjir atau Genangan',
            'kategori_tampilan' => 'Kondisi Air Berlebih',
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
            'kesimpulan' => 'Curah hujan 30 hari melebihi kebutuhan air untuk tanaman padi, sehingga varietas yang toleran terhadap rendaman lebih disarankan untuk mengurangi risiko genangan.',
            'penjelasan' => 'Total curah hujan selama 30 hari melebihi kebutuhan air yang dianjurkan, yaitu 150–200 mm per 30 hari. Kondisi ini menunjukkan potensi kelebihan air, genangan, atau banjir sehingga varietas yang lebih toleran terhadap rendaman lebih sesuai digunakan.',
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
                'saran' => 'Disarankan menyiapkan sumber air atau irigasi tambahan untuk menjaga kebutuhan air tanaman padi.',
            ];
        }

        return $risiko;
    }

    private function hitungKebutuhanAir(?float $totalCurahHujan, bool $valid = true): array
    {
        $kebutuhanMinimum = self::HUJAN_30_MIN;
        $kebutuhanMaksimum = self::HUJAN_30_MAX;

        if (!$valid || $totalCurahHujan === null) {
            return [
                'valid' => false,
                'status' => 'Belum Dapat Dihitung',
                'kebutuhan_minimum' => $kebutuhanMinimum,
                'kebutuhan_maksimum' => $kebutuhanMaksimum,
                'total_curah_hujan' => null,
                'estimasi_kekurangan_air' => null,
                'kelebihan_air' => null,
                'rumus' => 'Kebutuhan air minimum - total curah hujan 30 hari',
                'kesimpulan' => 'Estimasi kebutuhan air belum dapat dihitung karena data curah hujan 30 hari belum lengkap.',
                'saran' => 'Lengkapi data klimatologi atau lakukan generate prediksi terlebih dahulu agar sistem dapat menghitung estimasi kebutuhan air.',
            ];
        }

        $estimasiKekuranganAir = max(0, round($kebutuhanMinimum - $totalCurahHujan, 2));
        $kelebihanAir = max(0, round($totalCurahHujan - $kebutuhanMaksimum, 2));

        if ($totalCurahHujan < $kebutuhanMinimum) {
            return [
                'valid' => true,
                'status' => 'Kebutuhan Air Kurang',
                'kebutuhan_minimum' => $kebutuhanMinimum,
                'kebutuhan_maksimum' => $kebutuhanMaksimum,
                'total_curah_hujan' => $totalCurahHujan,
                'estimasi_kekurangan_air' => $estimasiKekuranganAir,
                'kelebihan_air' => 0,
                'rumus' => "{$kebutuhanMinimum} - {$totalCurahHujan} = {$estimasiKekuranganAir} mm",
                'kesimpulan' => "Total curah hujan 30 hari masih berada di bawah kebutuhan minimum air untuk tanaman padi. Diperkirakan terdapat kekurangan air sekitar {$estimasiKekuranganAir} mm selama periode yang dipilih.",
                'saran' => 'Siapkan sumber air atau irigasi tambahan untuk membantu menjaga ketersediaan air pada fase awal pertumbuhan padi.',
            ];
        }

        if ($totalCurahHujan <= $kebutuhanMaksimum) {
            return [
                'valid' => true,
                'status' => 'Kebutuhan Air Tercukupi',
                'kebutuhan_minimum' => $kebutuhanMinimum,
                'kebutuhan_maksimum' => $kebutuhanMaksimum,
                'total_curah_hujan' => $totalCurahHujan,
                'estimasi_kekurangan_air' => 0,
                'kelebihan_air' => 0,
                'rumus' => 'Curah hujan berada pada rentang kebutuhan air 150–200 mm/30 hari',
                'kesimpulan' => 'Total curah hujan 30 hari berada pada rentang kebutuhan air untuk tanaman padi, sehingga kebutuhan air pada periode tanam yang dipilih relatif tercukupi.',
                'saran' => 'Tetap lakukan pemantauan kondisi lahan dan ketersediaan air secara berkala.',
            ];
        }

        return [
            'valid' => true,
            'status' => 'Kelebihan Air',
            'kebutuhan_minimum' => $kebutuhanMinimum,
            'kebutuhan_maksimum' => $kebutuhanMaksimum,
            'total_curah_hujan' => $totalCurahHujan,
            'estimasi_kekurangan_air' => 0,
            'kelebihan_air' => $kelebihanAir,
            'rumus' => "{$totalCurahHujan} - {$kebutuhanMaksimum} = {$kelebihanAir} mm",
            'kesimpulan' => "Total curah hujan 30 hari melebihi batas kebutuhan air untuk tanaman padi. Terdapat potensi kelebihan air sekitar {$kelebihanAir} mm.",
            'saran' => 'Pastikan saluran air atau drainase berfungsi dengan baik untuk mengurangi risiko genangan pada lahan.',
        ];
    }

    private function buatAlasan30Hari(
        bool $suhuMendukung,
        bool $kelembabanMendukung,
        bool $curahHujanMendukung,
        float $rataSuhu,
        float $rataKelembaban,
        float $totalCurahHujan
    ): string {
        $alasan = [];

        $alasan[] = $suhuMendukung
            ? "Rata-rata suhu {$rataSuhu}°C berada pada rentang mendukung 24–32°C."
            : "Rata-rata suhu {$rataSuhu}°C berada di luar rentang mendukung 24–32°C.";

        $alasan[] = $kelembabanMendukung
            ? "Rata-rata kelembaban {$rataKelembaban}% berada pada rentang mendukung 60–90%."
            : "Rata-rata kelembaban {$rataKelembaban}% berada di luar rentang mendukung 60–90%.";

        $alasan[] = $curahHujanMendukung
            ? "Total curah hujan {$totalCurahHujan} mm selama 30 hari berada pada rentang mendukung 150–200 mm."
            : "Total curah hujan {$totalCurahHujan} mm selama 30 hari berada di luar rentang mendukung 150–200 mm.";

        return implode(' ', $alasan);
    }
}