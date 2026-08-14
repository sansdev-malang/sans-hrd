# 🧪 Testing API Integration

Panduan testing untuk memverifikasi koneksi antara Unit Apps (SD/SMP/PAUD) dengan HRD Central System.

---

## ✅ Pre-Testing Checklist

Sebelum memulai testing, pastikan:

- [ ] Ketiga unit apps sudah update .env dengan HRD_API_TOKEN dan SCHOOL_UNIT_ID
- [ ] Database HRD sudah ada data SchoolUnit (SD, SMP, PAUD)
- [ ] API tokens di database HRD sudah sesuai dengan HRD_API_TOKEN di .env unit apps
- [ ] Semua unit aktif (is_active = 1) di database HRD

---

## 📋 Test Plan

### Phase 1: Verify Configuration (No Authentication)

Endpoint testing tanpa login untuk check database & config:

**Test 1.1: Lihat daftar School Units**
```
GET http://sans-hrd.test/test/school-units
```

Expected Response:
```json
{
    "status": "success",
    "message": "School units configuration",
    "data": [
        {
            "id": 2,
            "name": "SD",
            "api_token": "rahasia_sd_123",
            "is_active": 1
        },
        {
            "id": 3,
            "name": "SMP",
            "api_token": "rahasia_smp_123",
            "is_active": 1
        },
        {
            "id": 1,
            "name": "PAUD",
            "api_token": "rahasia_paud_123",
            "is_active": 1
        }
    ]
}
```

**Test 1.2: Lihat semua API tokens (debugging)**
```
GET http://sans-hrd.test/test/tokens
```

**Test 1.3: Check migration status**
```
GET http://sans-hrd.test/test/migration-check
```

Expected: Pastikan unique constraint dan indexes sudah di-apply (jika belum, jalankan `php artisan migrate` di HRD)

---

### Phase 2: Test Token Validation

Test dengan mengirim request dengan header yang benar/salah:

**Test 2.1: Test dengan token BENAR (SD)**
```bash
curl -X GET "http://sans-hrd.test/test/test-attendance-matrix?school_unit_id=2&month=2026-08" \
  -H "X-API-TOKEN: rahasia_sd_123"
```

Expected Response (200 OK):
```json
{
    "status": "success",
    "message": "API token validation passed!",
    "data": {
        "unit_id": 2,
        "unit_name": "SD",
        "api_url": "http://sans-sd.test/api/v1/hrd",
        "is_active": 1,
        "month_requested": "2026-08"
    }
}
```

**Test 2.2: Test TANPA token**
```bash
curl -X GET "http://sans-hrd.test/test/test-attendance-matrix?school_unit_id=2&month=2026-08"
```

Expected Response (401 Unauthorized):
```json
{
    "status": "error",
    "message": "Missing X-API-TOKEN header"
}
```

**Test 2.3: Test dengan token SALAH**
```bash
curl -X GET "http://sans-hrd.test/test/test-attendance-matrix?school_unit_id=2&month=2026-08" \
  -H "X-API-TOKEN: token_salah_123"
```

Expected Response (401 Unauthorized):
```json
{
    "status": "error",
    "message": "Invalid API token. Expected: 'rahasia_sd_123', Got: 'token_salah_123'"
}
```

**Test 2.4: Test dengan school_unit_id SALAH**
```bash
curl -X GET "http://sans-hrd.test/test/test-attendance-matrix?school_unit_id=999&month=2026-08" \
  -H "X-API-TOKEN: rahasia_sd_123"
```

Expected Response (404 Not Found):
```json
{
    "status": "error",
    "message": "School unit 999 not found"
}
```

---

### Phase 3: Test Actual API Endpoints

Setelah Phase 1 & 2 berhasil, test endpoint asli yang dipakai unit apps:

**Test 3.1: Attendance Matrix dari Sans-SD**
```bash
curl -X GET "http://sans-hrd.test/api/attendance-matrix?school_unit_id=2&month=2026-08" \
  -H "X-API-TOKEN: rahasia_sd_123"
```

Expected: 200 OK dengan data attendance

**Test 3.2: Bonus Reports dari Sans-SMP**
```bash
curl -X GET "http://sans-hrd.test/api/bonus-reports?school_unit_id=3&month=2026-08" \
  -H "X-API-TOKEN: rahasia_smp_123"
```

Expected: 200 OK dengan data bonus reports

**Test 3.3: Payslips dari Sans-PAUD**
```bash
curl -X GET "http://sans-hrd.test/api/payslips?school_unit_id=1&month=2026-08" \
  -H "X-API-TOKEN: rahasia_paud_123"
```

Expected: 200 OK dengan data payslips

---

## 🔧 Troubleshooting

### Error: "Invalid API token"
- Cek database HRD tabel `school_units`, pastikan `api_token` sesuai dengan `HRD_API_TOKEN` di .env unit app
- Pastikan tidak ada whitespace atau spasi tambahan

### Error: "Missing X-API-TOKEN header"
- Header harus dikirim dengan format: `X-API-TOKEN: <token>`
- Perhatikan besar-kecil huruf

### Error: "School unit not found"
- Cek `school_unit_id` parameter di request, pastikan sesuai dengan `id` di database
- Reminder: SD=2, SMP=3, PAUD=1

### Error: "School unit is inactive"
- Cek kolom `is_active` di database, pastikan bernilai 1

### Response time lambat
- Sudah jalankan migrations? Indexes akan mempercepat queries
- Cek rate limiting: 60 requests per minute (gunakan header rate limit di response)

---

## 📝 Testing dengan Postman/Insomnia

1. Import testing endpoints ke Postman
2. Buat collection dengan 3 environments (SD, SMP, PAUD)
3. Setiap environment set variable:
   - `base_url`: http://sans-hrd.test
   - `api_token`: rahasia_sd_123 (untuk SD), dst.
   - `school_unit_id`: 2 (untuk SD), dst.

---

## ✅ Sign-off Criteria (Testing Complete)

Semua test di Phase 1, 2, 3 harus PASS:
- [ ] Phase 1: Configuration verified
- [ ] Phase 2: Token validation working
- [ ] Phase 3: Actual API endpoints returning data
- [ ] Logs in HRD show successful requests
- [ ] Logs in Unit Apps show successful data retrieval
- [ ] No 401/403/404 errors di production requests

---

**Next Steps After Testing:**
1. Run migrations on HRD: `php artisan migrate`
2. If all tests pass, ready for production deployment
3. Monitor logs for any integration issues
4. Remove `/test/*` routes before going live (comment out in test-api.php)
