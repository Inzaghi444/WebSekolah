<?php
require_once '../config.php';
requireRole('admin');

// Handle Tambah Prestasi
if (isset($_POST['tambah_prestasi'])) {
    $nama_prestasi = $conn->real_escape_string($_POST['nama_prestasi']);
    $peringkat = $conn->real_escape_string($_POST['peringkat']);
    $tingkat = $conn->real_escape_string($_POST['tingkat']);
    $tahun = intval($_POST['tahun']);
    $keterangan = $conn->real_escape_string($_POST['keterangan']);
    
    // Upload foto
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto = uploadFile($_FILES['foto'], 'prestasi');
    }
    
    $insert = $conn->query("INSERT INTO prestasi_sekolah (nama_prestasi, peringkat, tingkat, tahun, keterangan, foto) 
                            VALUES ('$nama_prestasi', '$peringkat', '$tingkat', $tahun, '$keterangan', '$foto')");
    
    if ($insert) {
        echo "<script>alert('✓ Prestasi berhasil ditambahkan!'); window.location.href='kelola_prestasi.php';</script>";
    }
}

// Handle Update Prestasi
if (isset($_POST['update_prestasi'])) {
    $id = intval($_POST['id']);
    $nama_prestasi = $conn->real_escape_string($_POST['nama_prestasi']);
    $peringkat = $conn->real_escape_string($_POST['peringkat']);
    $tingkat = $conn->real_escape_string($_POST['tingkat']);
    $tahun = intval($_POST['tahun']);
    $keterangan = $conn->real_escape_string($_POST['keterangan']);
    
    // Upload foto baru jika ada
    $fotoQuery = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto = uploadFile($_FILES['foto'], 'prestasi');
        $fotoQuery = ", foto = '$foto'";
    }
    
    $update = $conn->query("UPDATE prestasi_sekolah SET 
                            nama_prestasi = '$nama_prestasi', 
                            peringkat = '$peringkat',
                            tingkat = '$tingkat',
                            tahun = $tahun,
                            keterangan = '$keterangan'
                            $fotoQuery 
                            WHERE id = $id");
    
    if ($update) {
        echo "<script>alert('✓ Prestasi berhasil diperbarui!'); window.location.href='kelola_prestasi.php';</script>";
    }
}

// Handle Hapus Prestasi
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Ambil nama foto sebelum dihapus
    $prestasiData = $conn->query("SELECT foto FROM prestasi_sekolah WHERE id = $id")->fetch_assoc();
    
    // Hapus file foto jika ada
    if ($prestasiData && $prestasiData['foto']) {
        $fotoPath = '../' . UPLOAD_DIR . $prestasiData['foto'];
        if (file_exists($fotoPath)) {
            unlink($fotoPath);
        }
    }
    
    // Hapus data dari database
    $conn->query("DELETE FROM prestasi_sekolah WHERE id = $id");
    echo "<script>alert('✓ Prestasi berhasil dihapus!'); window.location.href='kelola_prestasi.php';</script>";
}

// Ambil prestasi untuk edit
$editPrestasi = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $editPrestasi = $conn->query("SELECT * FROM prestasi_sekolah WHERE id = $id")->fetch_assoc();
}

// Ambil semua prestasi
$prestasiQuery = $conn->query("SELECT * FROM prestasi_sekolah ORDER BY tahun DESC, tingkat DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Prestasi - Admin</title>
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
                <li><a href="kelola_siswa.php">👨‍🎓 Kelola Siswa</a></li>
                <li><a href="kelola_kegiatan.php">📸 Kelola Kegiatan</a></li>
                <li><a href="kelola_prestasi.php"  class="active">🏆 Kelola Prestasi</a></li>
                <li><a href="kelola_mapel.php">📚 Mata Pelajaran</a></li>
                <li><a href="../index.php">🏠 Ke Beranda</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Kelola Prestasi Sekolah</h1>
            </div>

            <!-- Form Tambah/Edit Prestasi -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?= $editPrestasi ? 'Edit Prestasi' : 'Tambah Prestasi Baru' ?></h2>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($editPrestasi): ?>
                        <input type="hidden" name="id" value="<?= $editPrestasi['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Nama Prestasi *</label>
                        <input type="text" name="nama_prestasi" value="<?= $editPrestasi['nama_prestasi'] ?? '' ?>" required placeholder="Contoh: Juara 1 Olimpiade Matematika">
                    </div>
                    
                    <div class="form-group">
                        <label>Peringkat *</label>
                        <input type="text" name="peringkat" value="<?= $editPrestasi['peringkat'] ?? '' ?>" required placeholder="Contoh: Juara 1, Juara 2, Juara Umum">
                    </div>
                    
                    <div class="form-group">
                        <label>Tingkat *</label>
                        <select name="tingkat" required>
                            <option value="">-- Pilih Tingkat --</option>
                            <option value="Kecamatan" <?= ($editPrestasi['tingkat'] ?? '') == 'Kecamatan' ? 'selected' : '' ?>>Kecamatan</option>
                            <option value="Kota" <?= ($editPrestasi['tingkat'] ?? '') == 'Kota' ? 'selected' : '' ?>>Kota</option>
                            <option value="Provinsi" <?= ($editPrestasi['tingkat'] ?? '') == 'Provinsi' ? 'selected' : '' ?>>Provinsi</option>
                            <option value="Nasional" <?= ($editPrestasi['tingkat'] ?? '') == 'Nasional' ? 'selected' : '' ?>>Nasional</option>
                            <option value="Internasional" <?= ($editPrestasi['tingkat'] ?? '') == 'Internasional' ? 'selected' : '' ?>>Internasional</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Tahun *</label>
                        <input type="number" name="tahun" value="<?= $editPrestasi['tahun'] ?? date('Y') ?>" required min="2000" max="<?= date('Y') + 1 ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="4" placeholder="Deskripsi tambahan tentang prestasi ini..."><?= $editPrestasi['keterangan'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Foto Prestasi</label>
                        <?php if ($editPrestasi && $editPrestasi['foto']): ?>
                            <div style="margin-bottom: 1rem;">
                                <img src="../<?= UPLOAD_DIR . $editPrestasi['foto'] ?>" 
                                     style="max-width: 300px; border-radius: 8px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="foto" accept="image/*">
                        <small style="color: #6b7280;">Format: JPG, PNG, GIF. Maksimal 5MB</small>
                    </div>
                    
                    <?php if ($editPrestasi): ?>
                        <button type="submit" name="update_prestasi" class="btn btn-primary">💾 Update Prestasi</button>
                        <a href="kelola_prestasi.php" class="btn btn-secondary">✕ Batal</a>
                    <?php else: ?>
                        <button type="submit" name="tambah_prestasi" class="btn btn-primary">➕ Tambah Prestasi</button>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Daftar Prestasi -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Prestasi</h2>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama Prestasi</th>
                                <th>Peringkat</th>
                                <th>Tingkat</th>
                                <th>Tahun</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($prestasiQuery->num_rows > 0): ?>
                                <?php while ($prestasi = $prestasiQuery->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if ($prestasi['foto']): ?>
                                            <img src="../<?= UPLOAD_DIR . $prestasi['foto'] ?>" 
                                                 style="width: 80px; height: 60px; border-radius: 5px; object-fit: cover;">
                                        <?php else: ?>
                                            <div style="width: 80px; height: 60px; border-radius: 5px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                                🏆
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($prestasi['nama_prestasi']) ?></strong></td>
                                    <td>
                                        <span style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); color: #333; padding: 4px 12px; border-radius: 15px; font-size: 0.85rem; font-weight: bold;">
                                            <?= htmlspecialchars($prestasi['peringkat']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="color: #667eea; font-weight: 600;">
                                            <?= htmlspecialchars($prestasi['tingkat']) ?>
                                        </span>
                                    </td>
                                    <td><?= $prestasi['tahun'] ?></td>
                                    <td>
                                        <a href="?edit=<?= $prestasi['id'] ?>" 
                                           class="btn btn-secondary" 
                                           style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                            ✏ Edit
                                        </a>
                                        <a href="?hapus=<?= $prestasi['id'] ?>" 
                                           onclick="return confirm('Yakin hapus prestasi ini?')" 
                                           class="btn btn-danger" 
                                           style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                            🗑 Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem; color: #999;">
                                        Belum ada data prestasi. Silakan tambah prestasi baru.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>