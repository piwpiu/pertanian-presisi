# Smart Farming Admin - Quick Start Guide

## 🚀 Memulai Dengan Cepat

### 1. Setup Database

```bash
cd d:\laragon\www\smart-farming

# Run migrations
php artisan migrate

# Seed admin user
php artisan db:seed --class=AdminSeeder
```

### 2. Akses Admin Panel

```
URL: http://127.0.0.1:8000/admin
Email: admin@smartfarming.local
Password: admin123
```

### 3. Alur Kerja Admin Harian

#### Tambah Data Baru

1. Buka http://127.0.0.1:8000/admin
2. Login dengan credentials admin
3. Klik tombol "+ Tambah Data"
4. Isi form dengan data hari ini:
    - Tanggal (format: YYYY-MM-DD)
    - TN (suhu minimum) → °C
    - TX (suhu maksimum) → °C
    - TAVG (suhu rata-rata) → °C
    - RH_AVG (kelembaban) → %
    - RR (curah hujan) → mm
    - SS (penyinaran matahari) → jam
5. Klik "Tambah Data"

#### Edit Data Existing

1. Di dashboard, temukan data yang perlu diubah
2. Klik tombol "Edit"
3. Ubah field yang diperlukan
4. Klik "Perbarui Data"

#### Hapus Data

1. Di dashboard, temukan data yang akan dihapus
2. Klik tombol "Delete"
3. Konfirmasi di modal
4. Data akan dihapus

---

## 📊 Data yang Digunakan Website dan LSTM

Data yang Anda input di admin panel akan digunakan untuk:

1. **Website Dashboard**
    - Menampilkan grafik data klimatologi mingguan
    - Fallback jika API prediksi gagal

2. **Model LSTM**
    - Training data untuk prediksi
    - Feature engineering untuk model
    - Historical data untuk validation

3. **Prediksi LSTM**
    - Input untuk model prediksi
    - Update database dengan prediksi baru

---

## 🔑 Credentials & Access

### Default Admin User

```
Email: admin@smartfarming.local
Password: admin123
```

### Ganti Password Admin

1. Buka database admin
2. Update password hash menggunakan Laravel:
    ```bash
    php artisan tinker
    > $user = User::where('email', 'admin@smartfarming.local')->first();
    > $user->password = Hash::make('password_baru');
    > $user->save();
    ```

---

## 📱 UI/UX Fitur

### Login Page

- Email input dengan validation
- Password input (terenkripsi)
- Error message yang jelas
- Link ke halaman utama

### Dashboard

- Tabel data dengan sorting
- Pagination (15 data per page)
- Tombol Edit dan Delete
- Success/Error messages
- Logout button

### Form Input/Edit

- 7 input field untuk klimatologi
- Validasi real-time (HTML5)
- Hint untuk setiap field
- Tombol Batal dan Submit
- Back link ke dashboard

---

## ✅ Testing Checklist

### Test Login

- [ ] Login dengan email benar, password salah → Error
- [ ] Login dengan email salah → Error
- [ ] Login dengan credentials benar → Success
- [ ] User non-admin → Denied
- [ ] Session timeout → Logout otomatis

### Test Add Data

- [ ] Submit dengan semua field → Success
- [ ] Submit dengan field kosong → Error
- [ ] Submit dengan tanggal duplikat → Error
- [ ] Submit dengan nilai non-numeric → Error
- [ ] Data muncul di dashboard → Verify

### Test Edit Data

- [ ] Buka form edit → Data terisi
- [ ] Edit satu field → Update success
- [ ] Edit tanggal menjadi duplikat → Error
- [ ] Data updated di tabel → Verify

### Test Delete Data

- [ ] Klik delete → Modal muncul
- [ ] Cancel delete → Modal close
- [ ] Confirm delete → Data hilang dari tabel

---

## 🎯 Common Tasks

### Lihat Semua Data

```bash
php artisan tinker
> Klimatologi::all()
```

### Cek User Role

```bash
php artisan tinker
> User::where('role', 'admin')->get()
```

### Reset Data

```bash
php artisan migrate:refresh --seed
```

### Export Data

```sql
SELECT * FROM klimatologi
ORDER BY tanggal DESC;
```

---

## 📋 File-file Penting

| File                                       | Fungsi            |
| ------------------------------------------ | ----------------- |
| `app/Http/Controllers/AdminController.php` | Controller logic  |
| `app/Http/Middleware/AdminMiddleware.php`  | Role checking     |
| `routes/web.php`                           | Route definitions |
| `resources/views/admin/`                   | UI templates      |
| `database/migrations/2026_06_01_*`         | Schema changes    |
| `database/seeders/AdminSeeder.php`         | Initial data      |

---

## 🔐 Security Notes

1. **CSRF Protection**: Semua form dilengkapi CSRF token
2. **Password Hashing**: Password di-hash dengan bcrypt
3. **SQL Injection**: Menggunakan parameter binding
4. **XSS Protection**: Input di-escape dalam templates
5. **Authorization**: Middleware check role untuk setiap route

---

## 📞 Troubleshooting Cepat

| Problem              | Solution                            |
| -------------------- | ----------------------------------- |
| Login gagal          | Verify credentials di database      |
| Data tidak muncul    | Check database dan migration status |
| Form tidak submit    | Check browser console untuk error   |
| Tanggal format error | Gunakan YYYY-MM-DD format           |
| Session timeout      | Login ulang                         |

---

## 📞 Kontak & Support

Jika ada pertanyaan atau masalah:

1. Check file dokumentasi: `ADMIN_GUIDE.md` dan `ADMIN_TECHNICAL.md`
2. Check logs: `storage/logs/laravel.log`
3. Hubungi developer sistem

---

**Last Updated**: 1 Juni 2026
**Version**: 1.0
