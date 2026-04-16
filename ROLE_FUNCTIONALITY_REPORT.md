# Kewer Application - Role Functionality Report

**Date:** 2026-04-16
**Application:** Koperasi Kewer (Koperasi Warga Ekonomi Rakyat)

---

## Executive Summary

The Kewer application uses a role-based access control (RBAC) system with hierarchical roles and granular permissions. The system is designed for a cooperative (koperasi) with both central (pusat) and branch (cabang) operations.

**Total Roles:** 7
**Total Permissions:** 25+
**Total Modules:** 16

---

## Role Hierarchy

The system follows a hierarchical structure where higher-level roles have broader access:

```
1. Owner (Highest)
2. Manajer Cabang
3. Admin Pusat
4. Admin Cabang
5. Petugas Pusat
6. Petugas Cabang
7. Karyawan (Lowest)
```

**Hierarchy Logic:**
- Higher-level roles can manage roles below them
- Owner has all permissions
- Each role has specific responsibilities based on their position in the organization

---

## Complete Role Definitions

### 1. Owner

**Hierarchy Level:** 1 (Highest)
**Access Scope:** All branches (pusat)
**Special Privileges:** All permissions automatically granted

**Responsibilities:**
- Overall system oversight
- Strategic decision making
- Full access to all features across all branches
- Can manage all other roles except owner

**Key Features:**
- ✅ All permissions automatically granted
- ✅ View consolidated reports for all branches
- ✅ Manage all system settings
- ✅ Create and manage branches
- ✅ Assign permissions to users
- ✅ Full audit log access

---

### 2. Manajer Cabang (Branch Manager)

**Hierarchy Level:** 2
**Access Scope:** Single branch (cabang)
**Can Manage:** admin_pusat, admin_cabang, petugas_pusat, petugas_cabang, karyawan

**Responsibilities:**
- Manage branch operations
- Approve/reject loan applications
- Monitor branch financial performance
- Manage branch staff
- Cash reconciliation

**Key Features:**

**Nasabah Management:**
- ✅ View nasabah list
- ✅ Add new nasabah
- ✅ Edit nasabah information
- ✅ Delete nasabah (with restrictions)

**Pinjaman Management:**
- ✅ View all pinjaman
- ✅ Create new pinjaman applications
- ✅ Approve pinjaman applications
- ✅ View pinjaman details
- ✅ Auto-confirm pinjaman (if permitted)

**Angsuran Management:**
- ✅ View angsuran schedule
- ✅ Record payments
- ✅ Edit payment records
- ✅ Delete payment records

**Financial Management:**
- ✅ View kas (cash) balance
- ✅ Update kas balance
- ✅ Cash reconciliation
- ✅ View pengeluaran (expenses)
- ✅ Manage pengeluaran

**Staff Management:**
- ✅ View users
- ✅ Add new users (except owner)
- ✅ Edit user information
- ✅ Delete users (with restrictions)
- ✅ Assign permissions to users

**Branch Management:**
- ✅ View branch information
- ⚠️ Cannot create/edit/delete branches (restricted to owner/superadmin)

**Settings:**
- ✅ View interest rate settings
- ✅ Modify interest rates
- ✅ View system settings

**Reports:**
- ✅ View consolidated reports
- ✅ View branch-specific reports
- ✅ Access family risk reports

---

### 3. Admin Pusat (Central Admin)

**Hierarchy Level:** 3
**Access Scope:** All branches (pusat)
**Can Manage:** petugas_pusat, petugas_cabang, karyawan

**Responsibilities:**
- Central administrative functions
- Cross-branch oversight
- System configuration
- User management across branches

**Key Features:**

**Nasabah Management:**
- ✅ View nasabah across all branches
- ✅ Add nasabah
- ✅ Edit nasabah
- ✅ Delete nasabah

**Pinjaman Management:**
- ✅ View pinjaman across all branches
- ✅ Create pinjaman
- ✅ View details
- ⚠️ Cannot approve pinjaman (branch manager responsibility)

**Angsuran Management:**
- ✅ View angsuran across all branches
- ✅ Record payments

**Financial Management:**
- ✅ View kas across all branches
- ✅ View pengeluaran
- ⚠️ Limited cash reconciliation access

**Staff Management:**
- ✅ View users across all branches
- ✅ Add users (lower-level roles)
- ✅ Edit users
- ✅ Assign permissions

**Branch Management:**
- ✅ View all branches
- ⚠️ Cannot create/edit/delete branches

**Settings:**
- ✅ View and modify interest rates
- ✅ Access system settings

**Reports:**
- ✅ View all reports
- ✅ Access family risk reports

---

### 4. Admin Cabang (Branch Admin)

**Hierarchy Level:** 4
**Access Scope:** Single branch (cabang)
**Can Manage:** petugas_cabang, karyawan

**Responsibilities:**
- Branch administration
- Branch-level user management
- Branch reporting

**Key Features:**

**Nasabah Management:**
- ✅ View nasabah (branch only)
- ✅ Add nasabah
- ✅ Edit nasabah
- ✅ Delete nasabah

**Pinjaman Management:**
- ✅ View pinjaman (branch only)
- ✅ Create pinjaman
- ⚠️ Cannot approve pinjaman

**Angsuran Management:**
- ✅ View angsuran (branch only)
- ✅ Record payments

**Financial Management:**
- ✅ View kas (branch only)
- ✅ View pengeluaran (branch only)
- ⚠️ Limited cash reconciliation

**Staff Management:**
- ✅ View users (branch only)
- ✅ Add users (petugas_cabang, karyawan)
- ✅ Edit users
- ⚠️ Cannot assign permissions (restricted)

**Branch Management:**
- ✅ View branch information
- ⚠️ Cannot modify branch

**Settings:**
- ✅ View interest rates
- ⚠️ Cannot modify (restricted to manager/owner)

**Reports:**
- ✅ View branch reports
- ⚠️ Limited access to consolidated reports

---

### 5. Petugas Pusat (Central Field Officer)

**Hierarchy Level:** 5
**Access Scope:** All branches (pusat)

**Responsibilities:**
- Field activities across branches
- Customer relationship management
- Payment collection
- Market surveys

**Key Features:**

**Nasabah Management:**
- ✅ View nasabah (read-only)
- ⚠️ Cannot add/edit/delete

**Pinjaman Management:**
- ✅ View pinjaman (read-only)
- ⚠️ Cannot create/approve

**Angsuran Management:**
- ✅ View angsuran
- ✅ Record payments
- ✅ Field activity tracking
- ✅ Access field activities module

**Financial Management:**
- ✅ View kas (read-only)
- ✅ Kas petugas management
  - Add kas petugas
  - View kas petugas
  - Setoran kas petugas

**Staff Management:**
- ⚠️ No user management access

**Branch Management:**
- ⚠️ No branch management access

**Settings:**
- ⚠️ No settings access

**Reports:**
- ✅ View basic reports
- ✅ Access field activity reports

---

### 6. Petugas Cabang (Branch Field Officer)

**Hierarchy Level:** 6
**Access Scope:** Single branch (cabang)

**Responsibilities:**
- Branch-level field activities
- Local customer service
- Payment collection
- Branch-specific market activities

**Key Features:**

**Nasabah Management:**
- ✅ View nasabah (branch only, read-only)
- ⚠️ Cannot add/edit/delete

**Pinjaman Management:**
- ✅ View pinjaman (branch only, read-only)
- ⚠️ Cannot create/approve

**Angsuran Management:**
- ✅ View angsuran (branch only)
- ✅ Record payments
- ✅ Field activity tracking (branch only)
- ✅ Access field activities module

**Financial Management:**
- ✅ View kas (branch only, read-only)
- ✅ Kas petugas management (branch only)
  - Add kas petugas
  - View kas petugas
  - Setoran kas petugas

**Staff Management:**
- ⚠️ No user management access

**Branch Management:**
- ⚠️ No branch management access

**Settings:**
- ⚠️ No settings access

**Reports:**
- ✅ View branch reports
- ✅ Access field activity reports

---

### 7. Karyawan (Employee)

**Hierarchy Level:** 7 (Lowest)
**Access Scope:** Single branch (cabang)

**Responsibilities:**
- Administrative support
- Data entry
- Cash handling
- Basic reporting

**Key Features:**

**Nasabah Management:**
- ✅ View nasabah (branch only, read-only)
- ⚠️ Cannot add/edit/delete

**Pinjaman Management:**
- ✅ View pinjaman (branch only, read-only)
- ⚠️ Cannot create/approve

**Angsuran Management:**
- ✅ View angsuran (branch only, read-only)
- ✅ Record payments
- ⚠️ No field activity access

**Financial Management:**
- ✅ View kas (branch only)
- ✅ Cash reconciliation
- ✅ View pengeluaran (branch only, read-only)
- ⚠️ Cannot modify financial records

**Staff Management:**
- ⚠️ No user management access

**Branch Management:**
- ⚠️ No branch management access

**Settings:**
- ⚠️ No settings access

**Reports:**
- ✅ View basic branch reports
- ⚠️ Limited reporting access

---

## Permission Matrix

### Core Permissions

| Permission | Description | Owner | Manajer Cabang | Admin Pusat | Admin Cabang | Petugas Pusat | Petugas Cabang | Karyawan |
|------------|-------------|-------|---------------|-------------|-------------|---------------|---------------|----------|
| nasabah.read | View nasabah list | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| manage_nasabah | Add/edit/delete nasabah | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| pinjaman.read | View pinjaman list | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| manage_pinjaman | Create pinjaman | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| pinjaman.approve | Approve pinjaman | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| pinjaman.auto_confirm | Auto-confirm settings | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| angsuran.read | View angsuran schedule | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| manage_pembayaran | Record/edit/delete payments | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| kas.read | View kas balance | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| kas.update | Update kas balance | ✅ | ✅ | ⚠️ | ⚠️ | ❌ | ❌ | ❌ |
| kas_petugas.read | View kas petugas | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| kas_petugas.update | Manage kas petugas | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| users.create | Create users | ✅ | ✅ | ✅ | ⚠️ | ❌ | ❌ | ❌ |
| users.read | View users | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| manage_users | Manage users | ✅ | ✅ | ✅ | ⚠️ | ❌ | ❌ | ❌ |
| assign_permissions | Assign permissions | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| cabang.read | View branches | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| manage_cabang | Manage branches | ✅ | ⚠️ | ❌ | ❌ | ❌ | ❌ | ❌ |
| manage_petugas | Manage petugas | ✅ | ✅ | ✅ | ⚠️ | ❌ | ❌ | ❌ |
| view_petugas | View petugas | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| manage_bunga | Modify interest rates | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| view_settings | View settings | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| manage_pengeluaran | Manage expenses | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| view_pengeluaran | View expenses | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| manage_kas_bon | Manage kas bon | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| view_kas_bon | View kas bon | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| view_laporan | View reports | ✅ | ✅ | ✅ | ⚠️ | ⚠️ | ⚠️ | ⚠️ |

**Legend:**
- ✅ Granted
- ❌ Not granted
- ⚠️ Conditional/Limited access

---

## Module Breakdown

### 1. Nasabah Module
**Purpose:** Customer (nasabah) management
**Features:**
- Customer registration with KTP verification
- Customer profile management
- Family linking for risk assessment
- Customer status management (aktif/nonaktif)
- OCR integration for KTP scanning

**Access by Role:**
- Full CRUD: Owner, Manajer Cabang, Admin Pusat, Admin Cabang
- Read-only: Petugas Pusat, Petugas Cabang, Karyawan

---

### 2. Pinjaman Module
**Purpose:** Loan application and management
**Features:**
- Loan application form
- Dynamic interest rate calculation
- Loan approval workflow
- Auto-confirmation settings
- Loan schedule generation
- Risk assessment integration
- Family risk checking

**Access by Role:**
- Full CRUD + Approve: Owner, Manajer Cabang
- Full CRUD (no approve): Admin Pusat, Admin Cabang
- Read-only: Petugas Pusat, Petugas Cabang, Karyawan

---

### 3. Angsuran Module
**Purpose:** Installment payment management
**Features:**
- Installment schedule view
- Payment recording
- Payment history
- Late payment tracking
- Payment reminders
- Field activity integration

**Access by Role:**
- Full CRUD: Owner, Manajer Cabang, Admin Pusat, Admin Cabang
- Payment recording: Petugas Pusat, Petugas Cabang, Karyawan
- Read-only: All roles

---

### 4. Field Activities Module
**Purpose:** Field officer activity tracking
**Features:**
- Activity logging (survey, collection, follow-up, promotion)
- Location tracking
- Activity reports
- GPS integration (placeholder)
- Photo documentation

**Access by Role:**
- Full access: Petugas Pusat, Petugas Cabang
- View reports: Owner, Manajer Cabang, Admin Pusat
- No access: Admin Cabang, Karyawan

---

### 5. Kas Petugas Module
**Purpose:** Field officer cash management
**Features:**
- Cash allocation to field officers
- Cash deposit tracking
- Balance management
- Transaction history
- Reconciliation

**Access by Role:**
- Full CRUD: Owner, Manajer Cabang, Admin Pusat, Admin Cabang, Petugas Pusat, Petugas Cabang
- Read-only: Karyawan

---

### 6. Cash Reconciliation Module
**Purpose:** Daily cash balance verification
**Features:**
- Daily cash balance tracking
- Reconciliation reports
- Discrepancy detection
- Audit trail
- Multi-branch support

**Access by Role:**
- Full access: Owner, Manajer Cabang
- Reconciliation only: Karyawan
- View-only: Admin Pusat, Admin Cabang
- No access: Petugas roles

---

### 7. Users Module
**Purpose:** User account management
**Features:**
- User creation
- User profile editing
- User deactivation
- Role assignment
- Permission assignment
- User activity logs

**Access by Role:**
- Full CRUD + Permissions: Owner, Manajer Cabang
- Full CRUD (limited): Admin Pusat
- Full CRUD (very limited): Admin Cabang
- No access: Petugas roles, Karyawan

---

### 8. Cabang Module
**Purpose:** Branch management
**Features:**
- Branch creation
- Branch information editing
- Branch status management
- Branch statistics
- Branch performance reports

**Access by Role:**
- Full CRUD: Owner
- View-only: Manajer Cabang, Admin Pusat, Admin Cabang
- No access: Petugas roles, Karyawan

---

### 9. Auto-Confirm Module
**Purpose:** Automated loan approval settings
**Features:**
- Auto-confirmation rules
- Risk threshold settings
- Approval limits
- Conditional approval logic
- Audit logs

**Access by Role:**
- Full access: Owner, Manajer Cabang
- No access: All other roles

---

### 10. Setting Bunga Module
**Purpose:** Interest rate configuration
**Features:**
- Base interest rate settings
- Risk adjustment factors
- Collateral adjustment factors
- Loan type configuration
- Dynamic rate calculation rules

**Access by Role:**
- Full access: Owner, Manajer Cabang, Admin Pusat
- View-only: Admin Cabang
- No access: Petugas roles, Karyawan

---

### 11. Pengeluaran Module
**Purpose:** Expense management
**Features:**
- Expense recording
- Expense categorization
- Expense approval workflow
- Expense reports
- Budget tracking

**Access by Role:**
- Full CRUD: Owner, Manajer Cabang, Admin Pusat, Admin Cabang
- View-only: Petugas roles, Karyawan

---

### 12. Kas Bon Module
**Purpose:** Cash advance (kas bon) management
**Features:**
- Cash advance requests
- Approval workflow
- Deduction tracking
- Balance calculation
- Repayment scheduling

**Access by Role:**
- Full CRUD: Owner, Manajer Cabang, Admin Pusat, Admin Cabang
- View-only: Petugas roles, Karyawan

---

### 13. Permissions Module
**Purpose:** Permission system management
**Features:**
- Permission definition
- Role-permission mapping
- User-permission overrides
- Permission audit logs
- Permission templates

**Access by Role:**
- Full access: Owner
- Limited access: Manajer Cabang, Admin Pusat
- No access: All other roles

---

### 14. Family Risk Module
**Purpose:** Family-based risk assessment
**Features:**
- Family relationship tracking
- Risk score calculation
- Loan limit adjustment
- Risk alerts
- Family risk reports

**Access by Role:**
- Full access: Owner, Manajer Cabang, Admin Pusat
- View-only: Admin Cabang
- No access: Petugas roles, Karyawan

---

### 15. Petugas Module
**Purpose:** Field officer management
**Features:**
- Petugas profile management
- Performance tracking
- Activity assignment
- Territory assignment
- Petugas reports

**Access by Role:**
- Full CRUD: Owner, Manajer Cabang, Admin Pusat
- View-only: Admin Cabang
- No access: Petugas roles, Karyawan

---

### 16. Pembayaran Module
**Purpose:** Payment processing
**Features:**
- Payment recording
- Payment verification
- Payment history
- Receipt generation
- Payment reconciliation

**Access by Role:**
- Full CRUD: Owner, Manajer Cabang, Admin Pusat, Admin Cabang
- Payment recording: Petugas Pusat, Petugas Cabang, Karyawan

---

## Special Features by Role

### Owner-Only Features
- Branch creation and deletion
- Full permission assignment
- System-wide audit log access
- All consolidated reports
- Global settings modification

### Manager-Only Features
- Loan approval authority
- Branch cash reconciliation
- Interest rate modification
- Auto-confirmation configuration
- Staff management (except owner)

### Admin-Only Features
- Cross-branch data access (Admin Pusat)
- User management (limited scope)
- Permission assignment (limited)
- Report generation

### Petugas-Only Features
- Field activity logging
- Kas petugas management
- Payment collection in field
- Customer interaction tracking
- GPS/location features

### Karyawan-Only Features
- Cash reconciliation
- Basic data entry
- Payment recording
- Report viewing (limited)

---

## Current Implementation Status

**Database Status:**
- ✅ Users table populated with superadmin and petugas
- ⚠️ ref_roles table exists but empty
- ⚠️ permissions table exists but empty
- ⚠️ role_permissions table exists but empty

**Code Implementation:**
- ✅ Permission system fully implemented in functions.php
- ✅ Role hierarchy defined
- ✅ Permission checks throughout all pages
- ⚠️ Default permissions not seeded in database
- ⚠️ Role-based menu rendering working correctly

**Recommendations:**
1. Seed ref_roles table with role definitions
2. Seed permissions table with all permission codes
3. Seed role_permissions table with default role-permission mappings
4. Update hasPermission() function to use database or fall back to hardcoded logic
5. Create admin panel for permission management

---

## Security Considerations

**Audit Logging:**
- All permission changes logged
- User action tracking
- IP address logging
- User agent tracking

**Permission Override:**
- User-specific permissions can override role permissions
- Audit trail for all overrides
- Requires assign_permissions permission

**Role Management:**
- Cannot modify own role
- Cannot assign higher role
- Owner role protected

**Data Isolation:**
- Pusat roles see all branches
- Cabang roles see only their branch
- Cabang_id filtering enforced at database level

---

## Summary

The Kewer application has a comprehensive role-based access control system with 7 hierarchical roles and 25+ granular permissions. The system is designed to support both central (pusat) and branch (cabang) operations with appropriate access controls for each organizational level.

**Key Strengths:**
- Clear role hierarchy
- Granular permission system
- Audit logging for security
- Data isolation by branch
- Flexible permission overrides

**Areas for Improvement:**
- Database tables need seeding with default data
- Permission management UI could be enhanced
- Role-based menu could be more dynamic
- Additional security features (2FA, etc.)

---

**Report Generated By:** Cascade AI Assistant
**Date:** 2026-04-16
