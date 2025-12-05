<?php
require_once '../config.php';
requireRole('admin');

// Handle Tambah Siswa
if (isset($_POST['tambah_siswa'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama = $conn->real_escape_string($_POST['nama']);
    $nis = $conn->real_escape_string($_POST['nis']);
    $kelas = $conn->real_escape_string($_POST['kelas']);
    
    // Upload foto
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto = uploadFile($_FILES['foto'], 'siswa');
    }
    
    // Insert user
    $insertUser = $conn->query("INSERT INTO users (username, password, role, nama_lengkap) 
                                VALUES ('$username', '$password', 'siswa', '$nama')");
    
    if ($insertUser) {
        $user_id = $conn->insert_id;
        
        // Insert siswa
        $insertSiswa = $conn->query("INSERT INTO siswa (user_id, nis, nama, kelas, foto) 
                                     VALUES ($user_id, '$nis', '$nama', '$kelas', '$foto')");
        
        if ($insertSiswa) {
            echo "<script>alert('✓ Siswa berhasil ditambahkan!'); window.location.href='kelola_siswa.php';</script>";
        }
    } else {
        echo "<script>alert('✕ Username sudah digunakan!');</script>";
    }
}

// Handle Hapus Siswa
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM siswa WHERE id = $id");
    echo "<script>alert('✓ Siswa berhasil dihapus!'); window.location.href='kelola_siswa.php';</script>";
}

// Ambil semua siswa
$siswaQuery = $conn->query("SELECT s.*, u.username FROM siswa s 
                            LEFT JOIN users u ON s.user_id = u.id 
                            ORDER BY s.kelas, s.nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
                <p style="font-size: 0.9rem; opacity: 0.8;"><?= $_SESSION['nama_lengkap'] ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="profil_sekolah.php">🏫 Profil Sekolah</a></li>
                <li><a href="kelola_guru.php">👨‍🏫 Kelola Guru</a></li>
                <li><a href="kelola_siswa.php" class="active">👨‍🎓 Kelola Siswa</a></li>
                <li><a href="kelola_kegiatan.php">📸 Kelola Kegiatan</a></li>
                <li><a href="kelola_prestasi.php">🏆 Kelola Prestasi</a></li>
                <li><a href="kelola_mapel.php">📚 Mata Pelajaran</a></li>
                <li><a href="../index.php">🏠 Ke Beranda</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Kelola Data Siswa</h1>
            </div>

            <!-- Form Tambah Siswa -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Tambah Siswa Baru</h2>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Username Login *</label>
                            <input type="text" name="username" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Password *</label>
                            <input type="password" name="password" required>
                        </div>
                        
                        <div class="form-group">
                            <label>NIS *</label>
                            <input type="text" name="nis" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Lengkap *</label>
                            <input type="text" name="nama" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Kelas *</label>
                            <select name="kelas" required>
                                <option value="">Pilih Kelas</option>
                                <option value="X-1">X-1</option>
                                <option value="X-2">X-2</option>
                                <option value="XI-1">XI-1</option>
                                <option value="XI-2">XI-2</option>
                                <option value="XII-1">XII-1</option>
                                <option value="XII-2">XII-2</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Foto Profil</label>
                            <input type="file" name="foto" accept="image/*">
                        </div>
                    </div>
                    
                    <button type="submit" name="tambah_siswa" class="btn btn-primary">➕ Tambah Siswa</button>
                </form>
            </div>

            <!-- Daftar Siswa -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Siswa</h2>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Username</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($siswa = $siswaQuery->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($siswa['foto']): ?>
                                        <img src="../<?= UPLOAD_DIR . $siswa['foto'] ?>" 
                                             style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                            👨‍🎓
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= $siswa['nis'] ?></td>
                                <td><?= $siswa['nama'] ?></td>
                                <td><span class="badge badge-info"><?= $siswa['kelas'] ?></span></td>
                                <td><?= $siswa['username'] ?></td>
                                <td>
                                    <a href="?hapus=<?= $siswa['id'] ?>" 
                                       onclick="return confirm('Yakin hapus siswa ini?')" 
                                       class="btn btn-danger" 
                                       style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                        🗑 Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>