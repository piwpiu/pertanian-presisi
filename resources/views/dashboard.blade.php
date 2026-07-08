<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pertanian Presisi Padi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
    .no-print {}

    #downloadReport {display: none;}

    @media print {
        body {background: #ffffff !important;}

        body * {visibility: hidden !important;}

        #downloadReport,
        #downloadReport * {visibility: visible !important;}

        #downloadReport {
            display: none;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 32px;
            background: #ffffff;
            color: #0f172a;
            font-family: Arial, sans-serif;
        }

        .no-print {display: none !important;}

        #downloadReport h1 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        #downloadReport h2 {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
        }

        #downloadReport p,
        #downloadReport li {
            font-size: 13px;
            line-height: 1.6;
        }

        #downloadReport table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        #downloadReport th,
        #downloadReport td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            font-size: 12px;
            text-align: left;
            vertical-align: top;
        }

        #downloadReport th {
            background: #f1f5f9;
            font-weight: bold;
        }

        @page {
            size: A4;
            margin: 14mm;
        }
    }

    .section-anchor {
        scroll-margin-top: 6rem;
    }

    .navbar-scrolled {
        background-color: rgba(255, 255, 255, 0.96);
        box-shadow: 0 24px 60px -24px rgba(15, 23, 42, 0.18);
    }
    </style>
</head>

<body class="bg-gradient-to-br from-emerald-50 via-white to-slate-100 text-slate-900">
<!-- Main sticky navigation bar -->
<header id="navbar" class="no-print fixed top-0 left-0 w-full z-50 transition-all duration-300 border-b border-slate-200 bg-white/90 shadow-sm backdrop-blur-sm">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                <i class="fa-solid fa-cloud text-white text-lg" aria-hidden="true"></i>
            </div>

            <div class="flex flex-col leading-tight">
                <span class="text-xs uppercase tracking-[0.35em] text-slate-500">DASHBOARD</span>
                <span class="text-lg font-semibold text-slate-900 md:text-xl">Pertanian Presisi Padi</span>
            </div>
        </div>

        {{-- Desktop Navigation --}}
        <div class="hidden flex-1 items-center justify-end gap-4 lg:flex">
            <nav class="flex flex-wrap items-center gap-2">
                <a href="#info" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50 hover:text-emerald-700">Info</a>
                <a href="#grafik" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50 hover:text-emerald-700">Grafik</a>
                <a href="#rekomendasi" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50 hover:text-emerald-700">Rekomendasi</a>
                <a href="#download" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50 hover:text-emerald-700">Download</a>
            </nav>

            <form method="GET" action="{{ url('/') }}" class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 shadow-sm">
                <label for="tanggal_tanam_desktop" class="sr-only">Masukkan Tanggal Tanam</label>

                <input
                    id="tanggal_tanam_desktop"
                    type="date"
                    name="tanggal_tanam"
                    class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                    value="{{ request('tanggal_tanam') ?? '' }}"
                    onchange="this.form.submit()"
                >

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500"
                >
                    Lihat
                </button>
            </form>
        </div>

        {{-- Mobile Toggle --}}
        <button id="navToggle" aria-expanded="false" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-800 shadow-sm transition duration-300 hover:bg-slate-100 lg:hidden">
            <span class="sr-only">Toggle navigation menu</span>
            <i class="fa-solid fa-bars text-lg" aria-hidden="true"></i>
        </button>
    </div>

    <div class="h-1.5 bg-gradient-to-r from-emerald-500 via-emerald-400 to-lime-300 w-full"></div>

    {{-- Mobile Navigation Menu --}}
    <div id="mobileMenu" class="hidden border-t border-slate-200 bg-white/95 px-4 py-4 shadow-xl backdrop-blur-xl lg:hidden">
        <nav class="flex flex-col gap-3">
            <a href="#info" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50">Info</a>
            <a href="#grafik" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50">Grafik</a>
            <a href="#rekomendasi" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50">Rekomendasi</a>
            <a href="#download" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50">Download</a>
        </nav>

        <form method="GET" action="{{ url('/') }}" class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <label for="tanggal_tanam_menu" class="mb-2 block text-sm font-semibold text-slate-700">
                Pilih Tanggal Tanam
            </label>

            <input
                id="tanggal_tanam_menu"
                type="date"
                name="tanggal_tanam"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                value="{{ request('tanggal_tanam') ?? '' }}"
                onchange="this.form.submit()"
            >

            <button
                type="submit"
                class="mt-3 w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500"
            >
                Lihat
            </button>

            <p class="mt-2 text-xs text-slate-500">
                Hasil akan otomatis berubah setelah tanggal dipilih.
            </p>
        </form>
    </div>
</header>

<div class="max-w-6xl mx-auto px-4 pt-28 pb-8 lg:pt-28">
    {{-- Mobile Date Picker: tampil langsung di halaman --}}
    <div class="block lg:hidden mb-5">
        <form method="GET" action="{{ url('/') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <label for="tanggal_tanam_mobile_visible" class="mb-2 block text-sm font-semibold text-slate-700">
                Pilih Tanggal Tanam
            </label>

            <input
                id="tanggal_tanam_mobile_visible"
                type="date"
                name="tanggal_tanam"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                value="{{ request('tanggal_tanam') ?? '' }}"
                onchange="this.form.submit()"
            >

            <button
                type="submit"
                class="mt-3 w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-500"
            >
                Lihat
            </button>

            <p class="mt-2 text-xs text-slate-500">
                Hasil akan otomatis berubah setelah tanggal dipilih.
            </p>
        </form>
    </div>

    @if(request('tanggal_tanam'))
    <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl shadow">
        <p class="text-lg font-bold text-blue-900">
            {{ \Carbon\Carbon::parse(request('tanggal_tanam'))->locale('id')->translatedFormat('d F Y') }}
        </p>

        <p class="text-sm text-gray-600">
            Hari: {{ \Carbon\Carbon::parse(request('tanggal_tanam'))->locale('id')->translatedFormat('l') }}
        </p>
    </div>
    @endif

    @if($umur !== null && $umur > 0)
    <div class="mt-6 bg-white p-4 rounded-xl shadow">
        <h2 class="font-semibold mb-2">Informasi Tanaman</h2>
        <p>Umur Tanaman: <b>{{ $umur }} hari</b></p>
        <p>Fase:
            <span class="font-bold
                @if($fase == 'Vegetatif Awal') text-green-500
                @elseif($fase == 'Vegetatif Akhir') text-blue-500
                @elseif($fase == 'Generatif') text-yellow-500
                @elseif($fase == 'Pematangan') text-red-500
                @else text-slate-600
                @endif">
                {{ $fase }}
            </span>
        </p>
    </div>
    @endif

@if($data)
@php
    $sourceTextClass = match($data->source) {
        'actual' => 'text-green-600',
        'realtime' => 'text-blue-600',
        'fallback_realtime' => 'text-orange-600',
        default => 'text-yellow-600',
    };
@endphp

<div id="info" class="section-anchor mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-start"> <!-- gara gara "items-start" saya stuck disini :) -->
    <!-- Kartu Suhu dengan Accordion -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
        <div class="p-6 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-orange-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-orange-700">SUHU</h3>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ $data->suhu }} °C</p>
                </div>
                <i class="fa-solid fa-thermometer-half text-4xl text-orange-600" aria-hidden="true"></i>
            </div>
        </div>
        <button onclick="toggleAccordion(this)" class="w-full px-6 py-3 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
            <span class="text-xs text-gray-500">Sumber: <span class="{{ $sourceTextClass }} font-bold">{{ $data->source_label }}</span></span>
            <i class="fa-solid fa-chevron-down accordion-icon text-gray-400 text-lg transition-transform transform" aria-hidden="true"></i>
        </button>
        <div class="accordion-content hidden px-6 py-4 bg-gray-50 border-t border-gray-200">
            <p class="text-sm text-gray-700 leading-relaxed">
                <strong>Suhu udara</strong> adalah ukuran derajat panas atau dingin di sekitar tanaman. Suhu optimal untuk padi berkisar <strong>22 – 30°C</strong>. Suhu yang terlalu tinggi dapat meningkatkan penguapan serta menurunkan produktivitas tanaman, sementara suhu terlalu rendah dapat menghambat pertumbuhan vegetatif.
            </p>
        </div>
    </div>

    <!-- Kartu Curah Hujan dengan Accordion -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
        <div class="p-6 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-blue-700">CURAH HUJAN</h3>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $data->curah_hujan }} mm</p>
                </div>
                <i class="fa-solid fa-cloud-rain text-4xl text-blue-600" aria-hidden="true"></i>
            </div>
        </div>
        <button onclick="toggleAccordion(this)" class="w-full px-6 py-3 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
            <span class="text-xs text-gray-500">Sumber: <span class="{{ $sourceTextClass }} font-bold">{{ $data->source_label }}</span></span>
            <i class="fa-solid fa-chevron-down accordion-icon text-gray-400 text-lg transition-transform transform" aria-hidden="true"></i>
        </button>
        <div class="accordion-content hidden px-6 py-4 bg-gray-50 border-t border-gray-200">
            <p class="text-sm text-gray-700 leading-relaxed">
                <strong>Curah hujan</strong> menunjukkan jumlah air hujan yang diterima dalam satu hari. Pada tanaman padi, curah hujan sekitar <strong>4 – 8 mm/hari</strong> umumnya mendukung pertumbuhan yang optimal. Curah hujan yang terlalu rendah dapat menyebabkan kekurangan air, sedangkan curah hujan yang terlalu tinggi dapat menimbulkan genangan.
            </p>
        </div>
    </div>

    <!-- Kartu Kelembaban dengan Accordion -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
        <div class="p-6 bg-gradient-to-r from-cyan-50 to-cyan-100 border-b border-cyan-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-cyan-700">KELEMBABAN</h3>
                    <p class="text-3xl font-bold text-cyan-600 mt-2">{{ $data->kelembaban }} %</p>
                </div>
                <i class="fa-solid fa-smog text-4xl text-cyan-600" aria-hidden="true"></i>
            </div>
        </div>
        <button onclick="toggleAccordion(this)" class="w-full px-6 py-3 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
            <span class="text-xs text-gray-500">Sumber: <span class="{{ $sourceTextClass }} font-bold">{{ $data->source_label }}</span></span>
            <i class="fa-solid fa-chevron-down accordion-icon text-gray-400 text-lg transition-transform transform" aria-hidden="true"></i>
        </button>
        <div class="accordion-content hidden px-6 py-4 bg-gray-50 border-t border-gray-200">
            <p class="text-sm text-gray-700 leading-relaxed">
                <strong>Kelembaban udara</strong> adalah persentase uap air di udara. Untuk tanaman padi, kelembaban relatif sekitar <strong>63 – 83%</strong> tergolong ideal. Kelembaban yang terlalu rendah dapat menyebabkan tanaman kehilangan air lebih cepat, sedangkan kelembaban yang terlalu tinggi dapat meningkatkan risiko penyakit.
            </p>
        </div>
    </div>
    @if($data->updated_at_label)
        <p class="text-sm text-slate-600">
            Terakhir diperbarui:
            <span class="font-semibold text-slate-900">{{ $data->updated_at_label }}</span>
        </p>
    @endif
</div>

<!-- Grafik charts section -->
<div id="grafik" class="section-anchor mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="font-semibold text-lg mb-4 flex items-center gap-2 text-slate-900"><i class="fa-solid fa-chart-line text-slate-700"></i>Grafik Suhu Mingguan</h2>
        <div class="relative h-80">
            <canvas id="chartSuhu"></canvas>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600 mt-4 mb-2">
            <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Data Aktual</span>
            <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>Data Realtime</span>
            <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span>Data Prediksi</span>
        </div>
        <p class="text-xs text-gray-500">Data: {{ count($grafikData['labels'] ?? []) }} hari</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="font-semibold text-lg mb-4 flex items-center gap-2 text-slate-900"><i class="fa-solid fa-cloud-showers-heavy text-slate-700"></i>Grafik Curah Hujan Mingguan</h2>
        <div class="relative h-80">
            <canvas id="chartCurahHujan"></canvas>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600 mt-4 mb-2">
            <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Data Aktual</span>
            <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>Data Realtime</span>
            <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span>Data Prediksi</span>
        </div>
        <p class="text-xs text-gray-500">Data: {{ count($grafikData['labels'] ?? []) }} hari</p>
    </div>
</div>

<!-- Download Section -->
<div id="download" class="section-anchor mt-6 no-print">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Download Informasi
                </p>

                <h2 class="mt-1 text-xl font-bold text-slate-900">
                    Unduh Hasil Analisis
                </h2>

                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    File unduhan berisi ringkasan tanggal tanam, informasi iklim,
                    {{ ($isHistorical ?? false) ? 'evaluasi kondisi tanam, ketersediaan air, dan kesesuaian varietas padi.' : 'rekomendasi tanam, estimasi kebutuhan air, dan rekomendasi varietas padi.' }}
                </p>
            </div>

            <button
                type="button"
                onclick="downloadLaporan()"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500"
            >
                <i class="fa-solid fa-download"></i>
                Download / Cetak PDF
            </button>
        </div>
    </div>
</div>

<!-- Kondisi Iklim Optimal -->
@if(!empty($rekomendasiIklim))
@php
    $suhu = $rekomendasiIklim['rata_suhu'];
    $kelembaban = $rekomendasiIklim['rata_kelembaban'];
    $hujan = $rekomendasiIklim['total_curah_hujan'];

    $suhuOk = $suhu !== null && $suhu >= 22 && $suhu <= 30;
    $kelembabanOk = $kelembaban !== null && $kelembaban >= 63 && $kelembaban <= 83;
    $hujanOk = $hujan !== null && $hujan >= 600 && $hujan <= 800;

    $tanggalTanamLabel = \Carbon\Carbon::parse($tanggalTanam)->format('d M Y');

    $parameterWaspada = [];

    if (!$suhuOk && $suhu !== null) {
        $parameterWaspada[] = 'suhu belum berada pada rentang optimal';
    }

    if (!$kelembabanOk && $kelembaban !== null) {
        $parameterWaspada[] = 'kelembaban belum berada pada rentang optimal';
    }

    if (!$hujanOk && $hujan !== null && $hujan < 600) {
        $parameterWaspada[] = 'curah hujan yang masih rendah';
    } elseif (!$hujanOk && $hujan !== null && $hujan > 800) {
        $parameterWaspada[] = 'curah hujan yang terlalu tinggi';
    }

    if (count($parameterWaspada) > 1) {
        $lastItem = array_pop($parameterWaspada);
        $teksWaspada = implode(', ', $parameterWaspada) . ' dan ' . $lastItem;
    } elseif (count($parameterWaspada) === 1) {
        $teksWaspada = $parameterWaspada[0];
    } else {
        $teksWaspada = 'terdapat risiko iklim yang perlu diperhatikan';
    }

    $isHistorical = $isHistorical ?? false;
    $statusTanamAsli = $rekomendasiIklim['status'] ?? null;
    $judulCardTanam = $isHistorical ? 'Evaluasi Kondisi Tanam' : 'Rekomendasi Waktu Tanam';

    $statusUser = match($statusTanamAsli) {
        'Direkomendasikan' => 'Direkomendasikan',
        'Direkomendasikan dengan Waspada', 'Perlu Waspada' => 'Perlu Waspada',
        'Tidak Direkomendasikan' => 'Tidak Direkomendasikan',
        default => 'Data Belum Lengkap',
    };

    $statusTanamLabel = $isHistorical
        ? match($statusTanamAsli) {
            'Direkomendasikan' => 'Kondisi Tanam Sesuai',
            'Direkomendasikan dengan Waspada', 'Perlu Waspada' => 'Kondisi Tanam Memerlukan Perhatian',
            'Tidak Direkomendasikan' => 'Kondisi Tanam Kurang Sesuai',
            default => $statusUser,
        }
        : $statusUser;

    $statusIcon = match($statusTanamAsli) {
        'Direkomendasikan' => 'fa-circle-check text-green-600',
        'Direkomendasikan dengan Waspada', 'Perlu Waspada' => 'fa-triangle-exclamation text-yellow-600',
        'Tidak Direkomendasikan' => 'fa-circle-xmark text-red-600',
        default => 'fa-circle-info text-slate-600',
    };

    $labelDetailTanam = $isHistorical ? 'Lihat detail evaluasi kondisi tanam' : 'Lihat detail perhitungan rekomendasi';
    $labelSaranTanam = $isHistorical ? 'Catatan Evaluasi' : 'Saran';

    $kesimpulan = $isHistorical
        ? match($statusTanamAsli) {
            'Direkomendasikan' =>
                'Berdasarkan hasil analisis selama 120 hari, kondisi iklim pada periode tersebut dinilai sesuai untuk budidaya padi.',

            'Direkomendasikan dengan Waspada', 'Perlu Waspada' =>
                "Berdasarkan hasil analisis selama 120 hari, kondisi tanam pada periode tersebut masih tergolong cukup sesuai, namun terdapat faktor iklim yang perlu diperhatikan, yaitu {$teksWaspada}.",

            'Tidak Direkomendasikan' =>
                "Berdasarkan hasil analisis selama 120 hari, kondisi tanam pada periode tersebut dinilai kurang sesuai karena {$teksWaspada}.",

            default =>
                'Data iklim 120 hari belum lengkap untuk melakukan evaluasi kondisi tanam.',
        }
        : match($statusTanamAsli) {
            'Direkomendasikan' =>
                'Kondisi iklim selama 120 hari mendukung untuk memulai tanam padi.',

            'Direkomendasikan dengan Waspada', 'Perlu Waspada' =>
                "Tanam pada periode ini masih dapat dipertimbangkan, tetapi terdapat kondisi iklim yang perlu diwaspadai, yaitu {$teksWaspada}.",

            'Tidak Direkomendasikan' =>
                "Kondisi iklim selama 120 hari belum mendukung untuk memulai tanam padi karena {$teksWaspada}.",

            default =>
                'Data iklim 120 hari belum lengkap untuk menentukan rekomendasi.',
        };

    $kesimpulanDetail = $isHistorical
        ? match($statusTanamAsli) {
            'Direkomendasikan' =>
                'Berdasarkan hasil analisis selama 120 hari, suhu, kelembaban, dan curah hujan pada periode tersebut berada pada rentang yang mendukung. Kondisi ini menunjukkan kesesuaian iklim untuk budidaya padi.',

            'Direkomendasikan dengan Waspada', 'Perlu Waspada' =>
                "Berdasarkan hasil analisis selama 120 hari, sebagian kondisi iklim pada periode tersebut sudah mendukung. Namun, terdapat kondisi yang perlu diperhatikan, yaitu {$teksWaspada}.",

            'Tidak Direkomendasikan' =>
                "Berdasarkan hasil analisis selama 120 hari, kondisi iklim pada periode tersebut belum cukup mendukung karena {$teksWaspada}. Kondisi tersebut menunjukkan risiko yang lebih tinggi terhadap budidaya padi.",

            default =>
                'Data iklim selama 120 hari belum lengkap sehingga evaluasi belum dapat dihitung secara penuh.',
        }
        : match($statusTanamAsli) {
            'Direkomendasikan' =>
                'Berdasarkan hasil analisis selama 120 hari, suhu, kelembaban, dan curah hujan berada pada rentang yang mendukung. Kondisi ini dapat dijadikan pertimbangan untuk memulai tanam padi.',

            'Direkomendasikan dengan Waspada', 'Perlu Waspada' =>
                "Berdasarkan hasil analisis selama 120 hari, sebagian kondisi iklim sudah mendukung. Namun, terdapat kondisi yang perlu diperhatikan, yaitu {$teksWaspada}.",

            'Tidak Direkomendasikan' =>
                "Berdasarkan hasil analisis selama 120 hari, kondisi iklim belum cukup mendukung karena {$teksWaspada}. Kondisi tersebut dapat meningkatkan risiko gangguan pada periode pertumbuhan padi.",

            default =>
                'Data iklim selama 120 hari belum lengkap sehingga rekomendasi belum dapat dihitung secara penuh.',
        };

    $saranDetail = $isHistorical
        ? match($statusTanamAsli) {
            'Direkomendasikan' =>
                'Pada periode tersebut, kondisi iklim secara umum mendukung budidaya padi. Pemantauan kondisi lahan tetap diperlukan untuk menjaga pertumbuhan tanaman padi.',

            'Direkomendasikan dengan Waspada', 'Perlu Waspada' =>
                "Pada periode tersebut, kondisi iklim masih cukup mendukung, namun diperlukan perhatian terhadap {$teksWaspada}. Pengelolaan air, drainase, atau irigasi menjadi faktor penting pada kondisi tersebut.",

            'Tidak Direkomendasikan' =>
                "Pada periode tersebut, kondisi iklim dinilai kurang sesuai karena {$teksWaspada}. Kondisi ini menunjukkan bahwa periode tersebut memiliki risiko yang lebih tinggi untuk budidaya padi.",

            default =>
                'Evaluasi belum dapat diberikan karena data iklim 120 hari belum lengkap.',
        }
        : match($statusTanamAsli) {
            'Direkomendasikan' =>
                "Tanam pada tanggal {$tanggalTanamLabel} dapat dipertimbangkan karena kondisi iklim selama 120 hari berada pada rentang yang mendukung. Petani tetap disarankan melakukan pemantauan rutin terhadap kondisi lahan.",

            'Direkomendasikan dengan Waspada', 'Perlu Waspada' =>
                "Tanam pada tanggal {$tanggalTanamLabel} masih dapat dipertimbangkan, tetapi perlu dilakukan antisipasi terhadap {$teksWaspada}. Pastikan pengelolaan air, drainase, atau irigasi disiapkan sesuai kondisi lahan.",

            'Tidak Direkomendasikan' =>
                "Tanam pada tanggal {$tanggalTanamLabel} belum disarankan karena {$teksWaspada}. Sebaiknya menunda waktu tanam atau menunggu periode dengan kondisi iklim yang lebih sesuai.",

            default =>
                'Rekomendasi belum dapat diberikan karena data iklim 120 hari belum lengkap.',
        };

    $alasanSuhu = $suhuOk
        ? "Suhu mendukung karena rata-rata suhu {$suhu}Â°C masih berada dalam rentang 22 â€“ 30Â°C."
        : "Suhu belum mendukung karena rata-rata suhu {$suhu}Â°C berada di luar rentang 22 â€“ 30Â°C.";

    $alasanKelembaban = $kelembabanOk
        ? "Kelembaban mendukung karena rata-rata kelembaban {$kelembaban}% masih berada dalam rentang 63 â€“ 83%."
        : "Kelembaban belum mendukung karena rata-rata kelembaban {$kelembaban}% berada di luar rentang 63 â€“ 83%.";

    if ($hujanOk) {
        $alasanHujan = "Curah hujan mendukung karena total curah hujan {$hujan} mm selama 120 hari berada dalam rentang 600 â€“ 800 mm.";
    } elseif ($hujan !== null && $hujan < 600) {
        $alasanHujan = "Curah hujan belum mendukung karena total curah hujan {$hujan} mm selama 120 hari masih lebih rendah dari kebutuhan air padi 600 mm. Kondisi ini dapat menunjukkan potensi kekurangan air.";
    } elseif ($hujan !== null && $hujan > 800) {
        $alasanHujan = "Curah hujan belum mendukung karena total curah hujan {$hujan} mm selama 120 hari melebihi kebutuhan air padi 800 mm. Kondisi ini dapat meningkatkan risiko genangan.";
    } else {
        $alasanHujan = "Data curah hujan belum tersedia.";
    }
    $sumberAnalisis = [];

    if ($rekomendasiIklim['jumlah_aktual'] > 0) {
        $sumberAnalisis[] = $rekomendasiIklim['jumlah_aktual'] . ' hari data aktual';
    }

    if ($rekomendasiIklim['jumlah_prediksi'] > 0) {
        $sumberAnalisis[] = $rekomendasiIklim['jumlah_prediksi'] . ' hari data prediksi';
    }

    if ($rekomendasiIklim['jumlah_tidak_tersedia'] > 0) {
        $sumberAnalisis[] = $rekomendasiIklim['jumlah_tidak_tersedia'] . ' hari data tidak tersedia';
    }
@endphp

<div id="rekomendasi" class="section-anchor mt-6 bg-white rounded-xl shadow overflow-hidden">
    <div class="p-6">
        <div class="flex gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-slate-100">
                <i class="fa-solid {{ $statusIcon }} text-3xl"></i>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {{ $judulCardTanam }}
                </p>

                <h2 class="mt-1 text-2xl font-bold text-slate-900">
                    {{ $statusTanamLabel }}
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    Periode analisis:
                    <b>{{ $rekomendasiIklim['periode'] }}</b>
                </p>

                <p class="mt-3 text-base leading-relaxed text-slate-700">
                    {{ $kesimpulan }}
                </p>

                @if($rekomendasiIklim['skor'] !== null)
                    <p class="mt-2 text-sm font-semibold text-slate-700">
                        Skor Kelayakan: {{ $rekomendasiIklim['skor_fuzzy'] ?? $rekomendasiIklim['skor'] }}/100

                        @if(($rekomendasiIklim['skor_fuzzy'] ?? $rekomendasiIklim['skor']) >= 80 && !empty($rekomendasiIklim['risiko_iklim']))
                            Namun terdapat {{ count($rekomendasiIklim['risiko_iklim']) }} risiko iklim yang perlu diwaspadai.
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>

    <button onclick="toggleAccordion(this)" class="w-full border-t border-slate-200 px-6 py-3 flex items-center justify-between text-left hover:bg-slate-50 transition">
        <span class="text-sm font-semibold text-slate-700">
            {{ $labelDetailTanam }}
        </span>
        <i class="fa-solid fa-chevron-down accordion-icon text-slate-400 transition-transform transform"></i>
    </button>

    <div class="accordion-content hidden border-t border-slate-200 bg-slate-50 p-6">
        @if(!$rekomendasiIklim['valid'])
            <div class="mb-4 rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                <p class="font-semibold">Data iklim 120 hari belum lengkap.</p>
                <p>{{ $rekomendasiIklim['alasan'] }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="rounded-xl border p-4 {{ $suhuOk ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                <p class="font-semibold {{ $suhuOk ? 'text-green-700' : 'text-red-700' }}">
                    {{ $suhuOk ? '✓ Suhu mendukung' : '✗ Suhu belum mendukung' }}
                </p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                    {{ $suhu !== null ? $suhu.' °C' : '-' }}
                </p>
                <p class="text-xs text-slate-600">Rentang optimal: 22 – 30°C</p>
            </div>

            <div class="rounded-xl border p-4 {{ $kelembabanOk ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                <p class="font-semibold {{ $kelembabanOk ? 'text-green-700' : 'text-red-700' }}">
                    {{ $kelembabanOk ? '✓ Kelembaban mendukung' : '✗ Kelembaban belum mendukung' }}
                </p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                    {{ $kelembaban !== null ? $kelembaban.' %' : '-' }}
                </p>
                <p class="text-xs text-slate-600">Rentang optimal: 63 – 83%</p>
            </div>

            <div class="rounded-xl border p-4 {{ $hujanOk ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                <p class="font-semibold {{ $hujanOk ? 'text-green-700' : 'text-red-700' }}">
                    {{ $hujanOk ? '✓ Curah hujan mendukung' : '✗ Curah hujan belum mendukung' }}
                </p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                    {{ $hujan !== null ? $hujan.' mm' : '-' }}
                </p>
                <p class="text-xs text-slate-600">Rentang optimal: 600 – 800 mm/120 hari</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-semibold uppercase text-slate-500">Hari Hujan</p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                    {{ $rekomendasiIklim['jumlah_hari_hujan'] ?? '-' }} hari
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-semibold uppercase text-slate-500">Hari Hujan Lebat</p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                    {{ $rekomendasiIklim['jumlah_hari_hujan_lebat'] ?? '-' }} hari
                </p>
                <p class="text-xs text-slate-500">Acuan: &gt; 50 mm/hari</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-semibold uppercase text-slate-500">Periode Kering Terpanjang</p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                    {{ $rekomendasiIklim['hari_kering_terpanjang'] ?? '-' }} hari
                </p>
                <p class="text-xs text-slate-500">Peringatan jika ≥ 5 hari</p>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
            <p class="font-semibold text-slate-900">Kesimpulan</p>
            <p class="mt-1 text-sm leading-relaxed text-slate-700">
                {{ $kesimpulanDetail }}
            </p>

            <p class="mt-4 font-semibold text-slate-900">Alasan teknis</p>
            <ul class="mt-2 space-y-2 text-sm leading-relaxed text-slate-700">
                <li>{{ $rekomendasiIklim['alasan_fuzzy'] ?? $rekomendasiIklim['alasan'] }}</li>
            </ul>

            <p class="mt-4 font-semibold text-slate-900">{{ $labelSaranTanam }}</p>
            <p class="mt-1 text-sm leading-relaxed text-slate-700">
                {{ $saranDetail }}
            </p>

            @if(!empty($rekomendasiIklim['risiko_iklim']))
                <div class="mt-4 rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                    <p class="font-semibold text-yellow-800">
                        Peringatan Risiko Iklim
                    </p>

                    <div class="mt-3 space-y-3">
                        @foreach($rekomendasiIklim['risiko_iklim'] as $risikoItem)
                            @php
                                $pesanRisiko = $risikoItem['pesan'];
                                $saranRisiko = $risikoItem['saran'];

                                if ($isHistorical && ($risikoItem['jenis'] ?? '') === 'Hujan Lebat') {
                                    $pesanRisiko = str_replace('diprediksi terjadi', 'terjadi', $pesanRisiko);
                                    $saranRisiko = 'Pada kondisi tersebut, pengelolaan saluran drainase diperlukan untuk mengurangi risiko genangan.';
                                } elseif ($isHistorical && ($risikoItem['jenis'] ?? '') === 'Periode Kering') {
                                    $pesanRisiko = str_replace('diprediksi terjadi', 'terjadi', $pesanRisiko);
                                    $saranRisiko = 'Pada kondisi tersebut, ketersediaan sumber air atau irigasi tambahan diperlukan untuk menjaga kebutuhan air pada tanaman padi.';
                                }
                            @endphp
                            <div class="rounded-lg border border-yellow-200 bg-white p-3">
                                <p class="text-sm font-bold text-yellow-800">
                                    {{ $risikoItem['jenis'] }} – {{ $risikoItem['tingkat'] }}
                                </p>

                                <p class="mt-1 text-sm leading-relaxed text-slate-700">
                                    {{ $pesanRisiko }}
                                </p>

                                <p class="mt-1 text-sm leading-relaxed text-slate-700">
                                    {{ $saranRisiko }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(count($sumberAnalisis) > 0)
                <p class="mt-4 text-xs text-slate-500">
                    Sumber: {{ implode(', ', $sumberAnalisis) }}.
                </p>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Estimasi Kebutuhan Air -->
@if(!empty($rekomendasiIklim['kebutuhan_air']))
@php
    $isHistorical = $isHistorical ?? false;
    $air = $rekomendasiIklim['kebutuhan_air'];
    $statusAir = $air['status'] ?? 'Belum Dapat Dihitung';
    $judulAir = $isHistorical ? 'Evaluasi Ketersediaan Air' : 'Estimasi Kebutuhan Air';
    $labelDetailAir = $isHistorical ? 'Lihat detail evaluasi ketersediaan air' : 'Lihat detail estimasi kebutuhan air';

    $airIcon = match($statusAir) {
        'Kebutuhan Air Kurang', 'Kekurangan Air' => 'fa-seedling text-orange-600',
        'Kebutuhan Air Tercukupi', 'Air Tercukupi' => 'fa-circle-check text-green-600',
        'Kelebihan Air', 'Potensi Air Berlebih' => 'fa-cloud-rain text-blue-600',
        default => 'fa-circle-info text-slate-600',
    };

    $airTheme = match($statusAir) {
        'Kebutuhan Air Kurang', 'Kekurangan Air' => [
            'iconBg' => 'bg-orange-100',
            'bg' => 'bg-orange-50',
            'border' => 'border-orange-100',
            'text' => 'text-orange-700',
            'ring' => 'ring-orange-100',
        ],
        'Kebutuhan Air Tercukupi', 'Air Tercukupi' => [
            'iconBg' => 'bg-green-100',
            'bg' => 'bg-green-50',
            'border' => 'border-green-100',
            'text' => 'text-green-700',
            'ring' => 'ring-green-100',
        ],
        'Kelebihan Air', 'Potensi Air Berlebih' => [
            'iconBg' => 'bg-blue-100',
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-100',
            'text' => 'text-blue-700',
            'ring' => 'ring-blue-100',
        ],
        default => [
            'iconBg' => 'bg-slate-100',
            'bg' => 'bg-slate-50',
            'border' => 'border-slate-100',
            'text' => 'text-slate-700',
            'ring' => 'ring-slate-100',
        ],
    };

    $labelSelisihAir = match($statusAir) {
        'Kelebihan Air', 'Potensi Air Berlebih' => 'Kelebihan Air',
        'Kebutuhan Air Kurang', 'Kekurangan Air' => 'Defisit Air',
        'Kebutuhan Air Tercukupi', 'Air Tercukupi' => 'Defisit Air',
        default => 'Selisih Air',
    };

    $nilaiSelisihAir = in_array($statusAir, ['Kelebihan Air', 'Potensi Air Berlebih'], true)
        ? ($air['kelebihan_air'] ?? null)
        : ($air['estimasi_kekurangan_air'] ?? null);

    $ringkasanAir = $isHistorical
        ? match($statusAir) {
            'Kebutuhan Air Kurang', 'Kekurangan Air' => 'Curah hujan pada periode tersebut belum mencukupi kebutuhan minimum air untuk tanaman padi, sehingga ketersediaan air menjadi faktor yang perlu diperhatikan.',
            'Kebutuhan Air Tercukupi', 'Air Tercukupi' => 'Curah hujan pada periode tersebut masih berada dalam batas kecukupan air untuk tanaman padi. Jika nilainya melewati rentang optimal, pemantauan drainase tetap perlu diperhatikan.',
            'Kelebihan Air', 'Potensi Air Berlebih' => 'Curah hujan pada periode tersebut melebihi batas kecukupan air untuk tanaman padi, sehingga drainase lahan menjadi faktor penting untuk mengurangi risiko genangan.',
            default => 'Data curah hujan belum lengkap untuk mengevaluasi ketersediaan air.',
        }
        : match($statusAir) {
            'Kebutuhan Air Kurang', 'Kekurangan Air' => 'Curah hujan belum mencukupi kebutuhan minimum air untuk tanaman padi, sehingga diperlukan perhatian terhadap ketersediaan air.',
            'Kebutuhan Air Tercukupi', 'Air Tercukupi' => 'Curah hujan masih berada dalam batas kecukupan air untuk tanaman padi. Jika nilainya melewati rentang optimal, pemantauan drainase tetap perlu diperhatikan.',
            'Kelebihan Air', 'Potensi Air Berlebih' => 'Curah hujan melebihi batas kecukupan air untuk tanaman padi, sehingga perlu memperhatikan potensi genangan dan drainase lahan.',
            default => 'Data curah hujan belum lengkap untuk menghitung estimasi kebutuhan air.',
        };

    $kesimpulanAir = $isHistorical
        ? match($statusAir) {
            'Kebutuhan Air Kurang', 'Kekurangan Air' =>
                "Total curah hujan 120 hari pada periode tersebut berada di bawah kebutuhan minimum air untuk tanaman padi. Ketersediaan air perlu didukung dengan pengelolaan irigasi yang baik.",
            'Kebutuhan Air Tercukupi', 'Air Tercukupi' =>
                'Total curah hujan 120 hari pada periode tersebut berada pada rentang kebutuhan air yang optimal untuk tanaman padi, sehingga kebutuhan air dinilai relatif tercukupi.',
            'Kelebihan Air', 'Potensi Air Berlebih' =>
                "Total curah hujan 120 hari pada periode tersebut melebihi batas kebutuhan air untuk tanaman padi. Terdapat potensi kelebihan air sekitar {$nilaiSelisihAir} mm.",
            default =>
                'Evaluasi ketersediaan air belum dapat dilakukan karena data curah hujan 120 hari belum lengkap.',
        }
        : $air['kesimpulan'];

    $catatanAir = $isHistorical
        ? match($statusAir) {
            'Kebutuhan Air Kurang', 'Kekurangan Air' => 'Pada periode tersebut, ketersediaan air perlu didukung dengan pengelolaan air dan irigasi yang baik.',
            'Kebutuhan Air Tercukupi', 'Air Tercukupi' => 'Pada periode tersebut, pemantauan kondisi lahan dan ketersediaan air tetap menjadi bagian penting dalam budidaya padi.',
            'Kelebihan Air', 'Potensi Air Berlebih' => 'Pada periode tersebut, drainase lahan menjadi faktor penting untuk mengurangi risiko genangan.',
            default => 'Data klimatologi atau prediksi perlu dilengkapi agar evaluasi ketersediaan air dapat dilakukan.',
        }
        : ($air['saran'] ?? null);
@endphp

<div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow">
    <div class="p-6">
        <div class="flex gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $airTheme['iconBg'] }} ring-4 {{ $airTheme['ring'] }}">
                <i class="fa-solid {{ $airIcon }} text-3xl"></i>
            </div>

            <div class="flex-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {{ $judulAir }}
                </p>

                <h2 class="mt-1 text-2xl font-bold text-slate-900">
                    {{ $statusAir }}
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    Periode analisis:
                    <b>{{ $rekomendasiIklim['periode'] }}</b>
                </p>

                <p class="mt-3 text-base leading-relaxed text-slate-700">
                    {{ $ringkasanAir }}
                </p>

                @if($nilaiSelisihAir !== null)
                    <p class="mt-2 text-sm font-semibold {{ $airTheme['text'] }}">
                        {{ $labelSelisihAir }}: {{ $nilaiSelisihAir }} mm
                    </p>
                @endif
            </div>
        </div>
    </div>

    <button onclick="toggleAccordion(this)"
        class="w-full border-t border-slate-200 px-6 py-3 flex items-center justify-between text-left hover:bg-slate-50 transition">
        <span class="text-sm font-semibold text-slate-700">
            {{ $labelDetailAir }}
        </span>
        <i class="fa-solid fa-chevron-down accordion-icon text-slate-400 transition-transform transform"></i>
    </button>

    <div class="accordion-content hidden border-t border-slate-200 bg-slate-50 p-6">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-600">
                        <i class="fa-solid fa-water"></i>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Kebutuhan Air
                        </p>
                        <p class="mt-1 text-xl font-bold text-slate-900">
                            {{ $air['kebutuhan_minimum'] }} – {{ $air['kebutuhan_maksimum'] }} mm
                        </p>
                        <p class="text-xs text-slate-500">
                            per 120 hari
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-cloud-rain"></i>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Curah Hujan
                        </p>
                        <p class="mt-1 text-xl font-bold text-slate-900">
                            {{ $air['total_curah_hujan'] !== null ? $air['total_curah_hujan'].' mm' : '-' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            hasil analisis 120 hari
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border {{ $airTheme['border'] }} {{ $airTheme['bg'] }} p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $airTheme['iconBg'] }} {{ $airTheme['text'] }}">
                        <i class="fa-solid {{ in_array($statusAir, ['Kelebihan Air', 'Potensi Air Berlebih'], true) ? 'fa-cloud-rain' : 'fa-seedling' }}"></i>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide {{ $airTheme['text'] }}">
                            {{ $labelSelisihAir }}
                        </p>
                        <p class="mt-1 text-xl font-bold text-slate-900">
                            {{ $nilaiSelisihAir !== null ? $nilaiSelisihAir.' mm' : '-' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            hasil estimasi
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- <div class="mt-4 rounded-xl border {{ $airTheme['border'] }} {{ $airTheme['bg'] }} p-4">
            <div class="flex gap-3">
                <div class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $airTheme['iconBg'] }} {{ $airTheme['text'] }}">
                    <i class="fa-solid {{ $airIcon }}"></i>
                </div>

                <div>
                    <p class="font-semibold {{ $airTheme['text'] }}">
                        {{ $statusAir }}
                    </p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-700">
                        {{ $kesimpulanAir }}
                    </p>
                </div>
            </div>
        </div> --}}

        <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
            <div class="flex gap-3">
                <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $airTheme['iconBg'] }}">
                    <i class="fa-solid {{ $airIcon }} {{ $airTheme['text'] }} text-lg"></i>
                </div>

                <div>
                    <p class="font-semibold {{ $airTheme['text'] }}">
                        {{ $statusAir }}
                    </p>

                    <p class="mt-2 text-sm leading-relaxed text-slate-700">
                        {{ $air['kesimpulan'] }}
                    </p>
                </div>
            </div>
        </div>

        {{-- <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
            <p class="font-semibold text-slate-900">
                Dasar Perhitungan
            </p>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">
                Estimasi kebutuhan air dihitung menggunakan pendekatan sederhana
                dengan membandingkan kebutuhan air tanaman padi sebesar
                <b>600-800 mm per 120 hari</b>
                terhadap total curah hujan hasil analisis selama periode yang sama.
            </p>
        </div> --}}

        @if(!empty($catatanAir))
            <div class="mt-4 rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                <p class="font-semibold text-yellow-800">
                    Catatan
                </p>
                <p class="mt-2 text-sm leading-relaxed text-slate-700">
                    {{ $catatanAir }}
                </p>
            </div>
        @endif
    </div>
</div>
@endif

<!-- Rekomendasi Varietas Padi -->
@if(!empty($rekomendasiVarietas))
@php
    $isHistorical = $isHistorical ?? false;

    $judulVarietasPadi = $isHistorical ? 'Evaluasi Kesesuaian Varietas Padi' : 'Rekomendasi Varietas Padi';
    $labelAksiVarietas = $isHistorical ? 'Evaluasi' : 'Rekomendasi';
    $labelDetailVarietas = $isHistorical ? 'Lihat detail evaluasi varietas' : 'Lihat detail rekomendasi varietas';
    $labelVarietasUtama = $isHistorical
        ? '3 varietas utama yang dinilai sesuai:'
        : '3 varietas utama yang direkomendasikan:';

    $teksKeteranganVarietasUtama = $isHistorical
        ? 'Varietas utama yang dinilai sesuai berdasarkan kondisi iklim pada periode ini.'
        : 'Rekomendasi varietas utama berdasarkan kondisi iklim pada periode ini.';

    $teksSumberVarietas = $isHistorical
        ? 'Evaluasi varietas ini didasarkan pada total curah hujan selama 120 hari dan data Kalender Tanam Bogor.'
        : 'Rekomendasi varietas ini didasarkan pada total curah hujan selama 120 hari dan data Kalender Tanam Bogor.';

    $tipeLahan = 'Sawah Irigasi';

    $teksDisclaimerVarietas = 'Catatan: Sistem ini difokuskan pada analisis padi sawah irigasi berbasis data klimatologi tingkat stasiun cuaca. Hasil yang ditampilkan digunakan sebagai alat bantu pendukung keputusan dan tetap perlu mempertimbangkan kondisi aktual lahan, ketersediaan irigasi, drainase, tanah, varietas, hama penyakit, serta arahan penyuluh atau pakar pertanian.';

    $varietasIcon = match($rekomendasiVarietas['kategori']) {
        'Potensi Kekeringan', 'Varietas Tahan Kekeringan' => 'fa-sun text-orange-600',
        'Kondisi Air Cukup', 'Varietas Umum' => 'fa-droplet text-green-600',
        'Potensi Banjir / Genangan', 'Varietas Toleran Genangan' => 'fa-water text-blue-600',
        default => 'fa-circle-info text-slate-600',
    };

    $varietasTheme = match($rekomendasiVarietas['kategori']) {
        'Potensi Kekeringan', 'Varietas Tahan Kekeringan' => [
            'bg' => 'bg-orange-50',
            'border' => 'border-orange-100',
            'text' => 'text-orange-700',
            'iconBg' => 'bg-orange-100',
        ],
        'Kondisi Air Cukup', 'Varietas Umum' => [
            'bg' => 'bg-emerald-50',
            'border' => 'border-emerald-100',
            'text' => 'text-emerald-700',
            'iconBg' => 'bg-emerald-100',
        ],
        'Potensi Banjir / Genangan', 'Varietas Toleran Genangan' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-100',
            'text' => 'text-blue-700',
            'iconBg' => 'bg-blue-100',
        ],
        default => [
            'bg' => 'bg-slate-50',
            'border' => 'border-slate-100',
            'text' => 'text-slate-700',
            'iconBg' => 'bg-slate-100',
        ],
    };

    $tanggalTanamVarietas = \Carbon\Carbon::parse($tanggalTanam)->format('d M Y');

    $daftarVarietasUtama = $rekomendasiVarietas['varietas_utama'] ?? [];
    $daftarVarietasUtamaUntukTeks = $daftarVarietasUtama;

    if (count($daftarVarietasUtamaUntukTeks) > 1) {
        $varietasTerakhir = array_pop($daftarVarietasUtamaUntukTeks);
        $teksVarietasUtama = implode(', ', $daftarVarietasUtamaUntukTeks) . ', dan ' . $varietasTerakhir;
    } elseif (count($daftarVarietasUtamaUntukTeks) === 1) {
        $teksVarietasUtama = $daftarVarietasUtamaUntukTeks[0];
    } else {
        $teksVarietasUtama = 'belum dapat ditentukan';
    }

    if (!$rekomendasiVarietas['valid']) {
        $teksRekomendasiVarietas = $isHistorical
            ? "Evaluasi kesesuaian varietas pada tanggal {$tanggalTanamVarietas} belum dapat ditentukan karena data iklim 120 hari belum lengkap."
            : "Rekomendasi varietas pada tanggal {$tanggalTanamVarietas} belum dapat ditentukan karena data iklim 120 hari belum lengkap.";
    } else {
        $teksRekomendasiVarietas = $isHistorical
            ? match($rekomendasiVarietas['kategori']) {
                'Potensi Kekeringan', 'Varietas Tahan Kekeringan' =>
                    "Curah hujan selama 120 hari dari tanggal {$tanggalTanamVarietas} berada di bawah kebutuhan air untuk tanaman padi. Berdasarkan kondisi iklim pada periode tersebut, varietas berikut dinilai sesuai sebagai pilihan varietas: {$teksVarietasUtama}.",

                'Kondisi Air Cukup', 'Varietas Umum' =>
                    "Curah hujan selama 120 hari dari tanggal {$tanggalTanamVarietas} berada pada rentang optimal untuk kebutuhan air pada tanaman padi. Berdasarkan kondisi iklim pada periode tersebut, varietas berikut dinilai sesuai sebagai pilihan varietas: {$teksVarietasUtama}.",

                'Potensi Banjir / Genangan', 'Varietas Toleran Genangan' =>
                    "Curah hujan selama 120 hari dari tanggal {$tanggalTanamVarietas} melebihi kebutuhan air untuk tanaman padi. Berdasarkan kondisi iklim pada periode tersebut, varietas berikut dinilai sesuai sebagai pilihan varietas: {$teksVarietasUtama}.",

                default =>
                    "Evaluasi kesesuaian varietas pada tanggal {$tanggalTanamVarietas} belum dapat ditentukan.",
            }
            : match($rekomendasiVarietas['kategori']) {
                'Potensi Kekeringan', 'Varietas Tahan Kekeringan' =>
                    "Curah hujan selama 120 hari dari tanggal {$tanggalTanamVarietas} berada di bawah kebutuhan air untuk tanaman padi, sehingga varietas yang disarankan adalah {$teksVarietasUtama}.",

                'Kondisi Air Cukup', 'Varietas Umum' =>
                    "Curah hujan selama 120 hari dari tanggal {$tanggalTanamVarietas} berada pada rentang optimal untuk kebutuhan air pada tanaman padi, sehingga varietas yang dapat dipertimbangkan adalah {$teksVarietasUtama}.",

                'Potensi Banjir / Genangan', 'Varietas Toleran Genangan' =>
                    "Curah hujan selama 120 hari dari tanggal {$tanggalTanamVarietas} melebihi kebutuhan air untuk tanaman padi, sehingga varietas yang disarankan adalah {$teksVarietasUtama}.",

                default =>
                    "Rekomendasi varietas pada tanggal {$tanggalTanamVarietas} belum dapat ditentukan.",
            };
    }

    $penjelasanVarietasDisplay = $isHistorical
        ? match($rekomendasiVarietas['kategori']) {
            'Potensi Kekeringan', 'Varietas Tahan Kekeringan' =>
                'Total curah hujan selama 120 hari pada periode tersebut berada di bawah kebutuhan air untuk tanaman padi pada sawah irigasi. Kondisi ini menunjukkan potensi kekurangan air, sehingga varietas yang lebih toleran terhadap kekeringan dinilai sesuai sebagai pilihan varietas.',
            'Kondisi Air Cukup', 'Varietas Umum' =>
                'Total curah hujan selama 120 hari pada periode tersebut berada dalam batas kecukupan air untuk tanaman padi pada sawah irigasi, yaitu 600 – 960 mm per 120 hari. Jika curah hujan berada pada 600 – 800 mm, kondisi tergolong optimal. Jika berada di atas 800 mm hingga 960 mm, air masih tercukupi tetapi pemantauan drainase tetap perlu diperhatikan.',
            'Potensi Banjir / Genangan', 'Varietas Toleran Genangan' =>
                'Total curah hujan selama 120 hari pada periode tersebut melebihi kebutuhan air yang dianjurkan untuk tanaman padi pada sawah irigasi, yaitu 600 – 960 mm per 120 hari. Kondisi ini menunjukkan potensi kelebihan air, genangan, atau banjir, sehingga varietas yang lebih toleran terhadap rendaman dinilai sesuai sebagai pilihan varietas.',
            default =>
                'Evaluasi kesesuaian varietas belum dapat ditentukan karena data iklim pada periode tersebut belum lengkap.',
        }
        : $rekomendasiVarietas['penjelasan'];

    $sumberVarietas = [];

    if ($rekomendasiVarietas['jumlah_aktual'] > 0) {
        $sumberVarietas[] = $rekomendasiVarietas['jumlah_aktual'] . ' hari data aktual';
    }

    if ($rekomendasiVarietas['jumlah_prediksi'] > 0) {
        $sumberVarietas[] = $rekomendasiVarietas['jumlah_prediksi'] . ' hari data prediksi';
    }

    if ($rekomendasiVarietas['jumlah_tidak_tersedia'] > 0) {
        $sumberVarietas[] = $rekomendasiVarietas['jumlah_tidak_tersedia'] . ' hari data tidak tersedia';
    }
@endphp

<div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow">
    <div class="p-6">
        <div class="flex gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $varietasTheme['iconBg'] }}">
                <i class="fa-solid {{ $varietasIcon }} text-3xl"></i>
            </div>

            <div class="flex-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {{ $judulVarietasPadi }}
                </p>

                <h2 class="mt-1 text-2xl font-bold text-slate-900">
                    {{ $rekomendasiVarietas['kategori_tampilan'] }}
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    Analisis curah hujan 120 hari:
                    <b>{{ $rekomendasiVarietas['periode'] }}</b>
                </p>

                <div class="mt-3 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    <i class="fa-solid fa-seedling"></i>
                    Konteks Lahan: {{ $tipeLahan }}
                </div>

                <div class="mt-4 rounded-xl border {{ $varietasTheme['border'] }} {{ $varietasTheme['bg'] }} p-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-lg bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase text-slate-500">
                                Total Curah Hujan
                            </p>
                            <p class="mt-1 text-2xl font-bold {{ $varietasTheme['text'] }}">
                                {{ $rekomendasiVarietas['total_curah_hujan'] }} mm
                            </p>
                            <p class="text-xs text-slate-500">
                                selama 120 hari
                            </p>
                        </div>

                        <div class="md:col-span-2 rounded-lg bg-white p-4 shadow-sm">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $varietasTheme['iconBg'] }}">
                                    <i class="fa-solid fa-lightbulb text-sm {{ $varietasTheme['text'] }}"></i>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold uppercase text-slate-500">
                                        {{ $labelAksiVarietas }}
                                    </p>

                                    <p class="mt-1 text-sm font-semibold leading-relaxed text-slate-800">
                                        {{ $teksRekomendasiVarietas }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($rekomendasiVarietas['valid'])
                    <div class="mt-5">
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $labelVarietasUtama }}
                        </p>

                        <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                            @foreach($rekomendasiVarietas['varietas_utama'] as $index => $namaVarietas)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $varietasTheme['iconBg'] }} text-sm font-bold {{ $varietasTheme['text'] }}">
                                            {{ $index + 1 }}
                                        </div>

                                        <div>
                                            <p class="text-sm font-bold text-slate-900">
                                                {{ $namaVarietas }}
                                            </p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $teksKeteranganVarietasUtama }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="mt-4 rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                        {{ $isHistorical ? 'Evaluasi kesesuaian varietas belum dapat ditentukan karena data iklim 120 hari belum lengkap.' : 'Rekomendasi varietas belum dapat ditentukan karena data iklim 120 hari belum lengkap.' }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <button onclick="toggleAccordion(this)" class="w-full border-t border-slate-200 px-6 py-3 flex items-center justify-between text-left hover:bg-slate-50 transition">
        <span class="text-sm font-semibold text-slate-700">
            {{ $labelDetailVarietas }}
        </span>
        <i class="fa-solid fa-chevron-down accordion-icon text-slate-400 transition-transform transform"></i>
    </button>

    <div class="accordion-content hidden border-t border-slate-200 bg-slate-50 p-6">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="font-semibold text-slate-900">
                Penjelasan {{ $labelAksiVarietas }}
            </p>

            <p class="mt-2 text-sm leading-relaxed text-slate-700">
                {{ $penjelasanVarietasDisplay }}
            </p>
        </div>

        @if($rekomendasiVarietas['valid'])
            @if(!empty($rekomendasiVarietas['varietas_alternatif']))
                <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                    <p class="font-semibold text-slate-900">
                        Varietas Alternatif
                    </p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($rekomendasiVarietas['varietas_alternatif'] as $namaVarietas)
                            <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm font-medium text-slate-700">
                                {{ $namaVarietas }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
            <p class="font-semibold text-slate-900">
                Sumber Data
            </p>

            @if(count($sumberVarietas) > 0)
                <p class="mt-2 text-sm text-slate-700">
                    {{ implode(', ', $sumberVarietas) }}.
                </p>
            @endif

            <p class="mt-2 text-xs text-slate-500">
                {{ $teksSumberVarietas }}
            </p>

            <div class="mt-3 rounded-lg border border-yellow-200 bg-yellow-50 p-3 text-xs leading-relaxed text-yellow-800">
                <p class="font-semibold">
                    Catatan Batas Operasional
                </p>
                <p class="mt-1">
                    {{ $teksDisclaimerVarietas }}
                </p>
            </div>
        </div>
    </div>
</div>
@endif

@else
<p>Data belum tersedia</p>
@endif

<script>
    // Data dari controller
    const grafikData = @json($grafikData ?? []);
    // Register datalabels plugin if loaded
    if (typeof ChartDataLabels !== 'undefined' && typeof Chart !== 'undefined') {
        Chart.register(ChartDataLabels);
    }
    
    // Fungsi untuk toggle accordion (gunakan Font Awesome chevron, toggle kelas rotate-180)
    function toggleAccordion(button) {
        const content = button.nextElementSibling;
        const icon = button.querySelector('.accordion-icon');
        const isHidden = content.classList.toggle('hidden');
        if (icon) {
            icon.classList.toggle('rotate-180', !isHidden);
        }
    }

    const navbar = document.querySelector('header');
    const navToggle = document.getElementById('navToggle');
    const mobileMenu = document.getElementById('mobileMenu');

    navToggle.addEventListener('click', () => {
        const willOpen = mobileMenu.classList.contains('hidden');
        mobileMenu.classList.toggle('hidden');
        navToggle.setAttribute('aria-expanded', String(willOpen));
    });

    document.querySelectorAll('#mobileMenu a').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
            navToggle.setAttribute('aria-expanded', 'false');
        });
    });

    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            navbar.classList.add('navbar-scrolled', 'shadow-2xl');
        } else {
            navbar.classList.remove('navbar-scrolled', 'shadow-2xl');
        }
    });
    
    console.log('🔍 Grafik Data:', grafikData);
    console.log('📊 Jumlah data:', grafikData.labels ? grafikData.labels.length : 0);
    
    const sourceColors = {
        actual: '#16a34a',
        realtime: '#2563eb',
        predicted: '#f97316',
        fallback_realtime: '#f97316'
    };
    const sourceBorderColors = {
        actual: '#15803d',
        realtime: '#1d4ed8',
        predicted: '#ea580c',
        fallback_realtime: '#ea580c'
    };
    const legendItems = [
        { text: 'Data Aktual', fillStyle: sourceColors.actual, strokeStyle: sourceBorderColors.actual },
        { text: 'Data Realtime', fillStyle: sourceColors.realtime, strokeStyle: sourceBorderColors.realtime },
        { text: 'Data Prediksi', fillStyle: sourceColors.predicted, strokeStyle: sourceBorderColors.predicted }
    ];
    const sources = Array.isArray(grafikData.sources) ? grafikData.sources : [];
    const sourceColorAt = (index) => sourceColors[sources[index]] || sourceColors.predicted;
    const sourceBorderColorAt = (index) => sourceBorderColors[sources[index]] || sourceBorderColors.predicted;
    const legendConfig = {
        display: true,
        labels: {
            font: { size: 14 },
            padding: 15,
            generateLabels: function() {
                return legendItems.map((item, index) => ({
                    text: item.text,
                    fillStyle: item.fillStyle,
                    strokeStyle: item.strokeStyle,
                    lineWidth: 0,
                    hidden: false,
                    datasetIndex: 0,
                    index
                }));
            }
        },
        onClick: function() {}
    };

    // Konfigurasi Chart Suhu
    if (grafikData && grafikData.labels && grafikData.labels.length > 0) {
        console.log('Membuat Chart Suhu');
        const suhuData = Array.isArray(grafikData.suhu) ? grafikData.suhu.filter(value => typeof value === 'number') : [];
        const suhuMin = suhuData.length ? Math.min(...suhuData) : 20;
        const suhuMax = suhuData.length ? Math.max(...suhuData) : 35;
        const suhuRange = Math.max(suhuMax - suhuMin, 4);
        const suhuPadding = Math.max(1, suhuRange * 0.1);
        const suhuScaleMin = Math.max(0, suhuMin - suhuPadding);
        const suhuScaleMax = suhuMax + suhuPadding;

        const ctxSuhu = document.getElementById('chartSuhu');
        if (ctxSuhu) {
            new Chart(ctxSuhu.getContext('2d'), {
                type: 'line',
                data: {
                    labels: grafikData.labels,
                    datasets: [{
                        label: 'Suhu (°C)',
                        data: grafikData.suhu,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.12)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: grafikData.suhu.map((_, index) => sourceColorAt(index)),
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        spanGaps: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        datalabels: {
                            display: true,
                            color: '#111827',
                            anchor: 'end',
                            align: 'top',
                            offset: -4,
                            font: { weight: '600', size: 11 },
                            //formatter: function(value) { return value; }
                            formatter: function(value) {
                                if (value === null || value === undefined) return '';
                                return Number(value).toFixed(Number.isInteger(value) ? 0 : 1);
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: suhuScaleMin,
                            max: suhuScaleMax,
                            ticks: {
                                font: { size: 12 }
                            },
                            title: {
                                display: true,
                                text: 'Suhu (°C)'
                            }
                        },
                        x: {
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }

        // Konfigurasi Chart Curah Hujan
        console.log('Membuat Chart Curah Hujan');
        const ctxCurahHujan = document.getElementById('chartCurahHujan');
        if (ctxCurahHujan) {
            new Chart(ctxCurahHujan.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: grafikData.labels,
                    datasets: [{
                        label: 'Curah Hujan (mm)',
                        data: grafikData.curah_hujan,
                        backgroundColor: grafikData.curah_hujan.map((_, index) => sourceColorAt(index)),
                        borderColor: grafikData.curah_hujan.map((_, index) => sourceBorderColorAt(index)),
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        datalabels: {
                            color: '#111827',
                            anchor: 'end',
                            align: 'end',
                            offset: -4,
                            font: { weight: '600', size: 11 },
                            // formatter: function(value) { return value; }
                            formatter: function(value) {
                                if (value === null || value === undefined) return '';
                                return Number(value).toFixed(Number.isInteger(value) ? 0 : 2);
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: { size: 12 }
                            },
                            title: {
                                display: true,
                                text: 'Curah Hujan (mm)'
                            }
                        },
                        x: {
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }
    } else {
        console.warn('Data grafik tidak tersedia atau kosong');
        console.warn('Labels:', grafikData.labels);
    }
</script>

<div id="downloadReport">
    <h1>Laporan Hasil Analisis Pertanian Presisi untuk Komoditas Padi Wilayah Bogor</h1>

    <p>
        Dicetak pada:
        <strong>{{ now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y H:i') }}</strong>
    </p>

    @if(request('tanggal_tanam'))
        <h2>Informasi Tanggal Tanam</h2>
        <p>
            Tanggal tanam:
            <strong>{{ \Carbon\Carbon::parse(request('tanggal_tanam'))->locale('id')->translatedFormat('d F Y') }}</strong>
        </p>
        <p>
            Hari:
            <strong>{{ \Carbon\Carbon::parse(request('tanggal_tanam'))->locale('id')->translatedFormat('l') }}</strong>
        </p>
    @endif

    @if($umur !== null && $umur > 0)
        <h2>Informasi Tanaman</h2>
        <p>Umur tanaman: <strong>{{ $umur }} hari</strong></p>
        <p>Fase tanaman: <strong>{{ $fase }}</strong></p>
    @endif

    @if($data)
        <h2>Informasi Iklim Harian</h2>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Nilai</th>
                    <th>Sumber Data</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Suhu</td>
                    <td>{{ $data->suhu }} °C</td>
                    <td>{{ $data->source_label }}</td>
                </tr>
                <tr>
                    <td>Curah Hujan</td>
                    <td>{{ $data->curah_hujan }} mm</td>
                    <td>{{ $data->source_label }}</td>
                </tr>
                <tr>
                    <td>Kelembaban</td>
                    <td>{{ $data->kelembaban }} %</td>
                    <td>{{ $data->source_label }}</td>
                </tr>
            </tbody>
        </table>

        @if($data->updated_at_label)
            <p>
                Terakhir diperbarui:
                <strong>{{ $data->updated_at_label }}</strong>
            </p>
        @endif
    @endif

    @if(!empty($rekomendasiIklim))
        <h2>{{ $judulCardTanam ?? 'Rekomendasi Waktu Tanam' }}</h2>

        <p>{{ $isHistorical ? 'Status evaluasi' : 'Status rekomendasi' }}: <strong>{{ $statusTanamLabel ?? $statusUser }}</strong></p>
        <p>Skor Kelayakan: <strong>{{ $rekomendasiIklim['skor_fuzzy'] ?? $rekomendasiIklim['skor'] ?? '-' }}/100</strong></p>
        <p>Periode analisis: <strong>{{ $rekomendasiIklim['periode'] }}</strong></p>
        <p>{{ $kesimpulan }}</p>

        <table>
            <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Nilai</th>
                    <th>Status</th>
                    <th>Rentang Acuan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Suhu</td>
                    <td>{{ $suhu !== null ? $suhu.' °C' : '-' }}</td>
                    <td>{{ $suhuOk ? 'Mendukung' : 'Belum Mendukung' }}</td>
                    <td>22 – 30°C</td>
                </tr>
                <tr>
                    <td>Kelembaban</td>
                    <td>{{ $kelembaban !== null ? $kelembaban.' %' : '-' }}</td>
                    <td>{{ $kelembabanOk ? 'Mendukung' : 'Belum Mendukung' }}</td>
                    <td>63 – 83%</td>
                </tr>
                <tr>
                    <td>Curah Hujan</td>
                    <td>{{ $hujan !== null ? $hujan.' mm' : '-' }}</td>
                    <td>{{ $hujanOk ? 'Mendukung' : 'Belum Mendukung' }}</td>
                    <td>600 – 800 mm/120 hari</td>
                </tr>
            </tbody>
        </table>

        <p><strong>Kesimpulan:</strong> {{ $kesimpulanDetail }}</p>
        <p><strong>{{ $labelSaranTanam ?? ($isHistorical ? 'Catatan Evaluasi' : 'Saran') }}:</strong> {{ $saranDetail }}</p>
    @endif

    @if(!empty($rekomendasiIklim['kebutuhan_air']))
        <h2>{{ $judulAir ?? 'Estimasi Kebutuhan Air' }}</h2>

        <p>Status: <strong>{{ $statusAir }}</strong></p>
        <p>Kebutuhan air: <strong>{{ $air['kebutuhan_minimum'] }} – {{ $air['kebutuhan_maksimum'] }} mm/120 hari</strong></p>
        <p>Total curah hujan: <strong>{{ $air['total_curah_hujan'] !== null ? $air['total_curah_hujan'].' mm' : '-' }}</strong></p>

        @if($nilaiSelisihAir !== null)
            <p>{{ $labelSelisihAir }}: <strong>{{ $nilaiSelisihAir }} mm</strong></p>
        @endif

        <p>{{ $kesimpulanAir ?? $air['kesimpulan'] }}</p>

        @if(!empty($catatanAir))
            <p><strong>Catatan:</strong> {{ $catatanAir }}</p>
        @endif
    @endif

    @if(!empty($rekomendasiVarietas))
        <h2>{{ $judulVarietasPadi ?? 'Rekomendasi Varietas Padi' }}</h2>

        <p>Kategori: <strong>{{ $rekomendasiVarietas['kategori_tampilan'] }}</strong></p>
        <p>Periode analisis: <strong>{{ $rekomendasiVarietas['periode'] }}</strong></p>
        <p>Total curah hujan: <strong>{{ $rekomendasiVarietas['total_curah_hujan'] }} mm</strong></p>
        <p>{{ $teksRekomendasiVarietas }}</p>

        @if($rekomendasiVarietas['valid'])
            <p><strong>{{ $isHistorical ? 'Varietas yang dinilai sesuai:' : 'Varietas utama:' }}</strong></p>
            <ol>
                @foreach($rekomendasiVarietas['varietas_utama'] as $namaVarietas)
                    <li>{{ $namaVarietas }}</li>
                @endforeach
            </ol>

            @if(!empty($rekomendasiVarietas['varietas_alternatif']))
                <p>
                    <strong>Varietas alternatif:</strong>
                    {{ implode(', ', $rekomendasiVarietas['varietas_alternatif']) }}
                </p>
            @endif
        @endif

        <p>{{ $penjelasanVarietasDisplay ?? $rekomendasiVarietas['penjelasan'] }}</p>
    @endif

    <h2>Catatan</h2>
    <p>
        Laporan ini dihasilkan secara otomatis oleh Website Pertanian Presisi untuk komoditas padi
        berdasarkan data klimatologi, data prediksi, dan {{ ($isHistorical ?? false) ? 'hasil evaluasi kondisi pada periode yang dipilih.' : 'hasil analisis rekomendasi.' }}
    </p>
</div>

<script>
    function downloadLaporan() {
        const report = document.getElementById('downloadReport');

        if (!report) {
            alert('Data laporan belum tersedia.');
            return;
        }

        const tanggalTanam = "{{ request('tanggal_tanam') ?? 'tanpa-tanggal' }}";
        const printWindow = window.open('', '_blank');

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Laporan-Pertanian-Presisi-${tanggalTanam}</title>
                <style>
                    @page {
                        size: A4;
                        margin: 14mm;
                    }

                    body {
                        background: #ffffff;
                        color: #0f172a;
                        font-family: Arial, sans-serif;
                        padding: 0;
                        margin: 0;
                    }

                    h1 {
                        font-size: 22px;
                        font-weight: bold;
                        margin-bottom: 8px;
                    }

                    h2 {
                        font-size: 16px;
                        font-weight: bold;
                        margin-top: 20px;
                        margin-bottom: 8px;
                        border-bottom: 1px solid #cbd5e1;
                        padding-bottom: 4px;
                    }

                    p,
                    li {
                        font-size: 13px;
                        line-height: 1.6;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 8px;
                        margin-bottom: 12px;
                    }

                    th,
                    td {
                        border: 1px solid #cbd5e1;
                        padding: 8px;
                        font-size: 12px;
                        text-align: left;
                        vertical-align: top;
                    }

                    th {
                        background: #f1f5f9;
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                ${report.innerHTML}
            </body>
            </html>
        `);

        printWindow.document.close();

        printWindow.onload = function () {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        };
    }
</script>

</body>
</html>
