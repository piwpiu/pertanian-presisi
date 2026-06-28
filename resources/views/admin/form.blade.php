@php
    $isEdit = isset($data);
    $fields = [
        ['name' => 'TN', 'label' => 'TN - Suhu Minimum', 'unit' => 'Satuan: Celsius', 'placeholder' => 'Contoh: 22.5'],
        ['name' => 'TX', 'label' => 'TX - Suhu Maksimum', 'unit' => 'Satuan: Celsius', 'placeholder' => 'Contoh: 32.5'],
        ['name' => 'TAVG', 'label' => 'TAVG - Suhu Rata-rata', 'unit' => 'Satuan: Celsius', 'placeholder' => 'Contoh: 27.5'],
        ['name' => 'RH_AVG', 'label' => 'RH_AVG - Kelembaban Rata-rata', 'unit' => 'Satuan: %', 'placeholder' => 'Contoh: 75.5'],
        ['name' => 'RR', 'label' => 'RR - Curah Hujan', 'unit' => 'Satuan: mm', 'placeholder' => 'Contoh: 10.5'],
        ['name' => 'SS', 'label' => 'SS - Lama Penyinaran Matahari', 'unit' => 'Satuan: jam', 'placeholder' => 'Contoh: 8.5'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isEdit ? 'Edit' : 'Tambah' }} Data - Admin Pertanian Presisi Padi</title>
    @vite(['resources/css/admin.css'])
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <div class="brand-mark">DEV</div>
                <div>
                    <h1>Admin Pertanian Presisi Padi</h1>
                    <p>{{ $isEdit ? 'Edit data klimatologi' : 'Input data klimatologi' }}</p>
                </div>
            </div>
            <div class="topbar-actions">
                <span class="admin-name">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="form-page">
        <a href="{{ route('admin.dashboard') }}" class="back-link">&larr; Kembali ke Dashboard</a>

        <section class="form-card">
            <div class="form-header">
                <h2>{{ $isEdit ? 'Edit Data' : 'Tambah Data Baru' }}</h2>
                <p>{{ $isEdit ? 'Perbarui data klimatologi yang sudah tersimpan.' : 'Masukkan data klimatologi harian sesuai periode pengamatan.' }}</p>
            </div>

            @if ($errors->any())
                <div class="form-alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.store') }}">
                @csrf

                @if ($isEdit)
                    <input type="hidden" name="id" value="{{ $data->id }}">
                @endif

                <div class="form-grid">
                    <div class="form-group full">
                        <label for="tanggal">Tanggal <span class="required">*</span></label>
                        <input
                            type="date"
                            id="tanggal"
                            name="tanggal"
                            value="{{ old('tanggal', $isEdit ? $data->tanggal->format('Y-m-d') : '') }}"
                            required
                        >
                        @error('tanggal')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        <div class="hint">Data dengan tanggal yang sama tidak bisa diduplikasi.</div>
                    </div>

                    @foreach ($fields as $field)
                        <div class="form-group">
                            <label for="{{ $field['name'] }}">{{ $field['label'] }} <span class="required">*</span></label>
                            <input
                                type="number"
                                id="{{ $field['name'] }}"
                                name="{{ $field['name'] }}"
                                step="0.1"
                                value="{{ old($field['name'], $isEdit ? $data->{$field['name']} : '') }}"
                                required
                                placeholder="{{ $field['placeholder'] }}"
                            >
                            @error($field['name'])
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                            <div class="hint">{{ $field['unit'] }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        {{ $isEdit ? 'Perbarui Data' : 'Tambah Data' }}
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
