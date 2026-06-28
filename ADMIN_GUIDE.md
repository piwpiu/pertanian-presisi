# Smart Farming Admin Dashboard - Panduan Penggunaan

## 📋 Daftar Isi

1. [Informasi Login](#informasi-login)
2. [Akses Admin Panel](#akses-admin-panel)
3. [Fitur Dashboard Admin](#fitur-dashboard-admin)
4. [Cara Update Data](#cara-update-data)
5. [Validasi Data](#validasi-data)

---

## 🔐 Informasi Login

### Akun Admin Default

- **Email**: `admin@smartfarming.local`
- **Password**: `admin123`

> **⚠️ PENTING**: Ganti password default setelah login pertama kali!

---

## 🚀 Akses Admin Panel

### URL Admin

```
http://127.0.0.1:8000/admin
```

### Langkah Login

1. Buka browser dan akses URL admin di atas
2. Anda akan diarahkan ke halaman login
3. Masukkan email: `admin@smartfarming.local`
4. Masukkan password: `admin123`
5. Klik tombol **Login**
6. Jika berhasil, Anda akan masuk ke Dashboard Admin

---

## 📊 Fitur Dashboard Admin

### 1. **Dashboard Utama**

- Menampilkan tabel daftar data klimatologi yang sudah tersimpan
- Menampilkan 15 data per halaman dengan pagination
- Kolom yang ditampilkan:
    - Tanggal
    - TN (Suhu Minimum)
    - TX (Suhu Maksimum)
    - TAVG (Suhu Rata-rata)
    - RH_AVG (Kelembaban Rata-rata)
    - RR (Curah Hujan)
    - SS (Lama Penyinaran Matahari)
    - Tombol Aksi (Edit/Delete)

### 2. **Tombol Tambah Data**

- Terletak di bagian atas kanan dashboard
- Klik untuk membuka form input data baru

### 3. **Tombol Edit**

- Klik untuk mengubah data yang sudah ada
- Data lama akan dimuat otomatis di form

### 4. **Tombol Delete**

- Klik untuk menghapus data
- Akan ada konfirmasi sebelum data dihapus

---

## ✏️ Cara Update Data

### Tambah Data Baru

1. Dari dashboard, klik tombol **+ Tambah Data**
2. Isi semua field yang tersedia:
    - **Tanggal** _(format: YYYY-MM-DD)_
    - **TN** (Suhu Minimum dalam °C)
    - **TX** (Suhu Maksimum dalam °C)
    - **TAVG** (Suhu Rata-rata dalam °C)
    - **RH_AVG** (Kelembaban Rata-rata dalam %)
    - **RR** (Curah Hujan dalam mm)
    - **SS** (Lama Penyinaran Matahari dalam jam)
3. Klik tombol **Tambah Data**
4. Jika berhasil, data akan ditambahkan dan Anda kembali ke dashboard

### Update Data Existing

1. Di dashboard, temukan data yang ingin diubah
2. Klik tombol **Edit** pada baris data tersebut
3. Form akan terbuka dengan data lama terisi otomatis
4. Ubah field yang perlu diubah
5. Klik tombol **Perbarui Data**
6. Data akan diupdate di database

### Hapus Data

1. Di dashboard, temukan data yang ingin dihapus
2. Klik tombol **Delete**
3. Modal konfirmasi akan muncul
4. Klik **Hapus** untuk mengkonfirmasi penghapusan
5. Data akan dihapus dari database

---

## ✅ Validasi Data

### Validasi Tanggal

- ✅ Format harus: `YYYY-MM-DD` (contoh: 2026-06-01)
- ❌ **Tidak bisa duplikat**: Jika ada data dengan tanggal yang sama, sistem akan menolak
- ✅ Saat update, validasi hanya akan cek tanggal pada data yang berbeda

### Validasi Nilai Numerik

- ✅ Semua field numerik harus berupa angka
- ✅ Bisa menggunakan desimal (contoh: 22.5, 75.3)
- ✅ Setiap field wajib diisi

### Pesan Error

Jika ada kesalahan, Anda akan melihat pesan error:

- `"Data untuk tanggal ini sudah ada di database."` → Tanggal sudah ada
- `"The [field] field is required."` → Field wajib diisi
- `"The [field] field must be a number."` → Field harus berupa angka

---

## 💾 Integrasi dengan Sistem

### Data Website

- Data yang Anda input di admin panel akan langsung digunakan di website utama
- Dashboard website akan menampilkan data yang sudah di-update

### Prediksi LSTM

- Data yang Anda input akan digunakan untuk training/prediksi model LSTM
- Model akan menggunakan data historis dari database

---

## 📝 Catatan Penting

1. **Backup Data**: Sebelum update data, sebaiknya backup database terlebih dahulu
2. **Format Tanggal**: Selalu gunakan format YYYY-MM-DD (tahun-bulan-hari)
3. **Update Harian**: Admin perlu melakukan update setiap hari untuk data yang aktual
4. **Keakuratan Data**: Pastikan data yang diinput sudah akurat sesuai dengan data iklim asli

---

## 🔍 Troubleshooting

### Tidak Bisa Login

- Pastikan email dan password benar
- Periksa apakah email terdaftar sebagai admin

### Tidak Bisa Tambah Data

- Pastikan semua field sudah diisi
- Periksa format tanggal (harus YYYY-MM-DD)
- Periksa apakah tanggal sudah ada di database

### Data Tidak Muncul

- Refresh halaman browser (Ctrl+R)
- Periksa pagination di bawah tabel
- Pastikan database sudah ter-update setelah migration

---

## 📞 Support

Jika ada pertanyaan atau masalah, hubungi administrator sistem.
