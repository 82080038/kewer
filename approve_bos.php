<?php
require_once 'config/path.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/config/database.php';

// Get pending bos registration
$pending = query("SELECT * FROM bos_registrations WHERE status = 'pending' LIMIT 1");

if (!$pending) {
    echo "No pending bos registrations found\n";
    exit;
}

$reg = $pending[0];
echo "Approving bos registration: ID {$reg['id']}, Username {$reg['username']}\n";

// Start transaction
$conn->begin_transaction();

try {
    // Create user account
    $result = query(
        "INSERT INTO users (username, password, nama, email, role, cabang_id, owner_bos_id, status) VALUES (?, ?, ?, ?, 'bos', NULL, NULL, 'aktif')",
        [$reg['username'], $reg['password_hash'], $reg['nama'], $reg['email']]
    );
    
    if (!$result) {
        throw new Exception("Failed to create user");
    }
    
    $bos_user_id = query("SELECT LAST_INSERT_ID() as id")[0]['id'];
    echo "User created: ID {$bos_user_id}\n";
    
    // Create person record in db_orang for address management
    try {
        require_once BASE_PATH . '/includes/people_helper.php';
        $person_id = createPersonWithAddress(
            [
                'kewer_user_id' => $bos_user_id,
                'nama' => $reg['nama'],
                'email' => $reg['email'] ?? null,
                'telp' => $reg['telp'] ?? null
            ],
            [
                'label' => 'kantor',
                'street_address' => $reg['alamat_usaha'] ?? '',
                'province_id' => $reg['province_id'] ?? null,
                'regency_id' => $reg['regency_id'] ?? null,
                'district_id' => $reg['district_id'] ?? null,
                'village_id' => $reg['village_id'] ?? null
            ]
        );
        echo $person_id ? "Person record created: ID {$person_id}\n" : "Warning: Person record not created\n";
    } catch (Exception $e) {
        echo "Warning: Failed to create person record: " . $e->getMessage() . "\n";
    }
    
    // Update registration status
    $result = query(
        "UPDATE bos_registrations SET status = 'approved', approved_at = CURRENT_TIMESTAMP, approved_by = ? WHERE id = ?",
        [1, $reg['id']]
    );
    
    if (!$result) {
        throw new Exception("Failed to update registration status");
    }
    
    $conn->commit();
    echo "✅ Bos registration approved successfully\n";
    echo "Username: {$reg['username']}\n";
    echo "Nama: {$reg['nama']}\n";
    echo "User ID: {$bos_user_id}\n";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Failed to approve bos registration\n";
    echo "Error: " . $e->getMessage() . "\n";
}

