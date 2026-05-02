# Organizational Structure Testing Guide

## Prerequisites
- Superadmin account exists (username: admin, password: password)
- Database schema has been updated
- All new files have been created

## Test Scenario 1: Bos Registration and Approval

### Step 1: Register as Bos
1. Open browser and navigate to: `http://localhost/kewer/pages/bos/register.php`
2. Fill in the form:
   - Username: `testbos`
   - Password: `password123`
   - Konfirmasi Password: `password123`
   - Nama Lengkap: `Test Bos`
   - Nama Perusahaan: `PT Test Company`
   - Email: `testbos@example.com`
   - No. Telepon: `08123456789`
   - Provinsi: Select any province
   - Kabupaten/Kota: Select any regency
   - Kecamatan: Select any district
   - Desa/Kelurahan: Select any village
   - Alamat: `Jl Test No. 123`
3. Click "Daftar"
4. Expected: Success message "Pendaftaran berhasil. Menunggu persetujuan superadmin."

### Step 2: Approve Bos Registration (as Superadmin)
1. Login as superadmin (username: admin, password: password)
2. Navigate to: `http://localhost/kewer/pages/superadmin/bos_approvals.php`
3. Expected: See pending registration for "testbos"
4. Click "Setujui" button
5. Confirm approval
6. Expected: Success message and registration removed from pending list
7. Verify in database: User with username "testbos" and role "bos" should exist

## Test Scenario 2: Bos First-Time Setup (Headquarters)

### Step 1: Login as New Bos
1. Navigate to: `http://localhost/kewer/login.php`
2. Login with username: `testbos`, password: `password123`
3. Expected: Redirected to headquarters setup page

### Step 2: Create Headquarters
1. Fill in the form:
   - Kode Cabang: `HQ`
   - Nama Kantor Pusat: `Kantor Pusat Test Company`
   - No. Telepon: `02112345678`
   - Email: `hq@testcompany.com`
   - Provinsi: Select any province
   - Kabupaten/Kota: Select any regency
   - Kecamatan: Select any district
   - Desa/Kelurahan: Select any village
   - Alamat: `Jl HQ No. 1`
2. Click "Buat Kantor Pusat"
3. Expected: Success message and redirect to dashboard after 3 seconds
4. Verify in database:
   - Cabang with kode_cabang "HQ" should exist
   - is_headquarters should be 1
   - owner_bos_id should match bos user id
   - Bos user cabang_id should be updated to point to headquarters

## Test Scenario 3: Add Employees to Headquarters

### Step 1: Login as Bos
1. Login with username: `testbos`
2. Navigate to: `http://localhost/kewer/pages/petugas/tambah.php`

### Step 2: Add Employee
1. Fill in the form:
   - Username: `karyawan1`
   - Password: `password123`
   - Konfirmasi Password: `password123`
   - Nama Lengkap: `Karyawan Satu`
   - Email: `karyawan1@testcompany.com`
   - Role: `karyawan`
   - Cabang: Select "Kantor Pusat Test Company"
2. Click "Simpan"
3. Expected: Success message
4. Verify in database:
   - User with username "karyawan1" should exist
   - owner_bos_id should match bos user id
   - cabang_id should match headquarters id

## Test Scenario 4: Create Branch Office

### Step 1: Login as Bos
1. Login with username: `testbos`
2. Navigate to: `http://localhost/kewer/pages/cabang/tambah.php`

### Step 2: Create Branch
1. Fill in the form:
   - Kode Cabang: `CB01`
   - Nama Cabang: `Cabang Jakarta Selatan`
   - No. Telepon: `02187654321`
   - Email: `cb01@testcompany.com`
   - Provinsi: Select Jakarta
   - Kabupaten/Kota: Select Jakarta Selatan
   - Kecamatan: Select any district
   - Desa/Kelurahan: Select any village
   - Alamat: `Jl Cabang No. 1`
   - Status: Aktif
   - Do NOT check "Jadikan Kantor Pusat"
2. Click "Simpan"
3. Expected: Success message
4. Verify in database:
   - Cabang with kode_cabang "CB01" should exist
   - is_headquarters should be 0
   - owner_bos_id should match bos user id

## Test Scenario 5: Delegated Permissions

### Step 1: Login as Bos
1. Login with username: `testbos`
2. Navigate to: `http://localhost/kewer/pages/bos/delegated_permissions.php`

### Step 2: Delegate Permission
1. Fill in the form:
   - Pilih Karyawan: Select "Karyawan Satu"
   - Scope Permission: Select "CRUD Karyawan"
   - Berlaku Sampai: Leave empty (no expiration)
   - Catatan: `Test delegation`
2. Click "Delegate Permission"
3. Expected: Success message and permission appears in list
4. Verify in database:
   - Record in delegated_permissions table should exist
   - delegator_id should match bos user id
   - delegatee_id should match karyawan1 user id
   - permission_scope should be "employee_crud"
   - is_active should be 1

### Step 3: Test Permission Check
1. Login as karyawan1 (username: karyawan1, password: password123)
2. Try to access user management: `http://localhost/kewer/pages/petugas/tambah.php`
3. Expected: Should be able to access (due to delegated permission)
4. Verify: The delegated permission is being checked in hasPermission() function

## Test Scenario 6: Branch Manager Assignment

### Step 1: Add Manager to Branch
1. Login as bos
2. Add a new user with role `admin_cabang` or `manager_cabang`
3. Assign them to branch "Cabang Jakarta Selatan"

### Step 2: Assign as Branch Manager via API
Use curl or Postman to call:
```
POST http://localhost/kewer/api/branch_managers.php?action=assign
Content-Type: application/x-www-form-urlencoded

cabang_id=<branch_id>
manager_user_id=<manager_user_id>
manager_type=admin_cabang
can_add_employees=1
can_manage_branch=1
```

### Step 3: Verify
1. Check database branch_managers table
2. Record should exist with correct values

## Test Scenario 7: Superadmin Monitoring

### Step 1: Login as Superadmin
1. Login with username: admin
2. Navigate to: `http://localhost/kewer/pages/cabang/index.php`

### Step 2: Verify Visibility
1. Expected: Should see all branches including those owned by testbos
2. Should see owner names for each branch
3. Should see branch type (Kantor Pusat vs Cabang)
4. Should be able to delete any branch

## Test Scenario 8: Data Filtering

### Step 1: Login as Bos
1. Login with username: testbos
2. Navigate to: `http://localhost/kewer/pages/cabang/index.php`

### Step 2: Verify Data Isolation
1. Expected: Should only see branches owned by testbos
2. Should not see branches owned by other bos (if any exist)

### Step 3: Login as Karyawan
1. Login with username: karyawan1
2. Navigate to: `http://localhost/kewer/pages/cabang/index.php`

### Step 4: Verify Restricted Access
1. Expected: Should only see their assigned branch or branches based on permissions
2. Should not see branches they don't have access to

## Test Scenario 9: Permission Revocation

### Step 1: Login as Bos
1. Login with username: testbos
2. Navigate to: `http://localhost/kewer/pages/bos/delegated_permissions.php`

### Step 2: Revoke Permission
1. Find the delegated permission for karyawan1
2. Click "Cabut" button
3. Confirm revocation
4. Expected: Success message and permission status changes to "Dicabut"

### Step 3: Verify Revocation
1. Login as karyawan1
2. Try to access user management
3. Expected: Should be denied access (permission revoked)

## Test Scenario 10: Headquarters Uniqueness

### Step 1: Login as Bos
1. Login with username: testbos
2. Navigate to: `http://localhost/kewer/pages/cabang/tambah.php`

### Step 2: Try to Create Second Headquarters
1. Fill in form with different details
2. Check "Jadikan Kantor Pusat"
3. Click "Simpan"
4. Expected: Error message "Bos hanya dapat memiliki satu kantor pusat"

## Verification Checklist

- [ ] Bos can register publicly
- [ ] Superadmin can approve/reject bos registrations
- [ ] Bos is redirected to setup headquarters on first login
- [ ] Bos can create only one headquarters
- [ ] Bos can create multiple branch offices
- [ ] Branches show owner information
- [ ] Employees have owner_bos_id set correctly
- [ ] Bos can delegate permissions to employees
- [ ] Delegated permissions are checked in hasPermission()
- [ ] Permissions can be revoked
- [ ] Superadmin can see all organizations
- [ ] Bos can only see their own organization
- [ ] Employees can only access based on role + delegated permissions
- [ ] Branch managers can be assigned via API
- [ ] Data is properly filtered by owner_bos_id

## Known Limitations

1. Branch manager assignment UI not yet created (API only)
2. No automated tests yet - manual testing required
3. ✅ Email notifications implemented for bos approval (v1.1.0)
4. No audit trail for delegated permission changes

## Next Steps

After manual testing is complete:
1. Implement automated tests using Puppeteer
2. ✅ Email notifications for bos approval completed (v1.1.0)
3. Create branch manager assignment UI
4. Add audit trail for all organizational structure changes
