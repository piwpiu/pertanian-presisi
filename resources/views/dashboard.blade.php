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
    @media print {
        button { display: none; }
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
<header id="navbar" class="fixed top-0 left-0 w-full z-50 transition-all duration-300 border-b border-slate-200 bg-white/90 shadow-sm backdrop-blur-sm">
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

        <div class="hidden flex-1 items-center justify-end gap-4 lg:flex">
            <nav class="flex flex-wrap items-center gap-2">
                <a href="#info" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50 hover:text-emerald-700">Info</a>
                <a href="#grafik" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50 hover:text-emerald-700">Grafik</a>
                <a href="#rekomendasi" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50 hover:text-emerald-700">Rekomendasi</a>
                <a href="#download" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50 hover:text-emerald-700">Download</a>
            </nav>
            <form method="GET" action="/" class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 shadow-sm">
                <label class="sr-only">Masukkan Tanggal Tanam</label>
                <input type="date" name="tanggal_tanam" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400" value="{{ request('tanggal_tanam') ?? '' }}">
                <button class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">Lihat</button>
            </form>
        </div>

        <button id="navToggle" aria-expanded="false" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-800 shadow-sm transition duration-300 hover:bg-slate-100 lg:hidden">
            <span class="sr-only">Toggle navigation menu</span>
            <i class="fa-solid fa-bars text-lg" aria-hidden="true"></i>
        </button>
    </div>

    <div class="h-1.5 bg-gradient-to-r from-emerald-500 via-emerald-400 to-lime-300 w-full"></div>
    <!-- Mobile navigation menu -->
    <div id="mobileMenu" class="hidden border-t border-slate-200 bg-white/95 px-4 py-4 shadow-xl backdrop-blur-xl lg:hidden">
        <nav class="flex flex-col gap-3">
            <a href="#info" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50">Info</a>
            <a href="#grafik" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50">Grafik</a>
            <a href="#rekomendasi" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50">Rekomendasi</a>
            <a href="#download" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition duration-300 hover:bg-emerald-50">Download</a>
        </nav>
        <form method="GET" action="/" class="mt-4 flex gap-2 rounded-full border border-slate-200 bg-slate-50 p-2">
            <label class="sr-only">Masukkan Tanggal Tanam</label>
            <input type="date" name="tanggal_tanam" class="flex-1 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400" value="{{ request('tanggal_tanam') ?? '' }}">
            <button class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500">Lihat</button>
        </form>
    </div>
</header>
<div class="h-20"></div>
<div class="max-w-6xl mx-auto px-4 py-4">

@if(request('tanggal_tanam'))
<div class="mt-4 bg-blue-50 border border-blue-200 p-4 rounded-xl shadow">
    <h2 class="font-semibold text-blue-700 mb-1"></h2>
    
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
        @else text-red-500
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
                <strong>Suhu udara</strong> adalah ukuran derajat panas atau dingin di sekitar tanaman. Suhu optimal untuk padi berkisar <strong>24 – 32°C</strong>. Suhu yang terlalu tinggi dapat meningkatkan penguapan serta menurunkan produktivitas tanaman, sementara suhu terlalu rendah dapat menghambat pertumbuhan vegetatif.
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
                <strong>Curah hujan</strong> menunjukkan jumlah air hujan yang diterima dalam satu hari. Pada tanaman padi, curah hujan sekitar <strong>4 – 7 mm/hari</strong> umumnya mendukung pertumbuhan yang optimal. Curah hujan yang terlalu rendah dapat menyebabkan kekurangan air, sedangkan curah hujan yang terlalu tinggi dapat menimbulkan genangan.
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
                <strong>Kelembaban udara</strong> adalah persentase uap air di udara. Untuk tanaman padi, kelembaban relatif sekitar <strong>60 – 90%</strong> tergolong ideal. Kelembaban yang terlalu rendah dapat menyebabkan tanaman kehilangan air lebih cepat, sedangkan kelembaban yang terlalu tinggi dapat meningkatkan risiko penyakit.
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

<!-- Download section anchor -->
<div id="download" class="section-anchor"></div>

<!-- Kondisi Iklim Optimal -->
@if(!empty($rekomendasiIklim))
@php
    $suhu = $rekomendasiIklim['rata_suhu'];
    $kelembaban = $rekomendasiIklim['rata_kelembaban'];
    $hujan = $rekomendasiIklim['total_curah_hujan'];

    $suhuOk = $suhu !== null && $suhu >= 24 && $suhu <= 32;
    $kelembabanOk = $kelembaban !== null && $kelembaban >= 60 && $kelembaban <= 90;
    $hujanOk = $hujan !== null && $hujan >= 150 && $hujan <= 200;

    $tanggalTanamLabel = \Carbon\Carbon::parse($tanggalTanam)->format('d M Y');

    $parameterWaspada = [];

    if (!$suhuOk && $suhu !== null) {
        $parameterWaspada[] = 'suhu belum berada pada rentang optimal';
    }

    if (!$kelembabanOk && $kelembaban !== null) {
        $parameterWaspada[] = 'kelembaban belum berada pada rentang optimal';
    }

    if (!$hujanOk && $hujan !== null && $hujan < 150) {
        $parameterWaspada[] = 'curah hujan masih rendah';
    } elseif (!$hujanOk && $hujan !== null && $hujan > 200) {
        $parameterWaspada[] = 'curah hujan terlalu tinggi';
    }

    if (count($parameterWaspada) > 1) {
        $lastItem = array_pop($parameterWaspada);
        $teksWaspada = implode(', ', $parameterWaspada) . ' dan ' . $lastItem;
    } elseif (count($parameterWaspada) === 1) {
        $teksWaspada = $parameterWaspada[0];
    } else {
        $teksWaspada = 'terdapat risiko iklim yang perlu diperhatikan';
    }

    $statusUser = match($rekomendasiIklim['status']) {
        'Direkomendasikan' => 'Direkomendasikan',
        'Direkomendasikan dengan Waspada' => 'Direkomendasikan dengan Waspada',
        'Tidak Direkomendasikan' => 'Tidak Direkomendasikan',
        default => 'Data Belum Lengkap',
    };

    $statusIcon = match($rekomendasiIklim['status']) {
        'Direkomendasikan' => 'fa-circle-check text-green-600',
        'Direkomendasikan dengan Waspada' => 'fa-triangle-exclamation text-yellow-600',
        'Tidak Direkomendasikan' => 'fa-circle-xmark text-red-600',
        default => 'fa-circle-info text-slate-600',
    };

    $kesimpulan = match($rekomendasiIklim['status']) {
        'Direkomendasikan' =>
            'Kondisi iklim selama 30 hari mendukung untuk memulai tanam padi.',

        'Direkomendasikan dengan Waspada' =>
            "Tanam masih dapat dipertimbangkan, tetapi terdapat kondisi iklim yang perlu diwaspadai, yaitu {$teksWaspada}.",

        'Tidak Direkomendasikan' =>
            "Kondisi iklim selama 30 hari belum mendukung untuk memulai tanam padi karena {$teksWaspada}.",

        default =>
            'Data iklim 30 hari belum lengkap untuk menentukan rekomendasi.',
    };

    $kesimpulanDetail = match($rekomendasiIklim['status']) {
        'Direkomendasikan' =>
            'Berdasarkan hasil analisis selama 30 hari, suhu, kelembaban, dan curah hujan berada pada rentang yang mendukung. Kondisi ini dapat dijadikan pertimbangan untuk memulai tanam padi.',

        'Direkomendasikan dengan Waspada' =>
            "Berdasarkan hasil analisis selama 30 hari, sebagian kondisi iklim sudah mendukung. Namun, terdapat kondisi yang perlu diperhatikan, yaitu {$teksWaspada}.",

        'Tidak Direkomendasikan' =>
            "Berdasarkan hasil analisis selama 30 hari, kondisi iklim belum cukup mendukung karena {$teksWaspada}. Kondisi tersebut dapat meningkatkan risiko gangguan pada fase awal pertumbuhan padi.",

        default =>
            'Data iklim selama 30 hari belum lengkap sehingga rekomendasi belum dapat dihitung secara penuh.',
    };

    $saranDetail = match($rekomendasiIklim['status']) {
        'Direkomendasikan' =>
            "Tanam pada tanggal {$tanggalTanamLabel} dapat dipertimbangkan karena kondisi iklim selama 30 hari berada pada rentang yang mendukung. Petani tetap disarankan melakukan pemantauan rutin terhadap kondisi lahan.",

        'Direkomendasikan dengan Waspada' =>
            "Tanam pada tanggal {$tanggalTanamLabel} masih dapat dipertimbangkan, tetapi perlu dilakukan antisipasi terhadap {$teksWaspada}. Pastikan pengelolaan air, drainase, atau irigasi disiapkan sesuai kondisi lahan.",

        'Tidak Direkomendasikan' =>
            "Tanam pada tanggal {$tanggalTanamLabel} belum disarankan karena {$teksWaspada}. Sebaiknya menunda waktu tanam atau menunggu periode dengan kondisi iklim yang lebih sesuai.",

        default =>
            'Rekomendasi belum dapat diberikan karena data iklim 30 hari belum lengkap.',
    };

    $alasanSuhu = $suhuOk
        ? "Suhu mendukung karena rata-rata suhu {$suhu}°C masih berada dalam rentang 24 – 32°C."
        : "Suhu belum mendukung karena rata-rata suhu {$suhu}°C berada di luar rentang 24 – 32°C.";

    $alasanKelembaban = $kelembabanOk
        ? "Kelembaban mendukung karena rata-rata kelembaban {$kelembaban}% masih berada dalam rentang 60 – 90%."
        : "Kelembaban belum mendukung karena rata-rata kelembaban {$kelembaban}% berada di luar rentang 60 – 90%.";

    if ($hujanOk) {
        $alasanHujan = "Curah hujan mendukung karena total curah hujan {$hujan} mm selama 30 hari berada dalam rentang 150 – 200 mm.";
    } elseif ($hujan !== null && $hujan < 150) {
        $alasanHujan = "Curah hujan belum mendukung karena total curah hujan {$hujan} mm selama 30 hari masih lebih rendah dari kebutuhan air padi 150 mm. Kondisi ini dapat menunjukkan potensi kekurangan air.";
    } elseif ($hujan !== null && $hujan > 200) {
        $alasanHujan = "Curah hujan belum mendukung karena total curah hujan {$hujan} mm selama 30 hari melebihi kebutuhan air padi 200 mm. Kondisi ini dapat meningkatkan risiko genangan.";
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
                    Status Rekomendasi Tanam
                </p>

                <h2 class="mt-1 text-2xl font-bold text-slate-900">
                    {{ $statusUser }}
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
                        {{ $rekomendasiIklim['skor'] }} dari 3 parameter iklim berada pada rentang mendukung.

                        @if($rekomendasiIklim['skor'] === 3 && !empty($rekomendasiIklim['risiko_iklim']))
                            Namun terdapat {{ count($rekomendasiIklim['risiko_iklim']) }} risiko iklim yang perlu diwaspadai.
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>

    <button onclick="toggleAccordion(this)" class="w-full border-t border-slate-200 px-6 py-3 flex items-center justify-between text-left hover:bg-slate-50 transition">
        <span class="text-sm font-semibold text-slate-700">
            Lihat detail perhitungan rekomendasi
        </span>
        <i class="fa-solid fa-chevron-down accordion-icon text-slate-400 transition-transform transform"></i>
    </button>

    <div class="accordion-content hidden border-t border-slate-200 bg-slate-50 p-6">
        @if(!$rekomendasiIklim['valid'])
            <div class="mb-4 rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                <p class="font-semibold">Data iklim 30 hari belum lengkap.</p>
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
                <p class="text-xs text-slate-600">Rentang optimal: 24 – 32°C</p>
            </div>

            <div class="rounded-xl border p-4 {{ $kelembabanOk ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                <p class="font-semibold {{ $kelembabanOk ? 'text-green-700' : 'text-red-700' }}">
                    {{ $kelembabanOk ? '✓ Kelembaban mendukung' : '✗ Kelembaban belum mendukung' }}
                </p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                    {{ $kelembaban !== null ? $kelembaban.' %' : '-' }}
                </p>
                <p class="text-xs text-slate-600">Rentang optimal: 60 – 90%</p>
            </div>

            <div class="rounded-xl border p-4 {{ $hujanOk ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                <p class="font-semibold {{ $hujanOk ? 'text-green-700' : 'text-red-700' }}">
                    {{ $hujanOk ? '✓ Curah hujan mendukung' : '✗ Curah hujan belum mendukung' }}
                </p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                    {{ $hujan !== null ? $hujan.' mm' : '-' }}
                </p>
                <p class="text-xs text-slate-600">Rentang optimal: 150 – 200 mm/30 hari</p>
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
                <li>{{ $suhuOk ? '✓' : '✗' }} {{ $alasanSuhu }}</li>
                <li>{{ $kelembabanOk ? '✓' : '✗' }} {{ $alasanKelembaban }}</li>
                <li>{{ $hujanOk ? '✓' : '✗' }} {{ $alasanHujan }}</li>
            </ul>

            <p class="mt-4 font-semibold text-slate-900">Saran pertanian</p>
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
                            <div class="rounded-lg border border-yellow-200 bg-white p-3">
                                <p class="text-sm font-bold text-yellow-800">
                                    {{ $risikoItem['jenis'] }} - {{ $risikoItem['tingkat'] }}
                                </p>

                                <p class="mt-1 text-sm leading-relaxed text-slate-700">
                                    {{ $risikoItem['pesan'] }}
                                </p>

                                <p class="mt-1 text-sm leading-relaxed text-slate-700">
                                    {{ $risikoItem['saran'] }}
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
    $air = $rekomendasiIklim['kebutuhan_air'];
    $statusAir = $air['status'] ?? 'Belum Dapat Dihitung';

    $airIcon = match($statusAir) {
        'Kebutuhan Air Kurang' => 'fa-seedling text-orange-600',
        'Kebutuhan Air Tercukupi' => 'fa-circle-check text-green-600',
        'Kelebihan Air' => 'fa-cloud-rain text-blue-600',
        default => 'fa-circle-info text-slate-600',
    };

    $airTheme = match($statusAir) {
        'Kebutuhan Air Kurang' => [
            'iconBg' => 'bg-orange-100',
            'bg' => 'bg-orange-50',
            'border' => 'border-orange-100',
            'text' => 'text-orange-700',
            'ring' => 'ring-orange-100',
        ],
        'Kebutuhan Air Tercukupi' => [
            'iconBg' => 'bg-green-100',
            'bg' => 'bg-green-50',
            'border' => 'border-green-100',
            'text' => 'text-green-700',
            'ring' => 'ring-green-100',
        ],
        'Kelebihan Air' => [
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

    $labelSelisihAir = $statusAir === 'Kelebihan Air'
        ? 'Kelebihan Air'
        : 'Estimasi Kekurangan Air';

    $nilaiSelisihAir = $statusAir === 'Kelebihan Air'
        ? ($air['kelebihan_air'] ?? null)
        : ($air['estimasi_kekurangan_air'] ?? null);

    $ringkasanAir = match($statusAir) {
        'Kebutuhan Air Kurang' => 'Curah hujan belum mencukupi kebutuhan minimum air untuk tanaman padi, sehingga diperlukan perhatian terhadap ketersediaan air.',
        'Kebutuhan Air Tercukupi' => 'Curah hujan berada pada rentang optimal kebutuhan air untuk tanaman padi, sehingga kondisi air pada awal tanam relatif mencukupi.',
        'Kelebihan Air' => 'Curah hujan melebihi kebutuhan air untuk tanaman padi, sehingga perlu memperhatikan potensi genangan dan drainase lahan.',
        default => 'Data curah hujan belum lengkap untuk menghitung estimasi kebutuhan air.',
    };
@endphp

<div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow">
    <div class="p-6">
        <div class="flex gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $airTheme['iconBg'] }} ring-4 {{ $airTheme['ring'] }}">
                <i class="fa-solid {{ $airIcon }} text-3xl"></i>
            </div>

            <div class="flex-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Estimasi Kebutuhan Air
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
            Lihat detail estimasi kebutuhan air
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
                            per 30 hari
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
                            hasil analisis 30 hari
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border {{ $airTheme['border'] }} {{ $airTheme['bg'] }} p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $airTheme['iconBg'] }} {{ $airTheme['text'] }}">
                        <i class="fa-solid {{ $statusAir === 'Kelebihan Air' ? 'fa-cloud-rain' : 'fa-seedling' }}"></i>
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
                        {{ $air['kesimpulan'] }}
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
                <b>150–200 mm per 30 hari</b>
                terhadap total curah hujan hasil analisis selama periode yang sama.
            </p>
        </div> --}}

        @if(!empty($air['saran']))
            <div class="mt-4 rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                <p class="font-semibold text-yellow-800">
                    Catatan
                </p>
                <p class="mt-2 text-sm leading-relaxed text-slate-700">
                    {{ $air['saran'] }}
                </p>
            </div>
        @endif
    </div>
</div>
@endif

<!-- Rekomendasi Varietas Padi -->
@if(!empty($rekomendasiVarietas))
@php
    $varietasIcon = match($rekomendasiVarietas['kategori']) {
        'Potensi Kekeringan' => 'fa-sun text-orange-600',
        'Kondisi Air Cukup' => 'fa-droplet text-green-600',
        'Potensi Banjir atau Genangan' => 'fa-water text-blue-600',
        default => 'fa-circle-info text-slate-600',
    };

    $varietasTheme = match($rekomendasiVarietas['kategori']) {
        'Potensi Kekeringan' => [
            'bg' => 'bg-orange-50',
            'border' => 'border-orange-100',
            'text' => 'text-orange-700',
            'iconBg' => 'bg-orange-100',
        ],
        'Kondisi Air Cukup' => [
            'bg' => 'bg-emerald-50',
            'border' => 'border-emerald-100',
            'text' => 'text-emerald-700',
            'iconBg' => 'bg-emerald-100',
        ],
        'Potensi Banjir atau Genangan' => [
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
        $teksRekomendasiVarietas = "Rekomendasi varietas pada tanggal {$tanggalTanamVarietas} belum dapat ditentukan karena data iklim 30 hari belum lengkap.";
    } else {
        $teksRekomendasiVarietas = match($rekomendasiVarietas['kategori']) {
            'Potensi Kekeringan' =>
                "Curah hujan 30 hari dari tanggal {$tanggalTanamVarietas} berada di bawah kebutuhan air untuk tanaman padi, sehingga varietas yang disarankan adalah {$teksVarietasUtama}.",

            'Kondisi Air Cukup' =>
                "Curah hujan 30 hari dari tanggal {$tanggalTanamVarietas} berada pada rentang optimal untuk kebutuhan air pada tanaman padi, sehingga varietas yang dapat dipertimbangkan adalah {$teksVarietasUtama}.",

            'Potensi Banjir atau Genangan' =>
                "Curah hujan 30 hari dari tanggal {$tanggalTanamVarietas} melebihi kebutuhan air untuk tanaman padi, sehingga varietas yang disarankan adalah {$teksVarietasUtama}.",

            default =>
                "Rekomendasi varietas pada tanggal {$tanggalTanamVarietas} belum dapat ditentukan.",
        };
    }

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
                    Rekomendasi Varietas Padi
                </p>

                <h2 class="mt-1 text-2xl font-bold text-slate-900">
                    {{ $rekomendasiVarietas['kategori_tampilan'] }}
                </h2>

                <p class="mt-2 text-sm text-slate-600">
                    Analisis curah hujan 30 hari:
                    <b>{{ $rekomendasiVarietas['periode'] }}</b>
                </p>

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
                                selama 30 hari
                            </p>
                        </div>

                        <div class="md:col-span-2 rounded-lg bg-white p-4 shadow-sm">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $varietasTheme['iconBg'] }}">
                                    <i class="fa-solid fa-lightbulb text-sm {{ $varietasTheme['text'] }}"></i>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold uppercase text-slate-500">
                                        Rekomendasi
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
                            3 varietas utama yang direkomendasikan:
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
                                                Rekomendasi varietas utama berdasarkan kondisi iklim pada periode ini.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="mt-4 rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                        Rekomendasi varietas belum dapat ditentukan karena data iklim 30 hari belum lengkap.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <button onclick="toggleAccordion(this)" class="w-full border-t border-slate-200 px-6 py-3 flex items-center justify-between text-left hover:bg-slate-50 transition">
        <span class="text-sm font-semibold text-slate-700">
            Lihat detail rekomendasi varietas
        </span>
        <i class="fa-solid fa-chevron-down accordion-icon text-slate-400 transition-transform transform"></i>
    </button>

    <div class="accordion-content hidden border-t border-slate-200 bg-slate-50 p-6">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="font-semibold text-slate-900">
                Penjelasan Rekomendasi
            </p>

            <p class="mt-2 text-sm leading-relaxed text-slate-700">
                {{ $rekomendasiVarietas['penjelasan'] }}
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
                Rekomendasi varietas ini didasarkan pada total curah hujan selama 30 hari dan data Kalender Tanam Bogor.
            </p>
        </div>
    </div>
</div>
@endif

@else
<p>Data belum tersedia</p>
@endif

<script>
    /*
      Script section: data injection, navbar/mobile menu toggles,
      and Chart.js rendering for suhu & curah hujan.
    */
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

<div class="max-w-6xl mx-auto px-4 py-3"></div>
<button onclick="window.print()" 
    class="mb-4 bg-blue-500 text-white px-4 py-2 rounded">
    Download / Cetak
</button>

    </div>
</body>
</html>
