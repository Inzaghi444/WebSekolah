<?php
require_once '../config.php';
requireRole('siswa');

// Ambil data siswa
$siswa = $conn->query("SELECT * FROM siswa WHERE user_id = " . $_SESSION['user_id'])->fetch_assoc();

// Handle Absensi
if (isset($_POST['absen'])) {
    $kelas_virtual_id = intval($_POST['kelas_virtual_id']);
    $siswa_id = $siswa['id'];
    
    // Cek apakah sudah absen
    $cekAbsen = $conn->query("SELECT * FROM absensi WHERE kelas_virtual_id = $kelas_virtual_id AND siswa_id = $siswa_id");
    
    if ($cekAbsen->num_rows === 0) {
        $insert = $conn->query("INSERT INTO absensi (kelas_virtual_id, siswa_id, status) 
                                VALUES ($kelas_virtual_id, $siswa_id, 'hadir')");
        
        if ($insert) {
            echo "<script>alert('✓ Absensi berhasil! Anda tercatat hadir.');</script>";
        }
    } else {
        echo "<script>alert('⚠ Anda sudah melakukan absensi untuk kelas ini!');</script>";
    }
}

// Filter mata pelajaran
$mapel_filter = isset($_GET['mapel']) ? intval($_GET['mapel']) : 0;

// Ambil daftar kelas virtual yang aktif
$query = "SELECT kv.*, mp.nama_mapel, mp.kode_mapel, g.nama as nama_guru,
          (SELECT COUNT(*) FROM absensi a WHERE a.kelas_virtual_id = kv.id AND a.siswa_id = {$siswa['id']}) as sudah_absen
          FROM kelas_virtual kv
          JOIN mata_pelajaran mp ON kv.mapel_id = mp.id
          LEFT JOIN guru g ON mp.guru_id = g.id
          WHERE mp.kelas = '{$siswa['kelas']}' AND kv.status = 'aktif'";

if ($mapel_filter > 0) {
    $query .= " AND mp.id = $mapel_filter";
}

$query .= " ORDER BY kv.created_at DESC";
$kelasQuery = $conn->query($query);

// Ambil daftar mata pelajaran untuk filter
$mapelList = $conn->query("SELECT * FROM mata_pelajaran WHERE kelas = '{$siswa['kelas']}' ORDER BY nama_mapel");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas Virtual - Siswa</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Portal Siswa</h3>
                <p style="font-size: 0.9rem; opacity: 0.8;"><?= $_SESSION['nama_lengkap'] ?></p>
                <p style="font-size: 0.85rem; opacity: 0.7;">Kelas: <?= $siswa['kelas'] ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="kelas_virtual.php" class="active">🖥 Kelas Virtual</a></li>
                <li><a href="rekap_absensi.php">📋 Rekap Absensi</a></li>
                <li><a href="nilai.php">📝 Nilai Saya</a></li>
                <li><a href="../index.php">🏠 Ke Beranda</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Kelas Virtual</h1>
            </div>

            <!-- Filter -->
            <div class="card">
                <form method="GET" style="display: flex; gap: 1rem; align-items: end;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label>Filter Mata Pelajaran</label>
                        <select name="mapel" onchange="this.form.submit()">
                            <option value="0">Semua Mata Pelajaran</option>
                            <?php while ($m = $mapelList->fetch_assoc()): ?>
                                <option value="<?= $m['id'] ?>" <?= $mapel_filter == $m['id'] ? 'selected' : '' ?>>
                                    <?= $m['nama_mapel'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php if ($mapel_filter > 0): ?>
                        <a href="kelas_virtual.php" class="btn btn-secondary">✕ Reset Filter</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Daftar Kelas Virtual Aktif -->
            <?php if ($kelasQuery->num_rows > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                    <?php while ($kelas = $kelasQuery->fetch_assoc()): ?>
                    <div class="card" style="margin-bottom: 0;">
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 10px 10px 0 0; margin: -1.5rem -1.5rem 1.5rem -1.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <span class="badge" style="background: rgba(255,255,255,0.3); color: white; margin-bottom: 0.5rem;">
                                        <?= $kelas['kode_mapel'] ?>
                                    </span>
                                    <h3 style="font-size: 1.3rem; margin-bottom: 0.25rem;"><?= $kelas['nama_mapel'] ?></h3>
                                    <p style="opacity: 0.9; font-size: 0.95rem;">👨‍🏫 <?= $kelas['nama_guru'] ?></p>
                                </div>
                                <?php if ($kelas['sudah_absen'] > 0): ?>
                                    <span class="badge badge-success">✓ Sudah Absen</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">✕ Belum Absen</span>
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
                        </div>
                        
                        <?php if ($kelas['sudah_absen'] === '0'): ?>
                            <form method="POST">
                                <input type="hidden" name="kelas_virtual_id" value="<?= $kelas['id'] ?>">
                                <button type="submit" name="absen" class="btn btn-primary btn-block">
                                    ✅ Absen Sekarang
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-success" style="margin-bottom: 0;">
                                ✓ Anda sudah tercatat hadir di kelas ini
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <div style="text-align: center; padding: 3rem;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">🖥</div>
                        <h3 style="color: #1f2937; margin-bottom: 0.5rem;">Tidak Ada Kelas Virtual Aktif</h3>
                        <p style="color: #6b7280;">
                            Saat ini tidak ada kelas virtual yang sedang berlangsung.
                            <?php if ($mapel_filter > 0): ?>
                                <br>Coba hapus filter untuk melihat semua kelas.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>