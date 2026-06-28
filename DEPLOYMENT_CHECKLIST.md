# ✅ Smart Farming Admin - Deployment Checklist

## 🚀 Pre-Deployment

- [ ] **Code Review**
    - [ ] All files created successfully
    - [ ] No syntax errors in PHP files
    - [ ] No errors in Blade templates
    - [ ] Routes properly configured
    - [ ] Middleware properly registered

- [ ] **Database**
    - [ ] Database connection verified
    - [ ] Migrations ready to run
    - [ ] Seeder ready to run
    - [ ] Backup of current database created
    - [ ] Data validation rules confirmed

- [ ] **Security**
    - [ ] CSRF protection in all forms
    - [ ] Password hashing enabled
    - [ ] Session configuration checked
    - [ ] SQL injection prevention verified
    - [ ] XSS protection in templates confirmed

---

## 🔧 Deployment Steps

### Step 1: Database Preparation

```bash
# Backup current database (important!)
mysqldump -u username -p database_name > backup_2026_06_01.sql

# Run migrations
cd d:\laragon\www\smart-farming
php artisan migrate

# Create admin user
php artisan db:seed --class=AdminSeeder
```

**Verify:**

- [ ] Migrations completed without errors
- [ ] `role` column added to users table
- [ ] New columns added to klimatologi table
- [ ] Admin user created successfully

### Step 2: File Deployment

```bash
# Copy all files to production
# Files should already be in: d:\laragon\www\smart-farming

# Verify files exist:
dir app\Http\Controllers\AdminController.php
dir app\Http\Middleware\AdminMiddleware.php
dir resources\views\admin\
dir database\migrations\2026_06_01_*
```

**Verify:**

- [ ] AdminController.php exists
- [ ] AdminMiddleware.php exists
- [ ] All view files exist (login, dashboard, form)
- [ ] Migration files exist
- [ ] Seeder file exists

### Step 3: Configuration

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Optimize
php artisan optimize
```

**Verify:**

- [ ] Cache cleared
- [ ] Routes cached (optional)
- [ ] No errors in console

### Step 4: Testing

#### Test Database Connection

```bash
php artisan tinker
> User::count()
> Klimatologi::count()
```

**Verify:**

- [ ] Database connection working
- [ ] Users table accessible
- [ ] Klimatologi table accessible

#### Test Admin User

```bash
php artisan tinker
> $user = User::where('email', 'admin@smartfarming.local')->first()
> $user->role  # Should be 'admin'
> Hash::check('admin123', $user->password)  # Should be true
```

**Verify:**

- [ ] Admin user exists
- [ ] Admin role is set
- [ ] Password is correct

#### Test Routes

```bash
# Open browser and test URLs
- http://127.0.0.1:8000/admin/login  # Should show login form
- http://127.0.0.1:8000/admin         # Should redirect to login
```

**Verify:**

- [ ] Login page loads correctly
- [ ] Dashboard redirects to login when not authenticated
- [ ] No 404 errors

#### Test Login Flow

1. Open http://127.0.0.1:8000/admin/login
2. Enter email: admin@smartfarming.local
3. Enter password: admin123
4. Click login

**Verify:**

- [ ] Login successful
- [ ] Redirected to dashboard
- [ ] Admin greeting visible
- [ ] Data table displayed

#### Test Create Data

1. Click "+ Tambah Data"
2. Fill form with test data:
    - Tanggal: 2026-06-03
    - TN: 22.5
    - TX: 32.5
    - TAVG: 27.5
    - RH_AVG: 75.5
    - RR: 10.5
    - SS: 8.5
3. Click "Tambah Data"

**Verify:**

- [ ] Form submits successfully
- [ ] Success message displayed
- [ ] Data appears in table
- [ ] No validation errors

#### Test Duplicate Date Prevention

1. Try to add data with same date (2026-06-03)
2. Submit form

**Verify:**

- [ ] Error message: "Data untuk tanggal ini sudah ada di database."
- [ ] Form not submitted
- [ ] Data not added to database

#### Test Edit Data

1. Click "Edit" on any row
2. Change one value (e.g., TN: 23.5)
3. Click "Perbarui Data"

**Verify:**

- [ ] Form pre-filled with old data
- [ ] Data updated successfully
- [ ] Success message displayed
- [ ] Dashboard shows updated value

#### Test Delete Data

1. Click "Delete" on any row
2. Confirm in modal
3. Data should disappear

**Verify:**

- [ ] Delete modal appears
- [ ] Clicking confirm removes data
- [ ] Success message displayed
- [ ] Data no longer in table

#### Test Pagination

1. Add multiple data entries (more than 15)
2. Check pagination controls

**Verify:**

- [ ] Pagination numbers visible
- [ ] Next/Previous buttons work
- [ ] Items per page = 15

#### Test Logout

1. Click "Logout" button
2. Should redirect to homepage

**Verify:**

- [ ] Session destroyed
- [ ] Redirected to /
- [ ] Cannot access /admin without login

---

## 🔍 Post-Deployment Verification

### Code Quality

- [ ] No console errors in browser dev tools
- [ ] No PHP errors in logs: `storage/logs/laravel.log`
- [ ] All Blade templates render correctly
- [ ] CSS styling applied correctly
- [ ] JavaScript functionality working

### Database

- [ ] All migrations applied
- [ ] Admin user exists with correct role
- [ ] Klimatologi table has all new columns
- [ ] No duplicate or invalid data
- [ ] Indexes created properly

### Security

- [ ] CSRF tokens working in all forms
- [ ] Session cookies secure
- [ ] Password hashing verified
- [ ] Authorization middleware active
- [ ] No sensitive data logged

### Performance

- [ ] Dashboard loads in < 2 seconds
- [ ] Form submission completes in < 1 second
- [ ] Database queries optimized
- [ ] No N+1 queries
- [ ] Pagination working smoothly

---

## 📋 Change Log

### Deployed Files

```
✅ app/Http/Controllers/AdminController.php (New)
✅ app/Http/Middleware/AdminMiddleware.php (New)
✅ app/Models/User.php (Modified - added role)
✅ app/Models/Klimatologi.php (Modified - added columns)
✅ routes/web.php (Modified - added admin routes)
✅ bootstrap/app.php (Modified - added middleware)
✅ resources/views/admin/login.blade.php (New)
✅ resources/views/admin/dashboard.blade.php (New)
✅ resources/views/admin/form.blade.php (New)
✅ database/migrations/2026_06_01_000000_add_role_to_users_table.php (New)
✅ database/migrations/2026_06_01_000001_update_klimatologi_table.php (New)
✅ database/seeders/AdminSeeder.php (New)
```

### Documentation

```
✅ ADMIN_GUIDE.md (User Manual)
✅ ADMIN_TECHNICAL.md (Technical Docs)
✅ ADMIN_QUICKSTART.md (Quick Reference)
✅ ADMIN_API_REFERENCE.md (API Docs)
✅ IMPLEMENTATION_REPORT.md (Summary)
✅ DEPLOYMENT_CHECKLIST.md (This file)
```

---

## 🆘 Rollback Plan

If deployment fails:

### Option 1: Database Rollback

```bash
# Restore from backup
mysql -u username -p database_name < backup_2026_06_01.sql

# Revert migrations
php artisan migrate:rollback

# Clear cache
php artisan cache:clear
```

### Option 2: File Rollback

```bash
# Revert file changes from git
git revert HEAD~1

# Or manually delete new files:
del app\Http\Controllers\AdminController.php
del app\Http\Middleware\AdminMiddleware.php
del resources\views\admin\*
```

---

## 📞 Support Contacts

| Issue             | Contact   | Action                      |
| ----------------- | --------- | --------------------------- |
| Database error    | DBA       | Check logs & restore backup |
| Code error        | Developer | Check error log & fix code  |
| Performance issue | DevOps    | Optimize queries/cache      |
| Security issue    | Security  | Patch & update              |

---

## 📝 Deployment Notes

- **Date**: 1 Juni 2026
- **Version**: 1.0
- **Environment**: Development (Laragon)
- **Default Admin**: admin@smartfarming.local / admin123
- **Change Password**: Yes, before production use
- **Backup**: Yes, created before deployment

---

## ✨ Post-Deployment Tasks

1. **Change Default Password**

    ```bash
    php artisan tinker
    > $user = User::find(1)
    > $user->password = Hash::make('NEW_PASSWORD')
    > $user->save()
    ```

2. **Test with Real Data**
    - Import historical climate data if available
    - Verify LSTM model can access new data

3. **Set Up Monitoring**
    - Monitor logs for errors
    - Track user activity
    - Monitor database performance

4. **User Training**
    - Train admin staff on new interface
    - Share ADMIN_GUIDE.md documentation
    - Establish daily update procedure

5. **Documentation Update**
    - Update README.md with admin panel info
    - Add admin contact to support doc
    - Create troubleshooting guide

---

## ✅ Final Sign-Off

**Deployment Completed: ✅ YES / ❌ NO**

**Tested By**: ********\_\_\_********  
**Date**: ********\_\_\_********  
**Status**: ********\_\_\_********  
**Notes**: ********\_\_\_********

---

**Deployment Checklist Version**: 1.0
**Last Updated**: 1 Juni 2026
