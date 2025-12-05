<?php
require_once '../config.php';
requireRole('admin');

// Handle Update Profil
if (isset($_POST['update_profil'])) {
    $nama_sekolah = $conn->real_escape_string($_POST['nama_sekolah']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $telepon = $conn->real_escape_string($_POST['telepon']);
    $email = $conn->real_escape_string($_POST['email']);
    $visi = $conn->real_escape_string($_POST['visi']);
    $misi = $conn->real_escape_string($_POST['misi']);
    $jumlah_siswa = intval($_POST['jumlah_siswa_aktif']);
    $jumlah_guru = intval($_POST['jumlah_guru_aktif']);
    $jumlah_kelas = intval($_POST['jumlah_ruang_kelas']);
    
    $update = $conn->query("UPDATE profil_sekolah SET 
        nama_sekolah = '$nama_sekolah',
        alamat = '$alamat',
        telepon = '$telepon',
        email = '$email',
        visi = '$visi',
        misi = '$misi',
        jumlah_siswa_aktif = $jumlah_siswa,
        jumlah_guru_aktif = $jumlah_guru,
        jumlah_ruang_kelas = $jumlah_kelas
        WHERE id = 1");
    
    if ($update) {
        echo "<script>alert('✓ Profil sekolah berhasil diperbarui!'); window.location.href='profil_sekolah.php';</script>";
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
    <title>Profil Sekolah - Admin</title>
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
                <li><a href="profil_sekolah.php" class="active">🏫 Profil Sekolah</a></li>
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
                <h1 class="dashboard-title">Edit Profil Sekolah</h1>
                <a href="../index.php" target="_blank" class="btn btn-secondary">👁 Preview Beranda</a>
            </div>

            <!-- Form Edit Profil Sekolah -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Informasi Sekolah</h2>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Nama Sekolah *</label>
                        <input type="text" name="nama_sekolah" value="<?= $profil['nama_sekolah'] ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Alamat Lengkap *</label>
                        <textarea name="alamat" rows="3" required><?= $profil['alamat'] ?></textarea>
                        <small style="color: #6b7280;">Masukkan alamat lengkap dengan kode pos</small>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Telepon *</label>
                            <input type="text" name="telepon" value="<?= $profil['telepon'] ?>" required placeholder="Contoh: 0411-123456">
                        </div>
                        
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" value="<?= $profil['email'] ?>" required placeholder="Contoh: info@sekolah.sch.id">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Visi Sekolah *</label>
                        <textarea name="visi" rows="3" required><?= $profil['visi'] ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Misi Sekolah *</label>
                        <textarea name="misi" rows="6" required><?= $profil['misi'] ?></textarea>
                        <small style="color: #6b7280;">Pisahkan setiap misi dengan enter (baris baru)</small>
                    </div>
                    
                    <hr style="margin: 2rem 0; border: none; border-top: 2px solid #e5e7eb;">
                    
                    <h3 style="margin-bottom: 1.5rem; color: #1f2937;">Statistik Sekolah</h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label>Jumlah Siswa Aktif *</label>
                            <input type="number" name="jumlah_siswa_aktif" value="<?= $profil['jumlah_siswa_aktif'] ?>" required min="0">
                            <small style="color: #6b7280;">Akan ditampilkan di beranda</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Jumlah Guru Aktif *</label>
                            <input type="number" name="jumlah_guru_aktif" value="<?= $profil['jumlah_guru_aktif'] ?>" required min="0">
                            <small style="color: #6b7280;">Akan ditampilkan di beranda</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Jumlah Ruang Kelas *</label>
                            <input type="number" name="jumlah_ruang_kelas" value="<?= $profil['jumlah_ruang_kelas'] ?>" required min="0">
                            <small style="color: #6b7280;">Akan ditampilkan di beranda</small>
                        </div>
                    </div>
                    
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #e5e7eb;">
                        <button type="submit" name="update_profil" class="btn btn-primary" style="font-size: 1.1rem;">
                            💾 Simpan Perubahan
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary" style="font-size: 1.1rem;">
                            ✕ Batal
                        </a>
                    </div>
                </form>
            </div>

            <!-- Preview Card -->
            <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h3 style="margin-bottom: 1.5rem;">Preview Statistik di Beranda</h3>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
                    <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 10px; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">👨‍🎓</div>
                        <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"><?= $profil['jumlah_siswa_aktif'] ?></div>
                        <div style="opacity: 0.9;">Siswa Aktif</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 10px; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">👨‍🏫</div>
                        <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"><?= $profil['jumlah_guru_aktif'] ?></div>
                        <div style="opacity: 0.9;">Guru Aktif</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); padding: 1.5rem; border-radius: 10px; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏫</div>
                        <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"><?= $profil['jumlah_ruang_kelas'] ?></div>
                        <div style="opacity: 0.9;">Ruang Kelas</div>
                    </div>
                </div>
            </div>

            <!-- Info Update -->
            <div class="card" style="background: #f0fdf4; border-left: 4px solid #10b981;">
                <div style="display: flex; gap: 1rem; align-items: start;">
                    <div style="font-size: 2rem;">ℹ️</div>
                    <div>
                        <h4 style="color: #065f46; margin-bottom: 0.5rem;">Tips Mengisi Profil Sekolah</h4>
                        <ul style="color: #047857; margin-left: 1.5rem; line-height: 1.8;">
                            <li>Pastikan semua informasi akurat dan up-to-date</li>
                            <li>Visi harus menggambarkan tujuan jangka panjang sekolah</li>
                            <li>Misi adalah langkah-langkah konkret untuk mencapai visi</li>
                            <li>Statistik akan ditampilkan di halaman beranda untuk publik</li>
                            <li>Perubahan akan langsung terlihat di halaman beranda</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>