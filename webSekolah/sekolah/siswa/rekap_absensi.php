<?php
require_once '../config.php';
requireRole('siswa');

// Ambil data siswa
$siswa = $conn->query("SELECT * FROM siswa WHERE user_id = " . $_SESSION['user_id'])->fetch_assoc();

// Filter
$mapel_filter = isset($_GET['mapel']) ? intval($_GET['mapel']) : 0;
$bulan_filter = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');

// Query rekap absensi
$query = "SELECT a.*, kv.tanggal, kv.topik, mp.nama_mapel, mp.kode_mapel
          FROM absensi a
          JOIN kelas_virtual kv ON a.kelas_virtual_id = kv.id
          JOIN mata_pelajaran mp ON kv.mapel_id = mp.id
          WHERE a.siswa_id = {$siswa['id']}
          AND DATE_FORMAT(kv.tanggal, '%Y-%m') = '$bulan_filter'";

if ($mapel_filter > 0) {
    $query .= " AND mp.id = $mapel_filter";
}

$query .= " ORDER BY kv.tanggal DESC, a.waktu_absen DESC";
$absensiQuery = $conn->query($query);

// Statistik
$totalHadir = $conn->query("SELECT COUNT(*) as total FROM absensi WHERE siswa_id = {$siswa['id']} AND status = 'hadir'")->fetch_assoc()['total'];
$totalIzin = $conn->query("SELECT COUNT(*) as total FROM absensi WHERE siswa_id = {$siswa['id']} AND status = 'izin'")->fetch_assoc()['total'];
$totalSakit = $conn->query("SELECT COUNT(*) as total FROM absensi WHERE siswa_id = {$siswa['id']} AND status = 'sakit'")->fetch_assoc()['total'];
$totalAlpha = $conn->query("SELECT COUNT(*) as total FROM absensi WHERE siswa_id = {$siswa['id']} AND status = 'alpha'")->fetch_assoc()['total'];
$totalPertemuan = $totalHadir + $totalIzin + $totalSakit + $totalAlpha;
$persenKehadiran = $totalPertemuan > 0 ? round(($totalHadir / $totalPertemuan) * 100, 1) : 0;

// Ambil daftar mata pelajaran untuk filter
$mapelList = $conn->query("SELECT * FROM mata_pelajaran WHERE kelas = '{$siswa['kelas']}' ORDER BY nama_mapel");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi - Siswa</title>
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
                <li><a href="kelas_virtual.php">🖥 Kelas Virtual</a></li>
                <li><a href="rekap_absensi.php" class="active">📋 Rekap Absensi</a></li>
                <li><a href="nilai.php">📝 Nilai Saya</a></li>
                <li><a href="../index.php">🏠 Ke Beranda</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Rekap Absensi Saya</h1>
            </div>

            <!-- Statistik Kehadiran -->
            <div class="stats-grid">
                <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?= $totalHadir ?></div>
                    <div class="stat-label">Hadir</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="stat-icon">📝</div>
                    <div class="stat-number"><?= $totalIzin ?></div>
                    <div class="stat-label">Izin</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                    <div class="stat-icon">🤒</div>
                    <div class="stat-number"><?= $totalSakit ?></div>
                    <div class="stat-label">Sakit</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                    <div class="stat-icon">❌</div>
                    <div class="stat-number"><?= $totalAlpha ?></div>
                    <div class="stat-label">Alpha</div>
                </div>
            </div>

            <!-- Info Persentase -->
            <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div style="text-align: center; padding: 1rem 0;">
                    <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;">
                        <?= $persenKehadiran ?>%
                    </div>
                    <div style="font-size: 1.2rem; opacity: 0.9;">
                        Persentase Kehadiran Total
                    </div>
                    <div style="font-size: 0.95rem; opacity: 0.8; margin-top: 0.5rem;">
                        Total Pertemuan: <?= $totalPertemuan ?>
                    </div>
                </div>
            </div>

            <!-- Filter -->
            <div class="card">
                <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
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
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Filter Bulan</label>
                        <input type="month" name="bulan" value="<?= $bulan_filter ?>" onchange="this.form.submit()">
                    </div>
                    
                    <?php if ($mapel_filter > 0 || $bulan_filter != date('Y-m')): ?>
                        <a href="rekap_absensi.php" class="btn btn-secondary">✕ Reset Filter</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabel Rekap Absensi -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Riwayat Absensi</h2>
                    <button onclick="window.print()" class="btn btn-secondary">🖨 Cetak</button>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Mata Pelajaran</th>
                                <th>Topik</th>
                                <th>Status</th>
                                <th>Waktu Absen</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($absensiQuery->num_rows > 0): ?>
                                <?php while ($absen = $absensiQuery->fetch_assoc()): ?>
                                <tr>
                                    <td><?= formatTanggal($absen['tanggal']) ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= $absen['kode_mapel'] ?></span>
                                        <strong><?= $absen['nama_mapel'] ?></strong>
                                    </td>
                                    <td><?= $absen['topik'] ?: '-' ?></td>
                                    <td>
                                        <?php 
                                        $badgeClass = '';
                                        switch($absen['status']) {
                                            case 'hadir': $badgeClass = 'badge-success'; break;
                                            case 'izin': $badgeClass = 'badge-warning'; break;
                                            case 'sakit': $badgeClass = 'badge-info'; break;
                                            case 'alpha': $badgeClass = 'badge-danger'; break;
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= strtoupper($absen['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($absen['waktu_absen'])) ?></td>
                                    <td><?= $absen['keterangan'] ?: '-' ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 3rem; color: #6b7280;">
                                        <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                                        Tidak ada data absensi untuk periode ini
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <style>
        @media print {
            .sidebar, .dashboard-header, .card-header button, form, .stats-grid {
                display: none !important;
            }
            .dashboard {
                display: block;
            }
            .main-content {
                padding: 0;
            }
            .card {
                box-shadow: none;
                border: 1px solid #000;
                page-break-inside: avoid;
            }
        }
    </style>
</body>
</html>