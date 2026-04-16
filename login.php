<?php
require_once 'config/path.php';
require_once BASE_PATH . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit();
}

$error = '';

// Check if session timeout
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $error = 'Session Anda telah expired. Silakan login kembali.';
}

// Test-specific login for Puppeteer (GET request with credentials)
if (isset($_GET['test_login']) && $_GET['test_login'] === 'true') {
    $username = $_GET['username'] ?? 'admin';
    $password = $_GET['password'] ?? 'password';
    
    error_log("Test login attempt - Username: {$username}");
    
    $dev_credentials = [
        'admin' => 'password',
        'owner' => 'password',
        'manager1' => 'password',
        'petugas1' => 'password',
        'petugas2' => 'password',
        'karyawan1' => 'password',
        'karyawan2' => 'password',
    ];
    
    error_log("Dev credentials check - Username: {$username}, Expected pass: " . ($dev_credentials[$username] ?? 'not found'));
    
    if (isset($dev_credentials[$username]) && $password === $dev_credentials[$username]) {
        error_log("Development credentials matched, querying database");
        $user = query("SELECT * FROM users WHERE username = ? AND status = 'aktif'", [$username]);
        if ($user) {
            error_log("User found in database: ID=" . $user[0]['id']);
            $_SESSION['user_id'] = $user[0]['id'];
            $_SESSION['cabang_id'] = $user[0]['cabang_id'];
            error_log("Session set before redirect - user_id: " . $_SESSION['user_id'] . ", cabang_id: " . $_SESSION['cabang_id']);
            session_write_close();
            error_log("Session closed, sending redirect header");
            header('Location: /kewer/dashboard.php');
            exit();
        } else {
            error_log("User NOT found in database for: {$username}");
        }
    } else {
        error_log("Development credentials NOT matched for: {$username}");
    }
}

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Log login attempt
    error_log("Login attempt - Username: {$username}, IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    error_log("Session status before login: " . session_status());
    error_log("Session ID: " . session_id());
    
    // Development quick login check (ONLY IN DEVELOPMENT MODE)
    if (APP_ENV === 'development') {
        error_log("Development mode login check for: {$username}");
        $dev_credentials = [
            getenv('DEV_ADMIN_USER') => getenv('DEV_ADMIN_PASS'),
            getenv('DEV_OWNER_USER') => getenv('DEV_OWNER_PASS'),
            getenv('DEV_MANAGER_USER') => getenv('DEV_MANAGER_PASS'),
            getenv('DEV_PETUGAS1_USER') => getenv('DEV_PETUGAS1_PASS'),
            getenv('DEV_PETUGAS2_USER') => getenv('DEV_PETUGAS2_PASS'),
            getenv('DEV_KARYAWAN1_USER') => getenv('DEV_KARYAWAN1_PASS'),
            getenv('DEV_KARYAWAN2_USER') => getenv('DEV_KARYAWAN2_PASS'),
        ];
        
        error_log("Dev credentials loaded: " . count($dev_credentials) . " users");
        
        if (isset($dev_credentials[$username]) && $password === $dev_credentials[$username]) {
            error_log("Development credentials matched for: {$username}");
            // Get user from database for session
            $user = query("SELECT * FROM users WHERE username = ? AND status = 'aktif'", [$username]);
            if ($user) {
                error_log("User found in database: ID=" . $user[0]['id']);
                $_SESSION['user_id'] = $user[0]['id'];
                $_SESSION['cabang_id'] = $user[0]['cabang_id'];
                error_log("Session set - user_id: " . $_SESSION['user_id'] . ", cabang_id: " . $_SESSION['cabang_id']);
                session_write_close();
                error_log("Session closed, redirecting to dashboard");
                header('Location: /kewer/dashboard.php');
                exit();
            } else {
                error_log("User NOT found in database for: {$username}");
            }
        } else {
            error_log("Development credentials NOT matched for: {$username}");
        }
    }
    
    // Normal login process
    error_log("Normal login process for: {$username}");
    $user = query("SELECT * FROM users WHERE username = ? AND status = 'aktif'", [$username]);
    
    if ($user && password_verify($password, $user[0]['password'])) {
        error_log("Password verified for: {$username}");
        $_SESSION['user_id'] = $user[0]['id'];
        $_SESSION['cabang_id'] = $user[0]['cabang_id'];
        error_log("Session set - user_id: " . $_SESSION['user_id'] . ", cabang_id: " . $_SESSION['cabang_id']);
        
        session_write_close();
        error_log("Session closed, redirecting to dashboard");
        header('Location: /kewer/dashboard.php');
        exit();
    } else {
        error_log("Login failed for: {$username}");
        $error = 'Username atau password salah';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h2><?php echo APP_NAME; ?></h2>
            <p class="text-muted">Sistem Pinjaman Modal Pedagang</p>
        </div>
        
        <?php if (APP_ENV === 'development'): ?>
        <div class="alert alert-warning mb-3">
            <strong>⚠️ Development Mode</strong><br>
            Quick login aktif. Jangan gunakan di production!
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" id="loginForm">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        
        <div class="mt-3">
            <p class="text-center text-muted mb-2">Quick Login (Development):</p>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-danger" onclick="quickLogin('admin', 'password')">
                    Superadmin: admin
                </button>
                <button type="button" class="btn btn-danger" onclick="quickLogin('owner', 'password')">
                    Superadmin: owner
                </button>
                <button type="button" class="btn btn-warning" onclick="quickLogin('manager1', 'password')">
                    Admin: manager1
                </button>
                <button type="button" class="btn btn-info" onclick="quickLogin('petugas1', 'password')">
                    Petugas: petugas1
                </button>
                <button type="button" class="btn btn-info" onclick="quickLogin('petugas2', 'password')">
                    Petugas: petugas2
                </button>
                <button type="button" class="btn btn-secondary" onclick="quickLogin('karyawan1', 'password')">
                    Karyawan: karyawan1
                </button>
                <button type="button" class="btn btn-secondary" onclick="quickLogin('karyawan2', 'password')">
                    Karyawan: karyawan2
                </button>
            </div>
        </div>
        
        <script>
            function quickLogin(username, password) {
                document.querySelector('input[name="username"]').value = username;
                document.querySelector('input[name="password"]').value = password;
                document.querySelector('form').submit();
            }
        </script>
    </div>
</body>
</html>
