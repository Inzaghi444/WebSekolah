<?php
require_once '../config.php';
requireRole('admin');

// Statistik
$totalSiswa = $conn->query("SELECT COUNT(*) as total FROM siswa")->fetch_assoc()['total'];
$totalGuru = $conn->query("SELECT COUNT(*) as total FROM guru")->fetch_assoc()['total'];
$totalKegiatan = $conn->query("SELECT COUNT(*) as total FROM kegiatan_sekolah")->fetch_assoc()['total'];
$totalMapel = $conn->query("SELECT COUNT(*) as total FROM mata_pelajaran")->fetch_assoc()['total'];
$totalPrestasi = $conn->query("SELECT COUNT(*) as total FROM prestasi_sekolah")->fetch_assoc()['total'];

// Handle Update Profil Sekolah
if (isset($_POST['update_profil'])) {
    $jumlah_siswa = intval($_POST['jumlah_siswa_aktif']);
    $jumlah_guru = intval($_POST['jumlah_guru_aktif']);
    $jumlah_kelas = intval($_POST['jumlah_ruang_kelas']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $telepon = $conn->real_escape_string($_POST['telepon']);
    $email = $conn->real_escape_string($_POST['email']);
    $visi = $conn->real_escape_string($_POST['visi']);
    $misi = $conn->real_escape_string($_POST['misi']);
    
    $update = $conn->query("UPDATE profil_sekolah SET 
        jumlah_siswa_aktif = $jumlah_siswa,
        jumlah_guru_aktif = $jumlah_guru,
        jumlah_ruang_kelas = $jumlah_kelas,
        alamat = '$alamat',
        telepon = '$telepon',
        email = '$email',
        visi = '$visi',
        misi = '$misi'
        WHERE id = 1");
    
    if ($update) {
        echo "<script>alert('✓ Profil sekolah berhasil diperbarui!');</script>";
    }
}

// Ambil profil sekolah
$profil = $conn->query("SELECT * FROM profil_sekolah LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
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
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="profil_sekolah.php">🏫 Profil Sekolah</a></li>
                <li><a href="kelola_guru.php">👨‍🏫 Kelola Guru</a></li>
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
                <h1 class="dashboard-title">Dashboard Admin</h1>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-number"><?= $totalSiswa ?></div>
                    <div class="stat-label">Total Siswa</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="stat-number"><?= $totalGuru ?></div>
                    <div class="stat-label">Total Guru</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-number"><?= $totalMapel ?></div>
                    <div class="stat-label">Mata Pelajaran</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📸</div>
                    <div class="stat-number"><?= $totalKegiatan ?></div>
                    <div class="stat-label">Kegiatan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-number"><?= $totalPrestasi ?></div>
                    <div class="stat-label">Prestasi</div>
                </div>
            </div>

            <!-- Form Update Profil Sekolah -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Update Profil Sekolah</h2>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Jumlah Siswa Aktif</label>
                        <input type="number" name="jumlah_siswa_aktif" value="<?= $profil['jumlah_siswa_aktif'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Jumlah Guru Aktif</label>
                        <input type="number" name="jumlah_guru_aktif" value="<?= $profil['jumlah_guru_aktif'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Jumlah Ruang Kelas</label>
                        <input type="number" name="jumlah_ruang_kelas" value="<?= $profil['jumlah_ruang_kelas'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Alamat Sekolah</label>
                        <textarea name="alamat" required><?= $profil['alamat'] ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" name="telepon" value="<?= $profil['telepon'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= $profil['email'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Visi</label>
                        <textarea name="visi" required><?= $profil['visi'] ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Misi</label>
                        <textarea name="misi" required><?= $profil['misi'] ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profil" class="btn btn-primary">💾 Simpan Perubahan</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>