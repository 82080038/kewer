# Multi-Database Integration

## Overview

The Kewer application is configured to use 3 databases for different purposes:

### 1. kewer (Main Database)
- **Purpose**: Main transactional database
- **Tables**: 49 tables + 3 views including users, nasabah, pinjaman, angsuran, cabang, koperasi_activities, petugas_daerah_tugas, billing_plans, usage_log, ai_advice, etc.
- **Connection**: `$conn` / `query()` function
- **Usage**: Primary application data, platform layer (appOwner)

### 2. db_alamat_simple (Address Database)
- **Purpose**: Indonesian administrative address data
- **Tables**: provinces, regencies, districts, villages
- **Connection**: `$conn_alamat` / `query_alamat()` function
- **Usage**: Hierarchical address selection (Province → Regency → District → Village)

### 3. db_orang (People Database)
- **Purpose**: People management system with national geospatial data
- **Tables**: 19 tables + 6 views including people, addresses, provinces (38), regencies (541), districts (8K), villages (81K), GPS, boundaries, metadata
- **Connection**: `$conn_orang` / `query_orang()` function
- **Usage**: Active - people + addresses for all users, nasabah, cabang

## Integration Status

### ✅ Completed Integrations

1. **Address Database (db_alamat_simple)**
   - Updated `includes/alamat_helper.php` to use `query_alamat()`
   - Updated `api/alamat.php` to use the integrated helper
   - Updated all pages using address dropdowns:
     - pages/nasabah/tambah.php, edit.php
     - pages/cabang/tambah.php, edit.php, index.php
     - pages/bos/register.php, setup_headquarters.php
   - Models (Nasabah.php, Cabang.php) use `LEFT JOIN db_alamat_simple.provinces` etc.

2. **People Database (db_orang)** - ACTIVE
   - `includes/people_helper.php` - Functions for people + addresses CRUD
   - All users, nasabah, cabang linked to db_orang.people
   - Cross-DB columns: users.db_orang_person_id, nasabah.db_orang_user_id, cabang.db_orang_person_id
   - 15 people records (11 users + 2 nasabah + 2 cabang)
   - 4 address records (2 nasabah + 2 cabang)
   - Integrated in: approve_bos.php, bos_registration.php, pages/nasabah, pages/petugas, pages/cabang

3. **Helper Functions**
   - `includes/address_helper.php` - Direct integration with db_alamat_simple
   - `includes/people_helper.php` - Active integration with db_orang (createPersonWithAddress, getPersonByUserId, etc.)

## Database Configuration

File: `config/database.php`

```php
// Main connection (kewer - transactions)
$conn = new mysqli(DB_KEWER_HOST, DB_KEWER_USER, DB_KEWER_PASS, DB_KEWER_NAME);

// Address database connection
$conn_alamat = new mysqli(DB_ALAMAT_HOST, DB_ALAMAT_USER, DB_ALAMAT_PASS, DB_ALAMAT_NAME);

// People database connection
$conn_orang = new mysqli(DB_ORANG_HOST, DB_ORANG_USER, DB_ORANG_PASS, DB_ORANG_NAME);
```

## Query Functions

### Main Database (kewer)
```php
$data = query("SELECT * FROM nasabah WHERE id = ?", [$id]);
```

### Address Database (db_alamat_simple)
```php
$provinces = query_alamat("SELECT id, code, name FROM provinces ORDER BY name");
$regencies = query_alamat("SELECT id, code, name FROM regencies WHERE province_id = ?", [$province_id]);
```

### People Database (db_orang)
```php
$person = query_orang("SELECT * FROM people WHERE kewer_user_id = ?", [$user_id]);
$addresses = query_orang("SELECT * FROM addresses WHERE person_id = ?", [$person_id]);
// Cross-DB join for location names
$prov = query_alamat("SELECT name FROM provinces WHERE id = ?", [$province_id]);
```

## Address Helper Functions

File: `includes/alamat_helper.php`

Available functions:
- `getProvinces()` - Get all provinces
- `getProvinceById($id)` - Get single province
- `getRegenciesByProvince($provinceId)` - Get regencies by province
- `getRegencyById($id)` - Get single regency
- `getDistrictsByRegency($regencyId)` - Get districts by regency
- `getDistrictById($id)` - Get single district
- `getVillagesByDistrict($districtId)` - Get villages by district
- `getVillageById($id)` - Get single village
- `getFullAddressString($provinceId, $regencyId, $districtId, $villageId)` - Get formatted address
- `provinceDropdown($name, $selected, $attrs)` - Generate province dropdown HTML
- `regencyDropdown($name, $selected, $provinceId, $attrs)` - Generate regency dropdown HTML
- `districtDropdown($name, $selected, $regencyId, $attrs)` - Generate district dropdown HTML
- `villageDropdown($name, $selected, $districtId, $attrs)` - Generate village dropdown HTML

## API Endpoints

### Address API
File: `api/alamat.php`

Endpoints:
- `GET /api/alamat.php?action=provinces` - Get all provinces
- `GET /api/alamat.php?action=regencies&province_id={id}` - Get regencies by province
- `GET /api/alamat.php?action=districts&regency_id={id}` - Get districts by regency
- `GET /api/alamat.php?action=villages&district_id={id}` - Get villages by district

## Data Flow

### Address Selection Flow
1. User selects province → API calls `action=provinces`
2. User selects regency → API calls `action=regencies&province_id={id}`
3. User selects district → API calls `action=districts&regency_id={id}`
4. User selects village → API calls `action=villages&district_id={id}`
5. Form submits with province_id, regency_id, district_id, village_id
6. Data stored in kewer.nasabah/cabang/bos_registrations tables
7. Address display uses `getFullAddressString()` to format

### People + Address Flow (db_orang)
1. Create user/nasabah/cabang in kewer
2. Create person record in db_orang.people (kewer_user_id or kewer_nasabah_id)
3. Create address record in db_orang.addresses (person_id + location IDs)
4. Update kewer table with db_orang_person_id or db_orang_user_id
5. Address display queries db_alamat_simple for location names

### Database Cross-Reference
- kewer.nasabah/cabang/bos_registrations store province_id, regency_id, district_id, village_id
- These IDs reference db_alamat_simple tables
- kewer.users/nasabah/cabang have db_orang_person_id linking to db_orang.people.id
- Address display queries db_alamat_simple for names via db_orang.addresses
- No foreign key constraints (separate databases)

## Future Integration Opportunities

### db_orang Integration (ACTIVE)
The db_orang database provides:
- People management with kewer_user_id and kewer_nasabah_id links
- Multiple addresses per person
- Contact information (email, phone)
- Identity documents (KTP)
- National geospatial data (38 provinces, 541 regencies, 8K districts, 81K villages)

Current implementation:
- All users, nasabah, cabang linked to db_orang.people
- Address creation via createPersonWithAddress()
- Location data references db_alamat_simple for Sumut
- Helper functions integrated in pages and APIs

## Testing

To test the multi-database integration:

1. **Test Address Dropdowns**
   - Navigate to pages/nasabah/tambah.php
   - Check if province dropdown loads
   - Select province and verify regency dropdown loads
   - Continue through district and village

2. **Test Address Storage**
   - Create new nasabah with complete address
   - Verify IDs are stored in kewer.nasabah
   - Check address display shows correct location names

3. **Test API Endpoints**
   ```bash
   curl "http://localhost/kewer/api/alamat.php?action=provinces"
   curl "http://localhost/kewer/api/alamat.php?action=regencies&province_id=1"
   ```

## Troubleshooting

### Common Issues

**Issue**: Address dropdowns not loading
- **Solution**: Check if db_alamat_simple connection is working in config/database.php
- **Solution**: Verify query_alamat() function is accessible
- **Solution**: Check province_id=3 for Sumut (not 12)

**Issue**: Address display shows IDs instead of names
- **Solution**: Ensure getFullAddressString() is being called
- **Solution**: Check if db_alamat_simple has data
- **Solution**: Verify cross-DB link (db_orang.addresses → db_alamat_simple)

**Issue**: People data not syncing
- **Solution**: Check if db_orang_person_id is set in kewer.users/nasabah/cabang
- **Solution**: Verify query_orang() function is accessible
- **Solution**: Check people_helper.php integration in pages/APIs

**Issue**: Cross-database queries failing
- **Solution**: Cannot JOIN across databases directly, use separate queries
- **Solution**: Map IDs manually in application code

## Security Considerations

1. **Database Credentials**: Stored in config/database.php, ensure proper permissions
2. **SQL Injection**: All query functions use prepared statements
3. **API Authentication**: Address API requires login (requireLogin())
4. **Rate Limiting**: API has rate limiting implemented

## Performance

- Address data is static (provinces, regencies, districts, villages)
- Consider caching address dropdown data
- db_alamat_simple is read-only for address reference
- db_orang.people and addresses are read-write
- Cross-DB queries may have slight overhead, consider caching people data
