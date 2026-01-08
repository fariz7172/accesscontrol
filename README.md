# Laravel Access Control - Complete API Documentation

## 📋 Table of Contents

1. [Overview](#overview)
2. [Quick Start](#quick-start)
3. [Authentication API](#authentication-api)
4. [Attendance Report API](#attendance-report-api)
5. [Open Gate API](#open-gate-api)
6. [Error Codes](#error-codes)
7. [Testing](#testing)
8. [Security](#security)

---

## 🌟 Overview

Laravel Access Control API menyediakan 3 kategori utama:
- **Authentication** - JWT-based login, logout, refresh token
- **Attendance Report** - Laporan kehadiran karyawan dengan filter lengkap
- **Open Gate** - Kontrol akses pintu/gate untuk Soyal dan Tasoft devices

**Base URL:** `http://localhost:8000/api`

**Authentication:** JWT Bearer Token (required untuk semua endpoint kecuali login)

---

## 🚀 Quick Start

### 1. Setup Environment

Tambahkan ke file `.env`:
```env
JWT_SECRET=Rg3QEGIKSWqqj7VBrXP732QsuICE10roCsBRbu3Gg8OPC15oKC4s3ko2VFG1sw3V
JWT_TTL=120
JWT_REFRESH_TTL=10080
```

Clear cache:
```bash
php artisan config:clear
php artisan cache:clear
```

### 2. Login & Get Token

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "123456"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1Qi...",
    "token_type": "bearer",
    "expires_in": 7200,
    "user": {
      "id": 1,
      "username": "admin",
      "priv": "1"
    }
  }
}
```

### 3. Use Token for API Calls

```bash
TOKEN="eyJ0eXAiOiJKV1Qi..."

curl -X POST http://localhost:8000/api/gate/open \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"device_id": 1}'
```

---

## 🔐 Authentication API

### Base URL
```
http://localhost:8000/api/auth
```

### Endpoints

#### 1. Login
**POST** `/api/auth/login`

**Request:**
```json
{
  "username": "admin",
  "password": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1Qi...",
    "token_type": "bearer",
    "expires_in": 7200,
    "user": {
      "id": 1,
      "username": "admin",
      "priv": "1",
      "bactive": {...}
    }
  }
}
```

#### 2. Get User Info
**GET** `/api/auth/me`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "priv": "1",
    "bactive": {...}
  }
}
```

#### 3. Refresh Token
**POST** `/api/auth/refresh`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1Qi...",
    "token_type": "bearer",
    "expires_in": 7200
  }
}
```

#### 4. Logout
**POST** `/api/auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

---

## 📊 Attendance Report API

### Base URL
```
http://localhost:8000/api/attendance
```

### Endpoints

#### 1. Get User Attendance Report
**POST** `/api/attendance/report`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "user_id": [1, 2, 3],
  "start_date": "2026-01-01",
  "end_date": "2026-01-31",
  "include_details": false
}
```

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | array | No* | Array of user IDs |
| `user_name` | string | No* | Search by name (partial match) |
| `start_date` | string | Yes | Format: YYYY-MM-DD |
| `end_date` | string | Yes | Format: YYYY-MM-DD |
| `include_details` | boolean | No | Include daily details (default: false) |

*Note: Minimal satu dari `user_id` atau `user_name` harus diisi

**Response:**
```json
{
  "success": true,
  "data": {
    "period": {
      "start_date": "2026-01-01",
      "end_date": "2026-01-31",
      "total_days": 31
    },
    "summary": {
      "hadir": 20,
      "alpha": 3,
      "telat": 5,
      "ijin": 2,
      "cuti": 1,
      "libur": 8
    },
    "users": [
      {
        "user_id": 1,
        "user_name": "John Doe",
        "department": "IT Department",
        "statistics": {
          "hadir": 20,
          "alpha": 3,
          "telat": 5,
          "ijin": 2,
          "cuti": 1,
          "libur": 8
        }
      }
    ]
  }
}
```

#### 2. Get User List
**GET** `/api/attendance/users`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `search` (optional) - Search by name
- `department_id` (optional) - Filter by department

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "department": "IT Department"
    }
  ]
}
```

#### 3. Get Attendance Statistics
**POST** `/api/attendance/statistics`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "start_date": "2026-01-01",
  "end_date": "2026-01-31",
  "department_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "period": {
      "start_date": "2026-01-01",
      "end_date": "2026-01-31",
      "total_days": 31
    },
    "total_employees": 50,
    "statistics": {
      "hadir": 1200,
      "alpha": 50,
      "telat": 150,
      "ijin": 30,
      "cuti": 20,
      "libur": 400
    },
    "percentage": {
      "hadir": 64.52,
      "alpha": 2.69,
      "telat": 8.06,
      "ijin": 1.61,
      "cuti": 1.08,
      "libur": 21.51
    }
  }
}
```

### Attendance Status Definitions

| Status | Kondisi | Deskripsi |
|--------|---------|-----------|
| **hadir** | `Present = 1` | Karyawan hadir |
| **alpha** | `Present = 0` dan tidak ada keterangan | Tidak hadir tanpa keterangan |
| **telat** | `Present = 1` dan `Time_In > Start_In` | Hadir tapi terlambat |
| **ijin** | Ada `DutyProcessID` (bukan cuti) | Izin sakit, dll |
| **cuti** | Ada `DutyProcessID` (tipe cuti) | Cuti |
| **libur** | `DayType = 2` atau `Remark = 'Holiday'` | Hari libur |

---

## 🚪 Open Gate API

### Base URL
```
http://localhost:8000/api/gate
```

### Endpoint

#### Open Gate/Door
**POST** `/api/gate/open`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body (Option 1 - By Device ID):**
```json
{
  "device_id": 1
}
```

**Request Body (Option 2 - By Serial Number):**
```json
{
  "device_sn": "SOYAL123"
}
```

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `device_id` | integer | No* | ID device dari database |
| `device_sn` | string | No* | Serial number device |

*Note: Minimal satu dari `device_id` atau `device_sn` harus diisi

**Response Success (Soyal - Type 1):**
```json
{
  "success": true,
  "message": "Pintu Main Gate berhasil dibuka",
  "data": {
    "device_id": 4,
    "device_name": "Main Gate",
    "device_type": 1,
    "device_sn": "SOYAL123",
    "ip_address": "192.168.1.100",
    "opened_at": "2026-01-07 11:00:00",
    "response_hex": "7E 06 01 21 84 00 A5 0C"
  }
}
```

**Response Success (Tasoft - Type 0/50):**
```json
{
  "success": true,
  "message": "Pintu Parking Gate berhasil dibuka",
  "data": {
    "device_id": 1,
    "device_name": "Parking Gate",
    "device_type": 50,
    "device_sn": "ZYSL20032920",
    "ip_address": "192.168.1.50",
    "opened_at": "2026-01-07 11:00:00"
  }
}
```

**Response Error:**
```json
{
  "success": false,
  "message": "Device not found",
  "error_code": "DEVICE_NOT_FOUND"
}
```

### Device Types

| Type | Name | Connection Method |
|------|------|-------------------|
| 1 | Soyal | TCP connection to IP:1621 |
| 0 | Tasoft | HTTP POST to external API |
| 50 | Tasoft | HTTP POST to external API |

---

## 📱 Device API

### Base URL
```
http://localhost:8000/api
```

### Endpoints

#### 1. Get Device List
**GET** `/api/devices`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Response Success:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Main Gate",
      "number": "101",
      "flagstatus": "Connect",
      "type": "Soyal",
      "sn": "SOYAL123",
      "ip": "192.168.1.100",
      "nodeid": 1,
      "description": "Pintu Utama",
      "stat": "IN"
    },
    {
      "id": 2,
      "name": "Back Door",
      "number": "102",
      "flagstatus": "Disconnect",
      "type": "Fingerprint",
      "sn": "FINGER001",
      "ip": "192.168.1.101",
      "nodeid": 2,
      "description": "Pintu Belakang",
      "stat": "OUT"
    }
  ]
}
```

---

## ⚠️ Error Codes

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 401 | Unauthorized (Invalid/missing token) |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Internal Server Error |

### Custom Error Codes

| Code | Description |
|------|-------------|
| `DEVICE_NOT_FOUND` | Device tidak ditemukan di database |
| `API_ERROR` | Error dari API eksternal (Tasoft) |
| `CONNECTION_ERROR` | Gagal connect ke device (TCP/HTTP) |
| `NO_RESPONSE` | Tidak ada response dari device (Soyal) |
| `API_URL_NOT_CONFIGURED` | API URL tidak dikonfigurasi |
| `INTERNAL_ERROR` | Internal server error |

---

## 🧪 Testing

### Automated Test Scripts

**Test JWT Authentication:**
```bash
test-jwt-api.bat
```

**Test Attendance API:**
```bash
test-attendance-api.bat
```

**Test Open Gate API:**
```bash
test-open-gate-api.bat
```

### Manual Testing Examples

#### Complete Workflow
```bash
# 1. Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"123456"}'

# 2. Get Attendance Report
TOKEN="eyJ0eXAiOiJKV1Qi..."

curl -X POST http://localhost:8000/api/attendance/report \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": [1],
    "start_date": "2026-01-01",
    "end_date": "2026-01-31"
  }'

# 3. Open Gate
curl -X POST http://localhost:8000/api/gate/open \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"device_id": 1}'
```

---

## 🔒 Security

### Best Practices

1. **HTTPS Only** - Always use HTTPS in production
2. **Token Storage** - Store tokens securely (never in localStorage for sensitive apps)
3. **Token Expiration** - Access token expires after 2 hours
4. **Refresh Token** - Use refresh endpoint before token expires
5. **Logout** - Always logout to invalidate token
6. **Rate Limiting** - Implement rate limiting to prevent abuse
7. **Audit Logging** - All API calls are logged for audit trail
8. **Input Validation** - All inputs are validated before processing
9. **SQL Injection Prevention** - Using Eloquent ORM
10. **Error Messages** - No sensitive data exposed in errors

### Token Configuration

- **Access Token TTL**: 120 minutes (2 hours)
- **Refresh Token TTL**: 10080 minutes (7 days)
- **Algorithm**: HS256
- **Token Type**: Bearer

---

## 💡 Usage Examples

### JavaScript/Fetch API

```javascript
// Login
async function login(username, password) {
  const response = await fetch('http://localhost:8000/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password })
  });
  
  const data = await response.json();
  if (data.success) {
    localStorage.setItem('token', data.data.access_token);
    return data.data;
  }
  throw new Error(data.message);
}

// Get Attendance
async function getAttendance(userId, startDate, endDate) {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://localhost:8000/api/attendance/report', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      user_id: [userId],
      start_date: startDate,
      end_date: endDate
    })
  });
  
  return await response.json();
}

// Open Gate
async function openGate(deviceId) {
  const token = localStorage.getItem('token');
  
  const response = await fetch('http://localhost:8000/api/gate/open', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ device_id: deviceId })
  });
  
  return await response.json();
}
```

### PHP/Guzzle

```php
use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'http://localhost:8000/api/']);

// Login
$response = $client->post('auth/login', [
    'json' => [
        'username' => 'admin',
        'password' => '123456'
    ]
]);

$data = json_decode($response->getBody(), true);
$token = $data['data']['access_token'];

// Open Gate
$response = $client->post('gate/open', [
    'headers' => [
        'Authorization' => "Bearer $token"
    ],
    'json' => [
        'device_id' => 1
    ]
]);
```

### Python/Requests

```python
import requests

base_url = 'http://localhost:8000/api'

# Login
response = requests.post(f'{base_url}/auth/login', json={
    'username': 'admin',
    'password': '123456'
})

data = response.json()
token = data['data']['access_token']

# Get Attendance
headers = {'Authorization': f'Bearer {token}'}
response = requests.post(f'{base_url}/attendance/report', 
    headers=headers,
    json={
        'user_id': [1],
        'start_date': '2026-01-01',
        'end_date': '2026-01-31'
    }
)

print(response.json())
```

---

## 🆘 Troubleshooting

### Common Issues

#### "Unauthenticated"
**Cause:** Token expired atau tidak valid  
**Solution:** Login ulang untuk mendapat token baru

#### "Device not found"
**Cause:** Device ID/SN tidak ada di database  
**Solution:** Check database atau gunakan endpoint `/attendance/users` untuk list devices

#### "Gagal terhubung ke perangkat" (Soyal)
**Cause:** Device offline atau network issue  
**Solution:** 
- Check IP address correct
- Ping device untuk test connectivity
- Check firewall not blocking port 1621

#### "Bad Request - Data tidak valid" (Tasoft)
**Cause:** Device tidak terhubung ke server Tasoft  
**Solution:**
- Check device online
- Verify serial number registered
- Check API URL configured correctly (table `api` id=9)

#### "No users found"
**Cause:** User ID tidak ada atau nama tidak match  
**Solution:** Use `/attendance/users` endpoint untuk get valid user IDs

---

## 📞 Support & Contact

Untuk pertanyaan atau issue, silakan hubungi tim development.

**Version:** 1.0.0  
**Last Updated:** 2026-01-07  
**Laravel Version:** 10.x  
**PHP Version:** 8.x

---

## 📄 License

Copyright © 2026 Soyal Access Control Team. All rights reserved.
