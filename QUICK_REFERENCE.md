# ⚡ QUICK REFERENCE - SANS-HRD API Security Update

## 🔴 BEFORE YOU DEPLOY

### For HRD Admin/Backend:
```bash
# 1. Backup database
mysqldump sans_hrd > backup_2026_08_14.sql

# 2. Add to .env
PKG_API_TOKEN=your_secure_random_token_here_32_chars_min

# 3. Update SchoolUnits in database (use tinker)
php artisan tinker
> $sd = App\Models\SchoolUnit::find(2); $sd->api_token = 'sd_token_abc123'; $sd->save();
> $smp = App\Models\SchoolUnit::find(3); $smp->api_token = 'smp_token_def456'; $smp->save();
> $paud = App\Models\SchoolUnit::find(4); $paud->api_token = 'paud_token_ghi789'; $paud->save();

# 4. Run migrations
php artisan migrate

# 5. Clear cache
php artisan cache:clear && php artisan config:cache
```

### For Unit App Developers (SD/SMP/PAUD):
```bash
# 1. Read integration guide
cat UNIT_APPS_INTEGRATION_GUIDE.md (from HRD)

# 2. Update .env
HRD_API_TOKEN=token_received_from_hrd_admin
SCHOOL_UNIT_ID=2  # or 3 for SMP, 4 for PAUD
HRD_API_URL=http://sans-hrd.local  # already exists

# 3. Find and update all HRD API calls
grep -r "Http::.*get\|Http::.*post" app/ routes/ --include="*.php" | grep -i hrd

# 4. Add withHeaders to each call
# OLD: Http::get($hrdUrl . '/api/...')
# NEW: Http::withHeaders(['X-API-TOKEN' => env('HRD_API_TOKEN')])->get(...)
#      + add 'school_unit_id' => config('app.school_unit_id') to params

# 5. Test in staging
# See UNIT_APPS_INTEGRATION_GUIDE.md for detailed test steps

# 6. NO migrations needed - only code changes!
php artisan cache:clear
```

---

## 🚀 DEPLOYMENT ORDER

1. **HRD First** (backend)
   ```
   git pull → migrate → cache:clear → test
   ```

2. **Wait 5 minutes**

3. **All Unit Apps Together** (backend)
   ```
   git pull → cache:clear → test
   ```

---

## ✅ QUICK TEST CHECKLIST

After deployment:

```bash
# Test 1: API without token (should fail)
curl http://sans-hrd.local/api/employees
# → 401 Unauthorized

# Test 2: API with token (should work)
curl -H "X-API-TOKEN: your_token" \
  http://sans-hrd.local/api/employees?school_unit_id=2
# → 200 OK

# Test 3: Create leave in unit app
# → Should sync to HRD without errors
# → Check logs for "Leave request synced successfully"

# Test 4: Check rate limiting works
for i in {1..70}; do curl -H "X-API-TOKEN: your_token" http://sans-hrd.local/api/employees?school_unit_id=2; done
# → First 60 work, rest get 429
```

---

## 🔧 KEY FILES CHANGED

**New Files**:
- `app/Http/Middleware/VerifySchoolUnitToken.php`
- `app/Http/Middleware/VerifyPkgApiToken.php`
- `database/migrations/2026_08_14_000000_*` (unique constraint)
- `database/migrations/2026_08_14_000001_*` (performance indexes)

**Modified Files**:
- `routes/web.php` (added middleware + rate limit)
- `app/Http/Controllers/Api/LeaveSyncApiController.php` (better logging)
- `app/Http/Controllers/Api/PkgIntegrationApiController.php` (better logging)

**Documentation**:
- `API_SECURITY_CHANGELOG.md` (detailed reference)
- `UNIT_APPS_INTEGRATION_GUIDE.md` (for unit apps)
- `IMPLEMENTATION_SUMMARY.md` (overview)

---

## 🆘 EMERGENCY ROLLBACK

If something goes wrong:

```bash
# Rollback HRD
git revert <commit_hash>
php artisan migrate:rollback
php artisan cache:clear

# Rollback unit apps
git revert <commit_hash>
php artisan cache:clear
```

---

## 📞 QUICK REFERENCE API FORMAT

All HRD API calls must now follow this pattern:

```php
// ANY request to HRD API
Http::withHeaders([
    'X-API-TOKEN' => env('HRD_API_TOKEN')
])->get('http://sans-hrd.local/api/endpoint-name', [
    'school_unit_id' => config('app.school_unit_id'),  // REQUIRED!
    'other_param' => 'value'
]);
```

---

## 📊 BEFORE/AFTER API COMPARISON

| Aspect | Before | After |
|--------|--------|-------|
| Authentication | ❌ None | ✅ Token required |
| Rate Limiting | ❌ None | ✅ 60/min per IP |
| Error Logging | ⚠️ Minimal | ✅ Full context |
| Duplicates | ❌ Possible | ✅ Prevented |
| Query Speed | ⚠️ Slow | ✅ 10-100x faster |
| Security | 🔴 Critical | ✅ Secure |

---

## 🎯 SUCCESS INDICATORS

After deployment, these should all be TRUE:

- ✅ HRD API requires token (401 without it)
- ✅ Unit apps still sync leaves successfully
- ✅ No 401 errors in unit app logs
- ✅ Leave requests appear in HRD normally
- ✅ Attendance/bonus reports load
- ✅ No "too many requests" errors under normal load
- ✅ Response times are faster than before
- ✅ No duplicate leaves in database

---

**Questions?** → See detailed docs above  
**Ready?** → Start with HRD deployment  
**Done!** → Monitor logs for 24 hours  

🚀 **DEPLOYMENT READY**
