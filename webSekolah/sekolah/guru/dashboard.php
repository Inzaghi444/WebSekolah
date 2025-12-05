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
        echo "<script>alert('✓ Kelas virtual berhasil dibuat!');</script>";
    }
}

// Handle Tutup Kelas Virtual
if (isset($_GET['tutup'])) {
    $id = intval($_GET['tutup']);
    $conn->query("UPDATE kelas_virtual SET status = 'selesai' WHERE id = $id");
    echo "<script>alert('✓ Kelas virtual ditutup!'); window.location.href='dashboard.php';</script>";
}

// Statistik
$totalMapel = $conn->query("SELECT COUNT(*) as total FROM mata_pelajaran WHERE guru_id = {$guru['id']}")->fetch_assoc()['total'];
$kelasAktif = $conn->query("SELECT COUNT(*) as total FROM kelas_virtual kv 
                            JOIN mata_pelajaran mp ON kv.mapel_id = mp.id 
                            WHERE mp.guru_id = {$guru['id']} AND kv.status = 'aktif'")->fetch_assoc()['total'];
$totalSiswa = $conn->query("SELECT COUNT(DISTINCT s.id) as total FROM siswa s
                            JOIN mata_pelajaran mp ON s.kelas = mp.kelas
                            WHERE mp.guru_id = {$guru['id']}")->fetch_assoc()['total'];

// Ambil mata pelajaran guru
$mapelQuery = $conn->query("SELECT * FROM mata_pelajaran WHERE guru_id = {$guru['id']} ORDER BY kelas, hari");

// Ambil kelas virtual aktif
$kelasVirtualQuery = $conn->query("SELECT kv.*, mp.nama_mapel, mp.kode_mapel, mp.kelas,
                                   (SELECT COUNT(*) FROM absensi a WHERE a.kelas_virtual_id = kv.id) as total_absen
                                   FROM kelas_virtual kv
                                   JOIN mata_pelajaran mp ON kv.mapel_id = mp.id
                                   WHERE mp.guru_id = {$guru['id']} AND kv.status = 'aktif'
                                   ORDER BY kv.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Portal Guru</h3>
                <p style="font-size: 0.9rem; opacity: 0.8;"><?= $_SESSION['nama_lengkap'] ?></p>
                <p style="font-size: 0.85rem; opacity: 0.7;">NIP: <?= $guru['nip'] ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="kelas_virtual.php">🖥 Kelola Kelas Virtual</a></li>
                <li><a href="rekap_absensi.php">📋 Rekap Absensi</a></li>
                <li><a href="kelola_nilai.php">📝 Kelola Nilai</a></li>
                <li><a href="../index.php">🏠 Ke Beranda</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Dashboard Guru</h1>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-number"><?= $totalMapel ?></div>
                    <div class="stat-label">Mata Pelajaran</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🖥</div>
                    <div class="stat-number"><?= $kelasAktif ?></div>
                    <div class="stat-label">Kelas Virtual Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-number"><?= $totalSiswa ?></div>
                    <div class="stat-label">Total Siswa</div>
                </div>
            </div>

            <!-- Form Buat Kelas Virtual -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Buat Kelas Virtual Baru</h2>
                </div>
                
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 2fr 1fr 3fr 1fr; gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Mata Pelajaran *</label>
                            <select name="mapel_id" required>
                                <option value="">Pilih Mata Pelajaran</option>
                                <?php 
                                $mapelQuery->data_seek(0);
                                while ($m = $mapelQuery->fetch_assoc()): 
                                ?>
                                    <option value="<?= $m['id'] ?>">
                                        <?= $m['nama_mapel'] ?> (<?= $m['kelas'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Tanggal *</label>
                            <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Topik Pembelajaran</label>
                            <input type="text" name="topik" placeholder="Contoh: Persamaan Linear">
                        </div>
                        
                        <button type="submit" name="buat_kelas" class="btn btn-primary">
                            ➕ Buat Kelas
                        </button>
                    </div>
                </form>
            </div>

            <!-- Kelas Virtual Aktif -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Kelas Virtual Aktif</h2>
                </div>
                
                <?php if ($kelasVirtualQuery->num_rows > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                        <?php while ($kelas = $kelasVirtualQuery->fetch_assoc()): ?>
                        <div style="background: white; border: 2px solid #e5e7eb; border-radius: 10px; padding: 1.5rem;">
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div>
                                        <span class="badge" style="background: rgba(255,255,255,0.3); color: white; margin-bottom: 0.5rem;">
                                            <?= $kelas['kode_mapel'] ?>
                                        </span>
                                        <h3 style="font-size: 1.2rem; margin-bottom: 0.25rem;"><?= $kelas['nama_mapel'] ?></h3>
                                        <p style="opacity: 0.9; font-size: 0.9rem;">Kelas <?= $kelas['kelas'] ?></p>
                                    </div>
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
                            
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="rekap_absensi.php?kelas_virtual=<?= $kelas['id'] ?>" class="btn btn-secondary" style="flex: 1; text-align: center; padding: 0.75rem;">
                                    📋 Lihat Absensi
                                </a>
                                <a href="?tutup=<?= $kelas['id'] ?>" 
                                   onclick="return confirm('Tutup kelas virtual ini?')" 
                                   class="btn btn-danger" 
                                   style="flex: 1; text-align: center; padding: 0.75rem;">
                                    🔒 Tutup Kelas
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: #6b7280;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">🖥</div>
                        <p>Belum ada kelas virtual yang aktif</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mata Pelajaran Diampu -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Mata Pelajaran yang Diampu</h2>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Mata Pelajaran</th>
                                <th>Kelas</th>
                                <th>Jadwal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $mapelQuery->data_seek(0);
                            while ($mapel = $mapelQuery->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><span class="badge badge-info"><?= $mapel['kode_mapel'] ?></span></td>
                                <td><strong><?= $mapel['nama_mapel'] ?></strong></td>
                                <td><span class="badge badge-success"><?= $mapel['kelas'] ?></span></td>
                                <td>
                                    <?= $mapel['hari'] ?>, 
                                    <?= substr($mapel['jam_mulai'], 0, 5) ?> - <?= substr($mapel['jam_selesai'], 0, 5) ?>
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