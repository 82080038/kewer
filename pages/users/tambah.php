<?php
require_once '../../includes/functions.php';
requireLogin();

if (!hasRole('superadmin')) {
    header('Location: ../../dashboard.php');
    exit();
}

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $cabang_id = $_POST['cabang_id'] ?? null;
    $gaji = $_POST['gaji'] ?? 0;
    $limit_kasbon = $_POST['limit_kasbon'] ?? 0;
    $status = $_POST['status'] ?? 'aktif';
    
    // Check duplicate username
    $check = query("SELECT id FROM users WHERE username = ?", [$username]);
    if ($check) {
        $_SESSION['error'] = 'Username sudah digunakan';
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $result = query("INSERT INTO users (username, password, nama, email, role, cabang_id, gaji, limit_kasbon, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $username,
            $hashed_password,
            $nama,
            $email,
            $role,
            $cabang_id,
            $gaji,
            $limit_kasbon,
            $status
        ]);
        
        if ($result) {
            // Log audit
            logAudit('CREATE', 'users', $result, null, ['username' => $username, 'nama' => $nama, 'role' => $role]);
            
            $_SESSION['success'] = 'User berhasil ditambahkan';
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['error'] = 'Gagal menambahkan user';
        }
    }
}

header('Location: index.php');
exit();
