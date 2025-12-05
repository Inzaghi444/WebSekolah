<?php
require_once 'config.php';

if (isLoggedIn()) {
    if (hasRole('admin')) redirect('admin/dashboard.php');
    elseif (hasRole('guru')) redirect('guru/dashboard.php');
    elseif (hasRole('siswa')) redirect('siswa/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $query = $conn->query("SELECT * FROM users WHERE username = '$username'");
    
    if ($query->num_rows > 0) {
        $user = $query->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            
            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } elseif ($user['role'] === 'guru') {
                redirect('guru/dashboard.php');
            } else {
                redirect('siswa/dashboard.php');
            }
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Sekolah</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Login Sistem Sekolah</h2>
                <p>Masukkan username dan password Anda</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="login-footer">
                <a href="index.php">← Kembali ke Beranda</a>
            </div>
            
            <div class="login-demo">
                <p><strong>Demo Akun:</strong></p>
                <p>Admin: admin / password</p>
                <p>Guru: guru1 / password</p>
                <p>Siswa: siswa1 / password</p>
            </div>
        </div>
    </div>
</body>
</html>