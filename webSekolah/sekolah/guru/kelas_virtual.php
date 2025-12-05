<?php
require_once '../config.php';
requireRole('guru');

// Ambil data guru
$guru = $conn->query("SELECT * FROM guru WHERE user_id = " . $_SESSION['user_id'])->fetch_assoc();

// Handle Buat Kelas Virtual
if (isset($_POST['buat_kelas'])) {
    $mapel_id = intval($_POST['mapel_id']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
    $topik = $conn->real_escape_string($_POST['topik']);
    
    $insert = $conn->query("INSERT INTO kelas_virtual (mapel_id, tanggal, topik, status) 
                            VALUES ($mapel_id, '$tanggal', '$topik', 'aktif')");
    
    if ($insert) {
        echo "<script>alert('✓ Kelas virtual berhasil dibuat!'); window.location.href='kelas_virtual.php';</script>";
    }
}

// Handle Update Kelas Virtual
if (isset($_POST['update_kelas'])) {
    $id = intval($_POST['id']);
    $topik = $conn->real_escape_string($_POST['topik']);
    
    $update = $conn->query("UPDATE kelas_virtual SET topik = '$topik' WHERE id = $id");
    
    if ($update) {
        echo "<script>alert('✓ Kelas virtual berhasil diperbarui!'); window.location.href='kelas_virtual.php';</script>";
    }
}

// Handle Tutup Kelas Virtual
if (isset($_GET['tutup'])) {
    $id = intval($_GET['tutup']);
    $conn->query("UPDATE kelas_virtual SET status = 'selesai' WHERE id = $id");
    echo "<script>alert('✓ Kelas virtual ditutup!'); window.location.href='kelas_virtual.php';</script>";
}

// Handle Buka Kembali Kelas Virtual
if (isset($_GET['buka'])) {
    $id = intval($_GET['buka']);
    $conn->query("UPDATE kelas_virtual SET status = 'aktif' WHERE id = $id");
    echo "<script>alert('✓ Kelas virtual dibuka kembali!'); window.location.href='kelas_virtual.php';</script>";
}

// Handle Hapus Kelas Virtual
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM kelas_virtual WHERE id = $id");
    echo "<script>alert('✓ Kelas virtual dihapus!'); window.location.href='kelas_virtual.php';</script>";
}

// Filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'aktif';

// Ambil mata pelajaran guru
$mapelQuery = $conn->query("SELECT * FROM mata_pelajaran WHERE guru_id = {$guru['id']} ORDER BY kelas, nama_mapel");

// Ambil kelas virtual
$query = "SELECT kv.*, mp.nama_mapel, mp.kode_mapel, mp.kelas,
          (SELECT COUNT(*) FROM absensi a WHERE a.kelas_virtual_id = kv.id) as total_absen
          FROM kelas_virtual kv
          JOIN mata_pelajaran mp ON kv.mapel_id = mp.id
          WHERE mp.guru_id = {$guru['id']}";

if ($status_filter === 'aktif') {
    $query .= " AND kv.status = 'aktif'";
} elseif ($status_filter === 'selesai') {
    $query .= " AND kv.status = 'selesai'";
}

$query .= " ORDER BY kv.tanggal DESC, kv.created_at DESC";
$kelasVirtualQuery = $conn->query($query);

// Ambil kelas untuk edit
$editKelas = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $editKelas = $conn->query("SELECT * FROM kelas_virtual WHERE id = $id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kelas Virtual - Guru</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Portal Guru</h3>
                <p style="font-size: 0.9rem; opacity: 0.8;"><?= $_SESSION['nama_lengkap'] ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="kelas_virtual.php" class="active">🖥 Kelola Kelas Virtual</a></li>
                <li><a href="rekap_absensi.php">📋 Rekap Absensi</a></li>
                <li><a href="kelola_nilai.php">📝 Kelola Nilai</a></li>
                <li><a href="../index.php">🏠 Ke Beranda</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Kelola Kelas Virtual</h1>
            </div>

            <!-- Form Buat/Edit Kelas Virtual -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?= $editKelas ? 'Edit Kelas Virtual' : 'Buat Kelas Virtual Baru' ?></h2>
                </div>
                
                <form method="POST">
                    <?php if ($editKelas): ?>
                        <input type="hidden" name="id" value="<?= $editKelas['id'] ?>">
                    <?php endif; ?>
                    
                    <div style="display: grid; grid-template-columns: 2fr 1fr 3fr 1fr; gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Mata Pelajaran *</label>
                            <select name="mapel_id" required <?= $editKelas ? 'disabled' : '' ?>>
                                <option value="">Pilih Mata Pelajaran</option>
                                <?php 
                                $mapelQuery->data_seek(0);
                                while ($m = $mapelQuery->fetch_assoc()): 
                                ?>
                                    <option value="<?= $m['id'] ?>" <?= $editKelas && $editKelas['mapel_id'] == $m['id'] ? 'selected' : '' ?>>
                                        <?= $m['nama_mapel'] ?> (<?= $m['kelas'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Tanggal *</label>
                            <input type="date" name="tanggal" value="<?= $editKelas ? $editKelas['tanggal'] : date('Y-m-d') ?>" required <?= $editKelas ? 'disabled' : '' ?>>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Topik Pembelajaran</label>
                            <input type="text" name="topik" value="<?= $editKelas['topik'] ?? '' ?>" placeholder="Contoh: Persamaan Linear">
                        </div>
                        
                        <?php if ($editKelas): ?>
                            <button type="submit" name="update_kelas" class="btn btn-primary">
                                💾 Update
                            </button>
                        <?php else: ?>
                            <button type="submit" name="buat_kelas" class="btn btn-primary">
                                ➕ Buat Kelas
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($editKelas): ?>
                        <div style="margin-top: 1rem;">
                            <a href="kelas_virtual.php" class="btn btn-secondary">✕ Batal</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Filter Status -->
            <div class="card">
                <div style="display: flex; gap: 1rem;">
                    <a href="?status=aktif" class="btn <?= $status_filter === 'aktif' ? 'btn-primary' : 'btn-secondary' ?>">
                        🟢 Kelas Aktif
                    </a>
                    <a href="?status=selesai" class="btn <?= $status_filter === 'selesai' ? 'btn-primary' : 'btn-secondary' ?>">
                        ⚫ Kelas Selesai
                    </a>
                    <a href="?status=semua" class="btn <?= $status_filter === 'semua' ? 'btn-primary' : 'btn-secondary' ?>">
                        📋 Semua Kelas
                    </a>
                </div>
            </div>

            <!-- Daftar Kelas Virtual -->
            <?php if ($kelasVirtualQuery->num_rows > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                    <?php while ($kelas = $kelasVirtualQuery->fetch_assoc()): ?>
                    <div class="card" style="margin-bottom: 0;">
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 10px 10px 0 0; margin: -1.5rem -1.5rem 1.5rem -1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <span class="badge" style="background: rgba(255,255,255,0.3); color: white; margin-bottom: 0.5rem;">
                                        <?= $kelas['kode_mapel'] ?>
                                    </span>
                                    <h3 style="font-size: 1.2rem; margin-bottom: 0.25rem;"><?= $kelas['nama_mapel'] ?></h3>
                                    <p style="opacity: 0.9; font-size: 0.9rem;">Kelas <?= $kelas['kelas'] ?></p>
                                </div>
                                <?php if ($kelas['status'] === 'aktif'): ?>
                                    <span class="badge badge-success">🟢 Aktif</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #6b7280; color: white;">⚫ Selesai</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 1rem;">
                            <p style="color: #6b7280; margin-bottom: 0.5rem;">
                                📅 <?= formatTanggal($kelas['tanggal']) ?>
                            </p>
                            <?php if ($kelas['topik']): ?>
                                <p style="font-weight: 600; color: #1f2937;">
                                    📝 <?= $kelas['topik'] ?>
                                </p>
                            <?php endif; ?>
                            <p style="color: #10b981; font-weight: 600; margin-top: 0.5rem;">
                                ✓ <?= $kelas['total_absen'] ?> Siswa Hadir
                            </p>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <a href="rekap_absensi.php?kelas_virtual=<?= $kelas['id'] ?>" class="btn btn-secondary" style="text-align: center; padding: 0.75rem; font-size: 0.9rem;">
                                📋 Absensi
                            </a>
                            <a href="?edit=<?= $kelas['id'] ?>" class="btn btn-secondary" style="text-align: center; padding: 0.75rem; font-size: 0.9rem;">
                                ✏ Edit
                            </a>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <?php if ($kelas['status'] === 'aktif'): ?>
                                <a href="?tutup=<?= $kelas['id'] ?>" 
                                   onclick="return confirm('Tutup kelas virtual ini?')" 
                                   class="btn btn-danger" 
                                   style="text-align: center; padding: 0.75rem; font-size: 0.9rem;">
                                    🔒 Tutup
                                </a>
                            <?php else: ?>
                                <a href="?buka=<?= $kelas['id'] ?>" 
                                   onclick="return confirm('Buka kembali kelas ini?')" 
                                   class="btn btn-success" 
                                   style="text-align: center; padding: 0.75rem; font-size: 0.9rem;">
                                    🔓 Buka
                                </a>
                            <?php endif; ?>
                            <a href="?hapus=<?= $kelas['id'] ?>" 
                               onclick="return confirm('Yakin hapus kelas ini? Data absensi akan ikut terhapus!')" 
                               class="btn btn-danger" 
                               style="text-align: center; padding: 0.75rem; font-size: 0.9rem;">
                                🗑 Hapus
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <div style="text-align: center; padding: 3rem; color: #6b7280;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">🖥</div>
                        <h3 style="color: #1f2937; margin-bottom: 0.5rem;">Tidak Ada Kelas Virtual</h3>
                        <p>
                            <?php if ($status_filter === 'aktif'): ?>
                                Belum ada kelas virtual yang aktif. Buat kelas baru di atas!
                            <?php elseif ($status_filter === 'selesai'): ?>
                                Belum ada kelas virtual yang selesai.
                            <?php else: ?>
                                Belum ada kelas virtual yang dibuat.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>