# Smart Farming Admin System - Dokumentasi Teknis

## 📚 Daftar Isi

1. [Arsitektur Sistem](#arsitektur-sistem)
2. [Database Schema](#database-schema)
3. [File Structure](#file-structure)
4. [Authentication Flow](#authentication-flow)
5. [API Endpoints](#api-endpoints)
6. [Validasi Data](#validasi-data)
7. [Troubleshooting](#troubleshooting)

---

## 🏗️ Arsitektur Sistem

### Komponen Utama

```
Admin System
├── Authentication (Login/Logout)
├── Authorization (Role-based access)
├── Dashboard (Data viewing & management)
├── CRUD Operations
│   ├── Create (Tambah data)
│   ├── Read (Lihat data)
│   ├── Update (Edit data)
│   └── Delete (Hapus data)
└── Database
    └── Klimatologi table
```

### Security Flow

```
User Request
    ↓
Check Authentication (Session)
    ↓
Check Authorization (Role = 'admin')
    ↓
Process Request (Controller)
    ↓
Validate Data
    ↓
Update Database
    ↓
Response
```

---

## 📊 Database Schema

### Users Table (Updated)

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255),
    role VARCHAR(255) DEFAULT 'user',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Roles:**

- `admin` - Dapat mengakses admin panel
- `user` - User regular (tidak bisa akses admin panel)

### Klimatologi Table (Updated)

```sql
CREATE TABLE klimatologi (
    id BIGINT PRIMARY KEY,
    tanggal DATE,
    TN FLOAT,                    -- Suhu Minimum
    TX FLOAT,                    -- Suhu Maksimum
    TAVG FLOAT,                  -- Suhu Rata-rata
    RH_AVG FLOAT,                -- Kelembaban Rata-rata
    RR FLOAT,                    -- Curah Hujan
    SS FLOAT,                    -- Lama Penyinaran Matahari
    suhu FLOAT NULL,             -- Backward compatibility
    curah_hujan FLOAT NULL,      -- Backward compatibility
    kelembaban FLOAT NULL,       -- Backward compatibility
    radiasi FLOAT NULL,
    data_json JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_tanggal (tanggal)
);
```

**Column Units:**
| Column | Unit | Type | Range |
|--------|------|------|-------|
| TN | °C | Numeric | -10 to 50 |
| TX | °C | Numeric | 0 to 50 |
| TAVG | °C | Numeric | -5 to 50 |
| RH_AVG | % | Numeric | 0 to 100 |
| RR | mm | Numeric | 0 to 500 |
| SS | jam | Numeric | 0 to 24 |

---

## 📁 File Structure

### Controllers

```
app/Http/Controllers/
├── AdminController.php         # Admin business logic
└── DashboardController.php     # Main app dashboard
```

### Middleware

```
app/Http/Middleware/
└── AdminMiddleware.php         # Check admin role
```

### Models

```
app/Models/
├── User.php                    # Updated with role
├── Klimatologi.php             # Updated with new columns
└── Prediksi.php
```

### Routes

```
routes/
└── web.php                     # Admin routes defined
```

### Views

```
resources/views/
├── admin/
│   ├── login.blade.php         # Login page
│   ├── dashboard.blade.php     # Data table
│   └── form.blade.php          # Create/Edit form
├── dashboard.blade.php
└── welcome.blade.php
```

### Migrations

```
database/migrations/
├── 2026_06_01_000000_add_role_to_users_table.php
└── 2026_06_01_000001_update_klimatologi_table.php
```

### Seeders

```
database/seeders/
├── AdminSeeder.php             # Create admin user
└── DatabaseSeeder.php
```

---

## 🔐 Authentication Flow

### Login Process

1. User membuka `/admin/login`
2. User input email dan password
3. Laravel authenticate credentials
4. Check apakah user role = 'admin'
5. Jika admin → set session dan redirect ke dashboard
6. Jika bukan admin → reject login
7. Jika kredensial salah → show error

### Session Management

- Session disimpan di `storage/framework/sessions/`
- Session expired: default 2 jam (configurable di `config/session.php`)
- CSRF token required untuk form submission

### Logout Process

1. Invalidate session
2. Regenerate token
3. Redirect ke homepage

---

## 🔗 API Endpoints

### Authentication Endpoints

```
GET  /admin/login                    # Show login form
POST /admin/login                    # Handle login
POST /admin/logout                   # Handle logout
```

### Data Management Endpoints

```
GET  /admin                          # Dashboard (protected)
GET  /admin/form                     # Show create form (protected)
GET  /admin/form/{id}                # Show edit form (protected)
POST /admin/store                    # Store/Update data (protected)
DELETE /admin/delete/{id}            # Delete data (protected)
```

### Request/Response Examples

#### Login Request

```http
POST /admin/login HTTP/1.1
Content-Type: application/x-www-form-urlencoded

email=admin@smartfarming.local&password=admin123
```

#### Add Data Request

```http
POST /admin/store HTTP/1.1
Content-Type: application/x-www-form-urlencoded

tanggal=2026-06-01&TN=22.5&TX=32.5&TAVG=27.5&RH_AVG=75.5&RR=10.5&SS=8.5
```

#### Response Success

```json
{
    "status": "success",
    "message": "Data berhasil ditambahkan.",
    "data": {
        "id": 825,
        "tanggal": "2026-06-01",
        "TN": 22.5,
        "TX": 32.5,
        "TAVG": 27.5,
        "RH_AVG": 75.5,
        "RR": 10.5,
        "SS": 8.5
    }
}
```

---

## ✅ Validasi Data

### Server-side Validation (AdminController)

```php
$validated = $request->validate([
    'tanggal' => 'required|date',
    'TN' => 'required|numeric',
    'TX' => 'required|numeric',
    'TAVG' => 'required|numeric',
    'RH_AVG' => 'required|numeric',
    'RR' => 'required|numeric',
    'SS' => 'required|numeric',
]);
```

### Business Logic Validation

1. **Tanggal Duplikat**: Check apakah tanggal sudah ada di database
    - Exception: Saat update, tanggal lama diizinkan
    - Error message: "Data untuk tanggal ini sudah ada di database."

2. **Nilai Numerik**: Semua field harus angka valid
    - Bisa desimal (0.1 precision)
    - Error message: "The [field] field must be a number."

3. **Required Fields**: Semua field wajib diisi
    - Error message: "The [field] field is required."

### Client-side Validation (HTML5)

```html
<input type="date" required /> <input type="number" required step="0.1" />
```

---

## 🔧 Configuration

### Auth Config (`config/auth.php`)

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],
```

### Middleware Alias (`bootstrap/app.php`)

```php
$middleware->alias([
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
]);
```

### Route Protection

```php
Route::middleware('admin')->group(function () {
    // Protected routes
});
```

---

## 🐛 Troubleshooting

### 1. Login Gagal

**Symptom**: "Email atau password salah."

- Pastikan email dan password benar
- Check user role di database: `SELECT * FROM users WHERE email='admin@smartfarming.local'`
- Pastikan user sudah di-seed: `php artisan db:seed --class=AdminSeeder`

### 2. Tidak Bisa Akses Dashboard

**Symptom**: Redirect ke login page meskipun sudah login

- Check session: `php artisan tinker` → `Auth::user()`
- Pastikan middleware 'admin' terdaftar di bootstrap/app.php
- Check browser cookies/session

### 3. Tidak Bisa Tambah Data

**Symptom**: Form error atau data tidak muncul

- Check form validation errors di page
- Verify database migration sudah jalan: `php artisan migrate:status`
- Check database connection di `.env`

### 4. Tanggal Duplikat Error

**Symptom**: "Data untuk tanggal ini sudah ada di database."

- Check apakah data dengan tanggal tersebut sudah ada:
    ```sql
    SELECT * FROM klimatologi WHERE tanggal='2026-06-01';
    ```
- Saat update, pastikan hanya field yang diubah, bukan tanggal yang duplikat

### 5. Halaman Blank/Error

**Symptom**: 500 Internal Server Error

- Check logs: `storage/logs/laravel.log`
- Verify database connection
- Restart PHP server

### Debug Commands

```bash
# Check migrations
php artisan migrate:status

# Seed admin user
php artisan db:seed --class=AdminSeeder

# Check users
php artisan tinker
> User::all()

# Clear cache
php artisan cache:clear
php artisan config:clear
```

---

## 📋 Deployment Checklist

- [ ] Run migrations: `php artisan migrate`
- [ ] Create admin user: `php artisan db:seed --class=AdminSeeder`
- [ ] Change default password di database
- [ ] Test login dengan credentials baru
- [ ] Test CRUD operations
- [ ] Check database backup
- [ ] Verify CSRF token in forms
- [ ] Test with different browsers
- [ ] Monitor logs for errors

---

## 📞 Support & Maintenance

### Regular Tasks

1. **Monitor**: Check logs regularly
2. **Backup**: Backup database daily
3. **Update**: Keep Laravel packages updated
4. **Security**: Change admin password regularly
5. **Test**: Test new migrations di staging dulu

### Performance Optimization

- Add database indexes untuk field `tanggal`
- Use pagination untuk large datasets
- Cache frequently accessed data
- Monitor query performance

---

## 📚 Additional Resources

- [Laravel Authentication](https://laravel.com/docs/authentication)
- [Laravel Middleware](https://laravel.com/docs/middleware)
- [Laravel Validation](https://laravel.com/docs/validation)
- [Blade Templates](https://laravel.com/docs/blade)
