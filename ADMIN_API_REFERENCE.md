# 🔌 Admin API Reference

## Overview

Smart Farming Admin API menyediakan endpoints untuk mengelola data klimatologi melalui web interface. Semua endpoint memerlukan authentication dan admin role.

---

## 🔐 Authentication

### Login

```http
POST /admin/login HTTP/1.1
Host: 127.0.0.1:8000
Content-Type: application/x-www-form-urlencoded

email=admin@smartfarming.local&password=admin123&_token=CSRF_TOKEN
```

**Response Success (302 Redirect)**

```
Location: /admin
Set-Cookie: XSRF-TOKEN=...; Path=/; HttpOnly
Set-Cookie: laravel_session=...; Path=/; HttpOnly
```

**Response Error (302 Redirect)**

```
Location: /admin/login?email=admin@smartfarming.local
Set-Cookie: XSRF-TOKEN=...; Path=/; HttpOnly
Set-Cookie: laravel_session=...; Path=/; HttpOnly
```

### Logout

```http
POST /admin/logout HTTP/1.1
Host: 127.0.0.1:8000
Cookie: laravel_session=...
Content-Type: application/x-www-form-urlencoded

_token=CSRF_TOKEN
```

**Response**

```
HTTP/1.1 302 Found
Location: /
```

---

## 📊 Endpoints

### 1. Get Login Page

```http
GET /admin/login HTTP/1.1
```

**Response**

```
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8

[HTML Login Form]
```

---

### 2. Get Dashboard (Protected)

```http
GET /admin HTTP/1.1
Cookie: laravel_session=...
```

**Response**

```
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8

[HTML Dashboard with Data Table]
```

**Pagination**

```
GET /admin?page=2
```

---

### 3. Get Create Form (Protected)

```http
GET /admin/form HTTP/1.1
Cookie: laravel_session=...
```

**Response**

```
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8

[HTML Form for Create Data]
```

---

### 4. Get Edit Form (Protected)

```http
GET /admin/form/825 HTTP/1.1
Cookie: laravel_session=...
```

**Response**

```
HTTP/1.1 200 OK
Content-Type: text/html; charset=UTF-8

[HTML Form Pre-filled with Data ID 825]
```

---

### 5. Create/Update Data (Protected)

```http
POST /admin/store HTTP/1.1
Content-Type: application/x-www-form-urlencoded
Cookie: laravel_session=...

tanggal=2026-06-02&TN=23.5&TX=33.5&TAVG=28.5&RH_AVG=76.5&RR=11.5&SS=9.5&_token=CSRF_TOKEN
```

**Request Parameters**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| tanggal | date | Yes | Format: YYYY-MM-DD, Unique |
| TN | float | Yes | Suhu Minimum (°C) |
| TX | float | Yes | Suhu Maksimum (°C) |
| TAVG | float | Yes | Suhu Rata-rata (°C) |
| RH_AVG | float | Yes | Kelembaban Rata-rata (%) |
| RR | float | Yes | Curah Hujan (mm) |
| SS | float | Yes | Penyinaran Matahari (jam) |
| id | integer | No | Data ID untuk update (jika kosong = create) |
| \_token | string | Yes | CSRF Token |

**Response Success**

```
HTTP/1.1 302 Found
Location: /admin?page=1
Set-Cookie: XSRF-TOKEN=...; Path=/
```

**Response Validation Error**

```
HTTP/1.1 302 Found
Location: /admin/form
Set-Cookie: XSRF-TOKEN=...; Path=/
```

**Errors:**

- `tanggal` required: "The tanggal field is required."
- `tanggal` duplicate: "Data untuk tanggal ini sudah ada di database."
- `tanggal` format: "The tanggal field must be a valid date."
- `TN` required: "The TN field is required."
- `TN` numeric: "The TN field must be a number."
- Similar for TX, TAVG, RH_AVG, RR, SS

---

### 6. Delete Data (Protected)

```http
DELETE /admin/delete/825 HTTP/1.1
Content-Type: application/x-www-form-urlencoded
Cookie: laravel_session=...

_token=CSRF_TOKEN&_method=DELETE
```

**Response Success**

```
HTTP/1.1 302 Found
Location: /admin
```

**Response Not Found**

```
HTTP/1.1 404 Not Found
```

---

## 🔍 Query Parameters

### Pagination

```
GET /admin?page=1
GET /admin?page=2
GET /admin?page=3
```

**Response** - shows 15 items per page, latest first

---

## 📋 Data Model

### Klimatologi Record

```json
{
    "id": 825,
    "tanggal": "2026-06-01",
    "TN": 22.5,
    "TX": 32.5,
    "TAVG": 27.5,
    "RH_AVG": 75.5,
    "RR": 10.5,
    "SS": 8.5,
    "created_at": "2026-06-01T18:45:30.000000Z",
    "updated_at": "2026-06-01T18:45:30.000000Z"
}
```

### User Record

```json
{
    "id": 1,
    "name": "Administrator",
    "email": "admin@smartfarming.local",
    "role": "admin",
    "created_at": "2026-05-31T18:47:37.000000Z",
    "updated_at": "2026-05-31T18:47:37.000000Z"
}
```

---

## 🛡️ Security Headers

### CSRF Token

Semua form harus include CSRF token:

```html
<form method="POST" action="/admin/store">@csrf ...</form>
```

Token dapat diakses via:

```php
{{ csrf_token() }}
```

### Session Management

- Session ID: `laravel_session` cookie
- CSRF Token: `XSRF-TOKEN` cookie
- Token expiry: 2 hours (default)

---

## 🚨 Error Handling

### 401 Unauthorized

```
HTTP/1.1 302 Found
Location: /admin/login
```

**Cause**: User not authenticated

### 403 Forbidden

```
HTTP/1.1 302 Found
Location: /admin/login
```

**Cause**: User tidak memiliki role admin

### 404 Not Found

```
HTTP/1.1 404 Not Found
```

**Cause**: Resource tidak ditemukan

### 422 Unprocessable Entity

```
HTTP/1.1 302 Found
Location: /admin/form
X-Inertia-Error-Bag: default
```

**Cause**: Validation error

### 500 Internal Server Error

```
HTTP/1.1 500 Internal Server Error
```

**Cause**: Server error, check logs

---

## 📝 Request Examples

### Add New Data (cURL)

```bash
curl -X POST http://127.0.0.1:8000/admin/store \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Cookie: laravel_session=YOUR_SESSION" \
  -d "tanggal=2026-06-02&TN=23.5&TX=33.5&TAVG=28.5&RH_AVG=76.5&RR=11.5&SS=9.5&_token=CSRF_TOKEN"
```

### Add New Data (JavaScript/Fetch)

```javascript
const token = document.querySelector('input[name="_token"]').value;

fetch("/admin/store", {
    method: "POST",
    headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "X-CSRF-TOKEN": token,
    },
    body: new URLSearchParams({
        tanggal: "2026-06-02",
        TN: "23.5",
        TX: "33.5",
        TAVG: "28.5",
        RH_AVG: "76.5",
        RR: "11.5",
        SS: "9.5",
    }),
})
    .then((response) => {
        if (response.ok) {
            window.location.href = "/admin";
        }
    })
    .catch((error) => console.error("Error:", error));
```

### Update Data (cURL)

```bash
curl -X POST http://127.0.0.1:8000/admin/store \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Cookie: laravel_session=YOUR_SESSION" \
  -d "id=825&tanggal=2026-06-01&TN=23.5&TX=33.5&TAVG=28.5&RH_AVG=76.5&RR=11.5&SS=9.5&_token=CSRF_TOKEN"
```

### Delete Data (cURL)

```bash
curl -X DELETE http://127.0.0.1:8000/admin/delete/825 \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Cookie: laravel_session=YOUR_SESSION" \
  -d "_token=CSRF_TOKEN"
```

---

## 🔄 Response Redirects

### After Successful Create

```
POST /admin/store
↓
Redirect to: /admin (dashboard view data)
Message: "Data berhasil ditambahkan."
```

### After Successful Update

```
POST /admin/store (with id)
↓
Redirect to: /admin
Message: "Data berhasil diperbarui."
```

### After Successful Delete

```
DELETE /admin/delete/{id}
↓
Redirect to: /admin
Message: "Data berhasil dihapus."
```

### After Login

```
POST /admin/login (success)
↓
Redirect to: /admin (dashboard)
```

### After Logout

```
POST /admin/logout
↓
Redirect to: / (homepage)
```

---

## 📊 Data Constraints

| Field  | Min | Max | Unit | Decimal |
| ------ | --- | --- | ---- | ------- |
| TN     | -10 | 50  | °C   | 1       |
| TX     | 0   | 50  | °C   | 1       |
| TAVG   | -5  | 50  | °C   | 1       |
| RH_AVG | 0   | 100 | %    | 1       |
| RR     | 0   | 500 | mm   | 1       |
| SS     | 0   | 24  | jam  | 1       |

---

## 🎯 Status Codes

| Code | Meaning          | Example                   |
| ---- | ---------------- | ------------------------- |
| 200  | OK               | GET /admin/form           |
| 302  | Redirect         | POST /admin/store success |
| 401  | Unauthorized     | Not authenticated         |
| 403  | Forbidden        | Not admin role            |
| 404  | Not Found        | Invalid record ID         |
| 422  | Validation Error | Invalid data format       |
| 500  | Server Error     | Database error            |

---

## 💡 Integration Tips

1. **CSRF Protection**: Always include `_token` in POST/DELETE requests
2. **Session Cookie**: Maintain `laravel_session` cookie between requests
3. **Date Format**: Always use YYYY-MM-DD format
4. **Numeric Format**: Use decimal notation (23.5, not 23,5)
5. **Error Handling**: Check redirect URL for success/failure
6. **Timeout**: Session timeout is 2 hours

---

## 📞 Support

For API questions or issues:

- Check `ADMIN_TECHNICAL.md` for implementation details
- Check Laravel logs in `storage/logs/laravel.log`
- Contact development team

---

**Version**: 1.0
**Last Updated**: 1 Juni 2026
