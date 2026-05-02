# Organizational Structure Design - Kewer Application

## Requirements

### Strict Workflow Rules
1. **Superadmin**: Pemilik aplikasi, tidak memiliki kantor, memonitor seluruh bos dan aktivitas seluruh bawahan bos/cabang
2. **Bos Registration**: Bos harus mendaftar dan disetujui oleh superadmin
3. **Headquarters (Kantor Pusat)**: Bos harus punya kantor pusat
4. **Employee Management**: Bos menambahkan data karyawan di kantor pusat
5. **Derived Access**: Bos memberikan akses turunan kepada karyawan untuk CRUD data karyawan maupun kantor cabang (biasanya diberikan kepada admin pusat, namun bagaimana apabila admin pusat tidak ada)
6. **Branch Management**: Bos menambahkan kantor cabang
7. **Branch Employee Management**: Bos menentukan siapa yang boleh menambahkan karyawan di cabang tersebut (biasanya oleh manajer cabang, namun bagaimana apabila manajer cabang tidak ada)

## Corrected Role Definitions

### Superadmin (Application Owner)
- **Definition**: Pemilik aplikasi Kewer
- **Scope**: Global - memonitor seluruh bos dan organisasi mereka
- **No Office**: Tidak memiliki kantor fisik
- **Responsibilities**:
  - Menyetujui pendaftaran bos baru
  - Memonitor aktivitas seluruh bos
  - Memonitor aktivitas seluruh cabang di seluruh organisasi
  - Technical oversight aplikasi
  - System configuration

### Bos (Business Owner)
- **Definition**: Pemilik usaha yang menggunakan aplikasi Kewer
- **Scope**: Organisasi mereka sendiri (headquarters + branches)
- **Must Have**: Kantor pusat (headquarters)
- **Responsibilities**:
  - Mendaftar ke aplikasi (disetujui superadmin)
  - Membuat kantor pusat
  - Menambahkan karyawan di kantor pusat
  - Membuat kantor cabang
  - Menentukan siapa yang boleh menambahkan karyawan di cabang
  - Memberikan akses turunan ke karyawan
  - Memonitor operasional organisasi mereka

### Organizational Structure per Bos

```
BOS (Owner)
├── Kantor Pusat (Headquarters)
│   ├── Admin Pusat (jika ada)
│   ├── Manager Pusat (jika ada)
│   ├── Petugas Pusat
│   └── Karyawan
└── Kantor Cabang 1, 2, 3, ... (Branches)
    ├── Admin Cabang (jika ada)
    ├── Manager Cabang (jika ada)
    ├── Petugas Cabang
    └── Karyawan
```

## Current System Analysis

### Current Role Hierarchy
- superadmin (Level 1) - Full access
- bos (Level 2) - Full access except assign_permissions
- manager_pusat (Level 3) - Cross-branch operational control
- manager_cabang (Level 4) - Branch operational control
- admin_pusat (Level 5) - Cross-branch administrative access
- admin_cabang (Level 6) - Branch administrative access
- petugas_pusat (Level 7) - Cross-branch field access
- petugas_cabang (Level 8) - Branch field access
- karyawan (Level 9) - Basic administrative access

### Current Issues
- Users can be created directly by superadmin without approval workflow
- No bos registration/approval system
- No headquarters concept in database
- No derived access delegation system
- Branch management doesn't track which bos owns it
- No flexible role assignment based on organizational structure
- Superadmin currently has cabang_id (should be NULL)

## New Organizational Structure Design

### Core Concepts

#### 1. Superadmin (Application Owner)
- **No Office**: cabang_id = NULL
- **Global Monitoring**: Can view all bos and all their organizations
- **Approval Authority**: Approves bos registrations
- **Technical Role**: System configuration and oversight

#### 2. Bos (Business Owner)
- **Registration**: Must register and be approved by superadmin
- **Headquarters**: Must create one headquarters (is_headquarters = true)
- **Branches**: Can create multiple branch offices
- **Ownership**: Owns all branches (track via owner_bos_id in cabang table)
- **Derived Access**: Can delegate CRUD permissions to employees

#### 3. Headquarters (Kantor Pusat)
- **Definition**: Main office owned by bos
- **Database**: cabang table with `is_headquarters = true`, `owner_bos_id = bos.id`
- **Employees**: Can have admin_pusat, manager_pusat, petugas_pusat, karyawan
- **Flexibility**: If admin_pusat doesn't exist, manager_pusat can perform admin_pusat duties

#### 4. Branch Offices (Kantor Cabang)
- **Definition**: Satellite offices owned by bos
- **Database**: cabang table with `is_headquarters = false`, `owner_bos_id = bos.id`
- **Owner**: bos (via owner_bos_id)
- **Created by**: bos
- **Manager**: Can be manager_cabang OR admin_cabang (flexible based on bos decision)
- **Employees**: Can have admin_cabang, manager_cabang, petugas_cabang, karyawan
- **Flexibility**: If manager_cabang doesn't exist, admin_cabang can perform manager_cabang duties

#### 5. Derived Access System
- **Concept**: Bos can delegate CRUD permissions to employees
- **Flexibility**: 
  - If admin_pusat doesn't exist, bos can delegate admin_pusat permissions to manager_pusat
  - If manager_cabang doesn't exist, bos can delegate manager_cabang permissions to admin_cabang
- **Implementation**: New table `delegated_permissions`

### Database Schema Changes

#### Modify Existing Tables

##### 1. users table
```sql
ALTER TABLE users ADD COLUMN owner_bos_id INT UNSIGNED NULL AFTER cabang_id;
ALTER TABLE users ADD INDEX idx_owner_bos_id (owner_bos_id);
ALTER TABLE users ADD COLUMN derived_permissions JSON NULL AFTER owner_bos_id;
```

##### 2. cabang table
```sql
ALTER TABLE cabang ADD COLUMN is_headquarters BOOLEAN DEFAULT false AFTER status;
ALTER TABLE cabang ADD COLUMN owner_bos_id INT UNSIGNED NULL AFTER is_headquarters;
ALTER TABLE cabang ADD INDEX idx_owner_bos_id (owner_bos_id);
ALTER TABLE cabang ADD COLUMN created_by_user_id INT UNSIGNED NULL AFTER owner_bos_id;
ALTER TABLE cabang ADD INDEX idx_created_by_user_id (created_by_user_id);
```

##### 3. Update superadmin record
```sql
UPDATE users SET cabang_id = NULL WHERE role = 'superadmin';
```

#### New Tables

##### 1. bos_registrations
```sql
CREATE TABLE bos_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    telp VARCHAR(20),
    nama_perusahaan VARCHAR(255),
    alamat TEXT,
    province_id INT UNSIGNED,
    regency_id INT UNSIGNED,
    district_id INT UNSIGNED,
    village_id INT UNSIGNED,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    rejection_reason TEXT,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    approved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
);
```

##### 2. delegated_permissions
```sql
CREATE TABLE delegated_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    delegator_id INT NOT NULL COMMENT 'User who delegates permission',
    delegatee_id INT NOT NULL COMMENT 'User who receives permission',
    permission_scope ENUM('employee_crud', 'branch_crud', 'branch_employee_crud', 'all_operations') NOT NULL,
    scope_limitation JSON NULL COMMENT 'Limitations on scope (e.g., specific branches)',
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT true,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_delegator_id (delegator_id),
    INDEX idx_delegatee_id (delegatee_id),
    INDEX idx_is_active (is_active)
);
```

##### 3. branch_managers
```sql
CREATE TABLE branch_managers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cabang_id INT NOT NULL,
    manager_user_id INT NOT NULL,
    manager_type ENUM('manager_cabang', 'admin_cabang', 'petugas_cabang') NOT NULL,
    appointed_by_bos_id INT NOT NULL,
    can_add_employees BOOLEAN DEFAULT true,
    can_manage_branch BOOLEAN DEFAULT true,
    appointed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cabang_id (cabang_id),
    INDEX idx_manager_user_id (manager_user_id),
    INDEX idx_appointed_by_bos_id (appointed_by_bos_id)
);
```

## Workflow Design

### 1. Bos Registration Workflow

```
Step 1: Bos Registration Form
- Bos fills registration form (username, password, personal info, company info)
- Data stored in bos_registrations table with status 'pending'

Step 2: Superadmin Review
- Superadmin views pending bos registrations
- Superadmin can approve or reject
- If approved: Create user record, bos can login
- If rejected: Store rejection reason

Step 3: Bos Login & Setup
- Bos logs in for the first time
- System prompts to create headquarters
- Bos must create headquarters before accessing other features
```

### 2. Headquarters Creation Workflow

```
Step 1: Bos creates headquarters
- Bos fills cabang form with is_headquarters = true
- owner_bos_id set to bos.id
- created_by_user_id set to bos.id

Step 2: Add employees to headquarters
- Bos can add employees with roles: admin_pusat, manager_pusat, petugas_pusat, karyawan
- owner_bos_id set to bos.id for all employees
- cabang_id set to headquarters.id
```

### 3. Branch Creation Workflow

```
Step 1: Bos creates branch
- Bos fills cabang form with is_headquarters = false
- owner_bos_id set to bos.id
- created_by_user_id set to bos.id

Step 2: Assign branch manager
- Bos selects who manages the branch
- Options: manager_cabang, admin_cabang, petugas_cabang
- If manager_cabang not available, can assign admin_cabang
- If admin_cabang not available, can assign petugas_cabang
- Record in branch_managers table

Step 3: Add employees to branch
- Based on branch manager assignment, determine who can add employees
- If manager_cabang assigned: manager_cabang can add employees
- If admin_cabang assigned (no manager_cabang): admin_cabang can add employees
- If petugas_cabang assigned (no admin_cabang, no manager_cabang): petugas_cabang can add employees
```

### 4. Derived Access Workflow

```
Step 1: Bos delegates permissions
- Bos selects employee to receive permissions
- Bos selects permission scope:
  - employee_crud: Can CRUD employees in their scope
  - branch_crud: Can CRUD branch data
  - branch_employee_crud: Can CRUD employees in branches
  - all_operations: Full operations
- Bos sets scope limitations (e.g., specific branches only)
- Record in delegated_permissions table

Step 2: Permission Check
- When employee performs action, check delegated_permissions
- If delegated permission exists and is active, allow action
- Otherwise, use standard role-based permissions
```

## Implementation Plan

### Phase 1: Database Schema Updates
1. Modify users table (add owner_bos_id, derived_permissions)
2. Modify cabang table (add is_headquarters, owner_bos_id, created_by_user_id)
3. Update superadmin cabang_id to NULL
4. Create bos_registrations table
5. Create delegated_permissions table
6. Create branch_managers table

### Phase 2: Backend Implementation
1. Create bos registration API
2. Create bos approval API
3. Update user creation API to include owner_bos_id
4. Update cabang creation API to include is_headquarters, owner_bos_id
5. Create delegated permissions API
6. Create branch manager assignment API
6. Update permission checking logic to include derived permissions

### Phase 3: Frontend Implementation
1. Create bos registration page (public)
2. Create bos approval page (superadmin only)
3. Update bos first-time setup flow (headquarters creation)
4. Update cabang management (show is_headquarters, owner)
5. Update user management (show owner_bos_id, derived permissions)
6. Create delegated permissions management page
7. Create branch manager assignment page

### Phase 4: Testing
1. Test bos registration and approval
2. Test headquarters creation
3. Test branch creation and manager assignment
4. Test employee management with derived permissions
5. Test permission checking with derived access
6. Test superadmin monitoring of all bos
