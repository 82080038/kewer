<?php
/**
 * People Helper Functions
 * Functions to integrate with db_orang database
 * 
 * Schema:
 *   db_orang.people    — data orang (linked to kewer.users or kewer.nasabah)
 *   db_orang.addresses — alamat orang (linked to people.id)
 *   Referensi lokasi   → db_alamat_simple.provinces/regencies/districts/villages
 */

/**
 * Create a person record in db_orang.people
 * Returns the person ID or false on failure
 */
function createPerson($data) {
    $sql = "INSERT INTO people (
        kewer_user_id, kewer_nasabah_id, nama, ktp, telp, email,
        jenis_kelamin, tanggal_lahir, tempat_lahir, pekerjaan, catatan
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $result = query_orang($sql, [
        $data['kewer_user_id'] ?? null,
        $data['kewer_nasabah_id'] ?? null,
        $data['nama'] ?? '',
        $data['ktp'] ?? null,
        $data['telp'] ?? null,
        $data['email'] ?? null,
        $data['jenis_kelamin'] ?? null,
        $data['tanggal_lahir'] ?? null,
        $data['tempat_lahir'] ?? null,
        $data['pekerjaan'] ?? null,
        $data['catatan'] ?? null
    ]);

    if ($result !== false && $result > 0) {
        $last = query_orang("SELECT LAST_INSERT_ID() as id");
        return (is_array($last) && isset($last[0])) ? (int)$last[0]['id'] : false;
    }
    return false;
}

/**
 * Create an address for a person in db_orang.addresses
 */
function createPersonAddress($person_id, $data) {
    $sql = "INSERT INTO addresses (
        person_id, label, street_address, house_number, rt, rw,
        province_id, regency_id, district_id, village_id, postal_code,
        latitude, longitude, is_primary, catatan
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    return query_orang($sql, [
        $person_id,
        $data['label'] ?? 'rumah',
        $data['street_address'] ?? '',
        $data['house_number'] ?? '',
        $data['rt'] ?? null,
        $data['rw'] ?? null,
        $data['province_id'] ?? null,
        $data['regency_id'] ?? null,
        $data['district_id'] ?? null,
        $data['village_id'] ?? null,
        $data['postal_code'] ?? '',
        $data['latitude'] ?? null,
        $data['longitude'] ?? null,
        $data['is_primary'] ?? 1,
        $data['catatan'] ?? null
    ]);
}

/**
 * Get person by kewer_user_id
 */
function getPersonByUserId($kewer_user_id) {
    if (!$kewer_user_id) return null;
    $result = query_orang("SELECT * FROM people WHERE kewer_user_id = ? LIMIT 1", [$kewer_user_id]);
    return (is_array($result) && isset($result[0])) ? $result[0] : null;
}

/**
 * Get person by kewer_nasabah_id
 */
function getPersonByNasabahId($kewer_nasabah_id) {
    if (!$kewer_nasabah_id) return null;
    $result = query_orang("SELECT * FROM people WHERE kewer_nasabah_id = ? LIMIT 1", [$kewer_nasabah_id]);
    return (is_array($result) && isset($result[0])) ? $result[0] : null;
}

/**
 * Get person addresses from db_orang (by person_id)
 */
function getPersonAddresses($person_id) {
    if (!$person_id) return [];
    $addresses = query_orang("
        SELECT a.*,
               p.name as province_name, r.name as regency_name,
               d.name as district_name, v.name as village_name
        FROM addresses a
        LEFT JOIN db_alamat_simple.provinces p ON a.province_id = p.id
        LEFT JOIN db_alamat_simple.regencies r ON a.regency_id = r.id
        LEFT JOIN db_alamat_simple.districts d ON a.district_id = d.id
        LEFT JOIN db_alamat_simple.villages v ON a.village_id = v.id
        WHERE a.person_id = ?
        ORDER BY a.is_primary DESC, a.created_at DESC
    ", [$person_id]);
    return is_array($addresses) ? $addresses : [];
}

/**
 * Get primary address for a person
 */
function getPrimaryAddress($person_id) {
    if (!$person_id) return null;
    $address = query_orang("
        SELECT a.*,
               p.name as province_name, r.name as regency_name,
               d.name as district_name, v.name as village_name
        FROM addresses a
        LEFT JOIN db_alamat_simple.provinces p ON a.province_id = p.id
        LEFT JOIN db_alamat_simple.regencies r ON a.regency_id = r.id
        LEFT JOIN db_alamat_simple.districts d ON a.district_id = d.id
        LEFT JOIN db_alamat_simple.villages v ON a.village_id = v.id
        WHERE a.person_id = ? AND a.is_primary = 1
        LIMIT 1
    ", [$person_id]);
    return (is_array($address) && isset($address[0])) ? $address[0] : null;
}

/**
 * Update person address
 */
function updatePersonAddress($address_id, $data) {
    $sql = "UPDATE addresses SET
        street_address = ?, house_number = ?, rt = ?, rw = ?,
        province_id = ?, regency_id = ?, district_id = ?, village_id = ?,
        postal_code = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?";

    return query_orang($sql, [
        $data['street_address'] ?? '',
        $data['house_number'] ?? '',
        $data['rt'] ?? null,
        $data['rw'] ?? null,
        $data['province_id'] ?? null,
        $data['regency_id'] ?? null,
        $data['district_id'] ?? null,
        $data['village_id'] ?? null,
        $data['postal_code'] ?? '',
        $address_id
    ]);
}

/**
 * Set primary address
 */
function setPrimaryAddress($person_id, $address_id) {
    query_orang("UPDATE addresses SET is_primary = 0 WHERE person_id = ?", [$person_id]);
    return query_orang("UPDATE addresses SET is_primary = 1 WHERE id = ? AND person_id = ?", [$address_id, $person_id]);
}

/**
 * Delete person address
 */
function deletePersonAddress($address_id, $person_id) {
    return query_orang("DELETE FROM addresses WHERE id = ? AND person_id = ?", [$address_id, $person_id]);
}

/**
 * Cari person di db_orang berdasarkan NIK/KTP.
 * Return row people atau null jika tidak ditemukan.
 */
function findPersonByKtp($ktp) {
    if (!$ktp || strlen(trim($ktp)) !== 16) return null;
    $result = query_orang("SELECT * FROM people WHERE ktp = ? LIMIT 1", [trim($ktp)]);
    return (is_array($result) && isset($result[0])) ? $result[0] : null;
}

/**
 * Cek apakah NIK sudah terdaftar sebagai user AKTIF di koperasi (owner_bos_id) tertentu.
 * Digunakan saat pendaftaran petugas baru untuk mencegah duplikat aktif.
 *
 * Return: array user jika sudah aktif, null jika belum/sudah resign.
 */
function isKtpActiveInKoperasi($ktp, $owner_bos_id) {
    if (!$ktp || !$owner_bos_id) return null;

    // Cari person di db_orang dulu
    $person = findPersonByKtp($ktp);
    if (!$person) return null;

    // Cek apakah kewer_user_id-nya aktif di koperasi ini
    if (!$person['kewer_user_id']) return null;

    $user = query(
        "SELECT id, username, nama, role, status FROM users
         WHERE id = ? AND owner_bos_id = ? AND status = 'aktif'",
        [$person['kewer_user_id'], $owner_bos_id]
    );
    return (is_array($user) && isset($user[0])) ? $user[0] : null;
}

/**
 * Ambil riwayat koperasi dari NIK — semua user (aktif maupun nonaktif) yang
 * terhubung ke NIK ini di seluruh koperasi dalam platform.
 * Berguna untuk referensi bos baru saat mendaftarkan petugas yang pernah bekerja di tempat lain.
 *
 * Return: array riwayat [{user_id, username, role, status, nama_koperasi, cabang, tanggal_masuk}]
 */
function getKtpKoperasiHistory($ktp) {
    if (!$ktp) return [];

    // Temukan semua kewer_user_id yang pernah memakai KTP ini
    // KTP bisa muncul di people dengan kewer_user_id berbeda jika pernah resign & daftar ulang
    $persons = query_orang("SELECT kewer_user_id FROM people WHERE ktp = ? AND kewer_user_id IS NOT NULL", [trim($ktp)]);
    if (!is_array($persons) || empty($persons)) return [];

    $user_ids = array_column($persons, 'kewer_user_id');
    $placeholders = implode(',', array_fill(0, count($user_ids), '?'));

    $rows = query(
        "SELECT u.id, u.username, u.nama, u.role, u.status, u.tanggal_masuk,
                c.nama_cabang, b.nama as nama_bos
         FROM users u
         LEFT JOIN cabang c ON u.cabang_id = c.id
         LEFT JOIN users b ON u.owner_bos_id = b.id
         WHERE u.id IN ($placeholders)
         ORDER BY u.created_at DESC",
        $user_ids
    );
    return is_array($rows) ? $rows : [];
}

/**
 * Sinkronisasi: setelah user di-nonaktifkan (resign), update status people di db_orang.
 * Data identitas TIDAK dihapus — hanya status yang diupdate.
 */
function syncPersonStatusOnResign($kewer_user_id) {
    if (!$kewer_user_id) return false;
    return query_orang(
        "UPDATE people SET status = 'nonaktif', updated_at = CURRENT_TIMESTAMP WHERE kewer_user_id = ?",
        [$kewer_user_id]
    );
}

/**
 * Sinkronisasi: saat petugas yang pernah resign daftar ulang di koperasi baru,
 * buat record people baru (dengan kewer_user_id baru) yang tetap memiliki KTP sama.
 * Data lama (kewer_user_id lama) tidak diubah — histori terjaga.
 */
function createPersonForReturningStaff($new_user_id, $ktp, $nama, $extra = []) {
    return createPerson(array_merge([
        'kewer_user_id'  => $new_user_id,
        'nama'           => $nama,
        'ktp'            => $ktp,
        'telp'           => $extra['telp'] ?? null,
        'email'          => $extra['email'] ?? null,
        'jenis_kelamin'  => $extra['jenis_kelamin'] ?? null,
        'tanggal_lahir'  => $extra['tanggal_lahir'] ?? null,
        'tempat_lahir'   => $extra['tempat_lahir'] ?? null,
        'pekerjaan'      => $extra['pekerjaan'] ?? null,
    ], $extra));
}

/**
 * Convenience: Create person + address in one call
 * Used when registering bos or creating nasabah
 */
function createPersonWithAddress($person_data, $address_data) {
    $person_id = createPerson($person_data);
    if (!$person_id) return false;

    if (!empty($address_data['street_address']) || !empty($address_data['province_id'])) {
        createPersonAddress($person_id, $address_data);
    }
    return $person_id;
}
