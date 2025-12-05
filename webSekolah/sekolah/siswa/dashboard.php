<?php
require_once '../config.php';
requireRole('siswa');

// Ambil data siswa
$siswaQuery = $conn->query("SELECT * FROM siswa WHERE user_id = " . $_SESSION['user_id']);
$siswa = $siswaQuery->fetch_assoc();

// Ambil mata pelajaran sesuai kelas siswa
$mapelQuery = $conn->query("SELECT mp.*, g.nama as nama_guru, 
                            (SELECT COUNT(*) FROM kelas_virtual kv WHERE kv.mapel_id = mp.id AND kv.status = 'aktif') as kelas_aktif
                            FROM mata_pelajaran mp 
                            LEFT JOIN guru g ON mp.guru_id = g.id 
                            WHERE mp.kelas = '{$siswa['kelas']}'
                            ORDER BY mp.hari, mp.jam_mulai");

// Statistik kehadiran siswa
$totalAbsen = $conn->query("SELECT COUNT(*) as total FROM absensi WHERE siswa_id = {$siswa['id']}")->fetch_assoc()['total'];
$totalHadir = $conn->query("SELECT COUNT(*) as total FROM absensi WHERE siswa_id = {$siswa['id']} AND status = 'hadir'")->fetch_assoc()['total'];
$persenKehadiran = $totalAbsen > 0 ? round(($totalHadir / $totalAbsen) * 100, 1) : 0;

// Riwayat absensi terbaru
$riwayatQuery = $conn->query("SELECT a.*, kv.tanggal, kv.topik, mp.nama_mapel, mp.kode_mapel
                              FROM absensi a
                              JOIN kelas_virtual kv ON a.kelas_virtual_id = kv.id
                              JOIN mata_pelajaran mp ON kv.mapel_id = mp.id
                              WHERE a.siswa_id = {$siswa['id']}
                              ORDER BY a.waktu_absen DESC
                              LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Portal Siswa</h3>
                <p style="font-size: 0.9rem; opacity: 0.8;"><?= $_SESSION['nama_lengkap'] ?></p>
                <p style="font-size: 0.85rem; opacity: 0.7;">NIS: <?= $siswa['nis'] ?></p>
                <p style="font-size: 0.85rem; opacity: 0.7;">Kelas: <?= $siswa['kelas'] ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="kelas_virtual.php">🖥 Kelas Virtual</a></li>
                <li><a href="rekap_absensi.php">📋 Rekap Absensi</a></li>
                <li><a href="nilai.php">📝 Nilai Saya</a></li>
                <li><a href="../index.php">🏠 Ke Beranda</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Dashboard Siswa</h1>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?= $totalHadir ?></div>
                    <div class="stat-label">Total Hadir</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-number"><?= $totalAbsen ?></div>
                    <div class="stat-label">Total Pertemuan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-number"><?= $persenKehadiran ?>%</div>
                    <div class="stat-label">Persentase Kehadiran</div>
                </div>
            </div>

            <!-- Mata Pelajaran -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Mata Pelajaran Kelas <?= $siswa['kelas'] ?></h2>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru</th>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Kelas Aktif</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($mapel = $mapelQuery->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge badge-info"><?= $mapel['kode_mapel'] ?></span></td>
                                <td><strong><?= $mapel['nama_mapel'] ?></strong></td>
                                <td><?= $mapel['nama_guru'] ?></td>
                                <td><?= $mapel['hari'] ?></td>
                                <td><?= substr($mapel['jam_mulai'], 0, 5) ?> - <?= substr($mapel['jam_selesai'], 0, 5) ?></td>
                                <td>
                                    <?php if ($mapel['kelas_aktif'] > 0): ?>
                                        <span class="badge badge-success"><?= $mapel['kelas_aktif'] ?> Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Tidak Ada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="kelas_virtual.php?mapel=<?= $mapel['id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                        🖥 Masuk Kelas
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Riwayat Absensi -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Riwayat Absensi Terbaru</h2>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($riwayatQuery->num_rows > 0): ?>
                                <?php while ($riwayat = $riwayatQuery->fetch_assoc()): ?>
                                <tr>
                                    <td><?= formatTanggal($riwayat['tanggal']) ?></td>
                                    <td><strong><?= $riwayat['nama_mapel'] ?></strong></td>
                                    <td><?= $riwayat['topik'] ?: '-' ?></td>
                                    <td>
                                        <?php 
                                        $badgeClass = '';
                                        switch($riwayat['status']) {
                                            case 'hadir': $badgeClass = 'badge-success'; break;
                                            case 'izin': $badgeClass = 'badge-warning'; break;
                                            case 'sakit': $badgeClass = 'badge-info'; break;
                                            case 'alpha': $badgeClass = 'badge-danger'; break;
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= strtoupper($riwayat['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($riwayat['waktu_absen'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2rem;">
                                        Belum ada riwayat absensi
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