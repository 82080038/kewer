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

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Development quick login check (ONLY IN DEVELOPMENT MODE)
    if (APP_ENV === 'development') {
        $dev_credentials = [
            getenv('DEV_ADMIN_USER') => getenv('DEV_ADMIN_PASS'),
            getenv('DEV_OWNER_USER') => getenv('DEV_OWNER_PASS'),
            getenv('DEV_MANAGER_USER') => getenv('DEV_MANAGER_PASS'),
            getenv('DEV_PETUGAS1_USER') => getenv('DEV_PETUGAS1_PASS'),
            getenv('DEV_PETUGAS2_USER') => getenv('DEV_PETUGAS2_PASS'),
            getenv('DEV_KARYAWAN1_USER') => getenv('DEV_KARYAWAN1_PASS'),
            getenv('DEV_KARYAWAN2_USER') => getenv('DEV_KARYAWAN2_PASS'),
        ];
        
        if (isset($dev_credentials[$username]) && $password === $dev_credentials[$username]) {
            // Get user from database for session
            $user = query("SELECT * FROM users WHERE username = ? AND status = 'aktif'", [$username]);
            if ($user) {
                $_SESSION['user_id'] = $user[0]['id'];
                $_SESSION['cabang_id'] = $user[0]['cabang_id'];
                session_write_close();
                header('Location: /kewer/dashboard.php');
                exit();
            }
        }
    }
    
    // Normal login process
    $user = query("SELECT * FROM users WHERE username = ? AND status = 'aktif'", [$username]);
    
    if ($user && password_verify($password, $user[0]['password'])) {
        $_SESSION['user_id'] = $user[0]['id'];
        $_SESSION['cabang_id'] = $user[0]['cabang_id'];
        
        session_write_close();
        header('Location: /kewer/dashboard.php');
        exit();
    } else {
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
