<?php
require_once '../config.php';
requireRole('admin');

// Handle Tambah Guru
if (isset($_POST['tambah_guru'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama = $conn->real_escape_string($_POST['nama']);
    $nip = $conn->real_escape_string($_POST['nip']);
    $mata_pelajaran = $conn->real_escape_string($_POST['mata_pelajaran']);
    $pendidikan = $conn->real_escape_string($_POST['pendidikan']);
    $kontak = $conn->real_escape_string($_POST['kontak']);
    $email = $conn->real_escape_string($_POST['email']);
    $tampil = isset($_POST['tampil_di_beranda']) ? 1 : 0;
    
    // Upload foto
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto = uploadFile($_FILES['foto'], 'guru');
    }
    
    // Insert user
    $insertUser = $conn->query("INSERT INTO users (username, password, role, nama_lengkap) 
                                VALUES ('$username', '$password', 'guru', '$nama')");
    
    if ($insertUser) {
        $user_id = $conn->insert_id;
        
        // Insert guru
        $insertGuru = $conn->query("INSERT INTO guru (user_id, nip, nama, foto, mata_pelajaran, pendidikan, kontak, email, tampil_di_beranda) 
                                    VALUES ($user_id, '$nip', '$nama', '$foto', '$mata_pelajaran', '$pendidikan', '$kontak', '$email', $tampil)");
        
        if ($insertGuru) {
            echo "<script>alert('✓ Guru berhasil ditambahkan!'); window.location.href='kelola_guru.php';</script>";
        }
    } else {
        echo "<script>alert('✕ Username sudah digunakan!');</script>";
    }
}

// Handle Hapus Guru
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM guru WHERE id = $id");
    echo "<script>alert('✓ Guru berhasil dihapus!'); window.location.href='kelola_guru.php';</script>";
}

// Handle Toggle Tampil
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE guru SET tampil_di_beranda = NOT tampil_di_beranda WHERE id = $id");
    echo "<script>window.location.href='kelola_guru.php';</script>";
}

// Ambil semua guru
$guruQuery = $conn->query("SELECT g.*, u.username FROM guru g 
                           LEFT JOIN users u ON g.user_id = u.id 
                           ORDER BY g.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Guru - Admin</title>
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
                <li><a href="kelola_guru.php" class="active">👨‍🏫 Kelola Guru</a></li>
                <li><a href="kelola_siswa.php">👨‍🎓 Kelola Siswa</a></li>
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
                <h1 class="dashboard-title">Kelola Data Guru</h1>
            </div>

            <!-- Form Tambah Guru -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Tambah Guru Baru</h2>
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
                            <label>NIP *</label>
                            <input type="text" name="nip" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Lengkap *</label>
                            <input type="text" name="nama" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Mata Pelajaran *</label>
                            <input type="text" name="mata_pelajaran" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Pendidikan *</label>
                            <input type="text" name="pendidikan" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Kontak</label>
                            <input type="text" name="kontak">
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email">
                        </div>
                        
                        <div class="form-group">
                            <label>Foto Profil</label>
                            <input type="file" name="foto" accept="image/*">
                        </div>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" name="tampil_di_beranda" checked>
                                Tampilkan di Beranda
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" name="tambah_guru" class="btn btn-primary">➕ Tambah Guru</button>
                </form>
            </div>

            <!-- Daftar Guru -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Guru</h2>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Mata Pelajaran</th>
                                <th>Pendidikan</th>
                                <th>Username</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($guru = $guruQuery->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($guru['foto']): ?>
                                        <img src="../<?= UPLOAD_DIR . $guru['foto'] ?>" 
                                             style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                            👨‍🏫
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= $guru['nip'] ?></td>
                                <td><?= $guru['nama'] ?></td>
                                <td><?= $guru['mata_pelajaran'] ?></td>
                                <td><?= $guru['pendidikan'] ?></td>
                                <td><?= $guru['username'] ?></td>
                                <td>
                                    <?php if ($guru['tampil_di_beranda']): ?>
                                        <span class="badge badge-success">Tampil</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Tersembunyi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?toggle=<?= $guru['id'] ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                        👁 Toggle
                                    </a>
                                    <a href="?hapus=<?= $guru['id'] ?>" 
                                       onclick="return confirm('Yakin hapus guru ini?')" 
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