# 📢 SANS-HRD API Security Update - Unit Apps Action Required

## ⚠️ BREAKING CHANGE
All unit apps (SD/SMP/PAUD) must update their HRD API integration code!

**Deadline**: Before deploying HRD security update to production  
**Affected Apps**: sans-sd, sans-smp, sans-paud

---

## 🔧 What Changed on HRD

HRD now requires authentication for ALL API endpoints:

### New Requirement
```
Every HTTP request to HRD /api/* must include:
- Header: X-API-TOKEN
- Parameter: school_unit_id
```

### Rate Limiting
```
Limit: 60 requests per 1 minute per IP
Excess: 429 Too Many Requests
```

---

## 📝 Update Your Unit App

### Step 1: Add Configuration to `.env`

```env
# Add this to your .env file
HRD_API_TOKEN=your_token_from_hrd_admin
HRD_API_URL=http://sans-hrd.local  # Already exists
SCHOOL_UNIT_ID=2  # or 3 for SMP, 4 for PAUD
```

### Step 2: Update API Calls in Models

#### Before (Old - No Longer Works)
```php
// ❌ This will now fail with 401 Unauthorized
Http::get(rtrim($hrdUrl, '/') . '/api/attendance-matrix', [
    'month' => $month,
    'unit_id' => strtolower($schoolUnit)
]);
```

#### After (New - Required)
```php
// ✅ This is the correct format
Http::withHeaders([
    'X-API-TOKEN' => config('app.hrd_api_token')
])->get(rtrim($hrdUrl, '/') . '/api/attendance-matrix', [
    'school_unit_id' => config('app.school_unit_id'),  // CRITICAL!
    'month' => $month
]);
```

### Step 3: Find & Update All HRD API Calls

**Files to check** (in each unit app):
- `app/Models/LeaveRequest.php` - syncToCentral(), deleteFromCentral()
- `routes/web.php` - dashboard attendan ce/bonus reports
- `app/Http/Controllers/Api/HrdApiController.php` - if exists
- Any other file calling `Http::get()` or `Http::post()` to HRD

**Pattern to update**:
```php
// FIND:
$response = Http::timeout(15)->get(rtrim($hrdUrl, '/') . '/api/...')

// REPLACE WITH:
$response = Http::withHeaders([
    'X-API-TOKEN' => env('HRD_API_TOKEN')
])->timeout(15)->get(rtrim($hrdUrl, '/') . '/api/...', [
    'school_unit_id' => config('app.school_unit_id'),
    ...other params
])
```

### Step 4: Update sync/leave-request Endpoint

**In LeaveRequest.php model**:

#### Before
```php
Http::timeout(5)->post(rtrim($hrdUrl, '/') . '/api/sync/leave-request', [
    'school_unit_id' => $schoolUnitId,
    'remote_leave_id' => $this->id,
    // ... other fields
]);
```

#### After
```php
Http::withHeaders([
    'X-API-TOKEN' => env('HRD_API_TOKEN')
])->timeout(5)->post(rtrim($hrdUrl, '/') . '/api/sync/leave-request', [
    'school_unit_id' => config('app.school_unit_id'),  // Must match config
    'remote_leave_id' => $this->id,
    // ... other fields
]);
```

### Step 5: Test Before Production

```bash
# Test 1: Leave sync
php artisan tinker
$leave = App\Models\LeaveRequest::find(1);
$leave->syncToCentral();  // Should succeed without error

# Test 2: Check HRD logs
# - SSH to HRD server
# - tail -f storage/logs/laravel.log
# - Should see successful sync logs, NO 401 errors

# Test 3: Check response
# Should see: "Leave request synced successfully"
# NOT: "Unauthorized" or "Invalid API token"
```

---

## 📋 COMPLETE CHECKLIST

For each unit app (SD/SMP/PAUD):

- [ ] Add `HRD_API_TOKEN` and `SCHOOL_UNIT_ID` to `.env`
- [ ] Find all `Http::*` calls to HRD in codebase
- [ ] Add `withHeaders(['X-API-TOKEN' => ...])` to each
- [ ] Add `school_unit_id` parameter to each request
- [ ] Test in staging environment
- [ ] Verify logs show successful sync, no 401 errors
- [ ] Deploy to production together with HRD
- [ ] Monitor first 24 hours for errors
- [ ] Document any API changes needed

---

## 🔍 Search Commands

### Find all HRD API calls
```bash
# Search for patterns
grep -r "Http::.*get\|Http::.*post" app/ routes/ --include="*.php" | grep -i hrd

# Or more specific
grep -r "rtrim.*hrdUrl" app/ --include="*.php"
```

### Find without X-API-TOKEN
```bash
# These calls need updating
grep -r "Http::.*get\|Http::.*post" app/ --include="*.php" | grep -v "withHeaders"
```

---

## 🚨 Common Issues

### "401 Unauthorized"
```
Problem: Token missing or invalid
Solution: 
1. Check HRD_API_TOKEN in .env
2. Compare with HRD app's SchoolUnit.api_token
3. Ensure withHeaders(['X-API-TOKEN' => ...]) is present
```

### "Missing school_unit_id parameter"
```
Problem: Parameter not sent in request
Solution:
1. Add 'school_unit_id' => config('app.school_unit_id') to query params
2. Verify SCHOOL_UNIT_ID in .env matches HRD database
```

### "School unit not found"
```
Problem: school_unit_id value doesn't exist in HRD
Solution:
1. Log into HRD admin
2. Check SchoolUnits table: SELECT * FROM school_units
3. Update .env SCHOOL_UNIT_ID to correct value
```

### "429 Too Many Requests"
```
Problem: Exceeded 60 requests/minute rate limit
Solution:
1. Optimize API calls (batch requests if possible)
2. Cache results (use Redis/File cache for frequently called data)
3. Reduce polling frequency
```

---

## 📞 Support

If your unit app breaks after this HRD update:

1. **Check HRD logs** for error details
   ```
   ssh hrd@sans-hrd.local
   tail -f /var/www/sans-hrd/storage/logs/laravel.log
   ```

2. **Test API manually**
   ```bash
   curl -H "X-API-TOKEN: your_token" \
     "http://sans-hrd.local/api/attendance-matrix?school_unit_id=2&month=2026-08"
   ```

3. **Contact HRD admin** with:
   - Error message from logs
   - Curl test command you tried
   - Your unit ID and app name

---

## ✅ After Successful Update

Monitor for 24 hours:
- No 401 errors in logs
- Leave requests syncing normally
- Attendance reports loading correctly
- Bonus reports updating properly

Then mark this update as **COMPLETE** in your deployment notes.

---

**Update Required For**: sans-sd, sans-smp, sans-paud  
**Priority**: 🔴 CRITICAL - Do before HRD deployment  
**Estimated Time**: 30 minutes - 1 hour per app
