<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Pertanian Presisi Padi</title>
    @vite(['resources/css/admin.css'])
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <div class="brand-mark">DEV</div>
                <div>
                    <h1>Admin Pertanian Presisi Padi</h1>
                    <p>Manajemen data klimatologi</p>
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

    <main class="page">
        <section class="page-header">
            <div>
                <div class="eyebrow">Dashboard Admin</div>
                <h2 class="page-title">Data Klimatologi Kota Bogor</h2>
                <p class="page-subtitle">Kelola data harian agar proses pemeriksaan dan pembaruan data lebih terarah.</p>
                {{-- <p class="page-subtitle">Tempat mengambil data : 
                    <a href="https://dataonline.bmkg.go.id/" target="_blank" rel="noopener noreferrer">https://dataonline.bmkg.go.id/</a>
                </p> --}}
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.form') }}" class="btn-primary">Tambah Data</a>

                <form method="POST" action="{{ route('admin.generate-prediksi') }}"
                    onsubmit="return confirm('Generate ulang prediksi akan mengosongkan dan memperbarui tabel prediksi. Lanjutkan?')">
                    @csrf
                    <button type="submit" class="btn-secondary">
                        Generate Prediksi
                    </button>
                </form>
            </div>
        </section>

        @if (session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif
        
        @if (session('error'))
            <div class="alert" style="background: #fee2e2; color: #991b1b;">
                {{ session('error') }}
            </div>
        @endif

        <section class="overview-grid" aria-label="Ringkasan data">
            <article class="summary-card">
                <div class="summary-label">Total Data</div>
                <div class="summary-value">{{ number_format($summary['total']) }} Data</div>
                <div class="summary-note"> Sumber data :
                    <a href="https://dataonline.bmkg.go.id/data-harian" target="_blank" rel="noopener noreferrer">https://dataonline.bmkg.go.id/</a></div>
            </article>

            <article class="summary-card">
                <div class="summary-label">Periode Data</div>
                <div class="summary-value">{{ $summary['period_label'] }}</div>
                <div class="summary-note">Tersedia {{ number_format($summary['selected_total']) }} data pada periode ini</div>
            </article>

            <article class="missing-card">
                <div class="missing-header">
                    <div>
                        <div class="missing-title">Tanggal yang belum diinput</div>
                        <div class="missing-subtitle">Pada periode {{ $summary['period_label'] }}</div>
                    </div>
                    <span class="missing-count">{{ $summary['missing_count'] }} tanggal</span>
                </div>

                @if ($summary['missing_count'] > 0)
                    <div class="missing-list">
                        @foreach ($summary['missing_dates'] as $missingDate)
                            <span class="date-chip">{{ $missingDate->format('d M Y') }}</span>
                        @endforeach
                    </div>
                @elseif (! $summary['missing_period_end'])
                    <div class="complete-state">Periode ini belum berjalan.</div>
                @else
                    <div class="complete-state">Semua tanggal pada periode ini sudah memiliki data.</div>
                @endif
            </article>
        </section>

        <section class="table-panel">
            <div class="table-toolbar">
                <div>
                    <h2>Daftar Data Bulan {{ $summary['selected_month_label'] }}</h2>
                    <p>Menampilkan {{ $data->count() }} dari {{ number_format($data->total()) }} data.</p>
                </div>

                <form method="GET" action="{{ route('admin.dashboard') }}" class="filter-form">
                    <div class="filter-field">
                        <label for="month">Bulan</label>
                        <select id="month" name="month">
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}" @selected($selectedMonth === $month)>
                                    {{ Carbon\Carbon::create(null, $month, 1)->locale('id')->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-field">
                        <label for="year">Tahun</label>
                        <select id="year" name="year">
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn-primary">Tampilkan</button>
                    <a href="{{ route('admin.form') }}" class="btn-secondary">Input Data Baru</a>
                </form>
            </div>

            @if ($data->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>TN</th>
                            <th>TX</th>
                            <th>TAVG</th>
                            <th>RH_AVG</th>
                            <th>RR</th>
                            <th>SS</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $row)
                            <tr>
                                <td data-label="Tanggal">{{ Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                                <td data-label="TN">{{ $row->TN ?? '-' }}</td>
                                <td data-label="TX">{{ $row->TX ?? '-' }}</td>
                                <td data-label="TAVG">{{ $row->TAVG ?? '-' }}</td>
                                <td data-label="RH_AVG">{{ $row->RH_AVG ?? '-' }}</td>
                                <td data-label="RR">{{ $row->RR ?? '-' }}</td>
                                <td data-label="SS">{{ $row->SS ?? '-' }}</td>
                                <td data-label="Aksi">
                                    <div class="actions">
                                        <a href="{{ route('admin.form', $row->id) }}" class="btn-secondary">Edit</a>
                                        <button type="button" class="btn-danger" onclick="openDeleteModal({{ $row->id }})">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <p>Belum ada data klimatologi pada periode ini.</p>
                </div>
            @endif
        </section>
    </main>

    <div id="deleteModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="deleteTitle">
        <div class="modal-content">
            <div class="modal-header" id="deleteTitle">Hapus Data</div>
            <div class="modal-body">Data yang dihapus tidak dapat dikembalikan. Pastikan data ini memang tidak digunakan lagi.</div>
            <div class="modal-footer">
                <button type="button" class="btn-ghost" onclick="closeDeleteModal()">Batal</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(id) {
            document.getElementById('deleteForm').action = `/admin/delete/${id}`;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        document.getElementById('deleteModal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>
