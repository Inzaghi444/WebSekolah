<?php
require_once '../config.php';
requireRole('guru');

// Ambil data guru
$guru = $conn->query("SELECT * FROM guru WHERE user_id = " . $_SESSION['user_id'])->fetch_assoc();

// Filter
$kelas_virtual_filter = isset($_GET['kelas_virtual']) ? intval($_GET['kelas_virtual']) : 0;
$mapel_filter = isset($_GET['mapel']) ? intval($_GET['mapel']) : 0;

// Query rekap absensi
$query = "SELECT a.*, s.nama as nama_siswa, s.nis, s.kelas as kelas_siswa,
          kv.tanggal, kv.topik, mp.nama_mapel, mp.kode_mapel
          FROM absensi a
          JOIN siswa s ON a.siswa_id = s.id
          JOIN kelas_virtual kv ON a.kelas_virtual_id = kv.id
          JOIN mata_pelajaran mp ON kv.mapel_id = mp.id
          WHERE mp.guru_id = {$guru['id']}";

if ($kelas_virtual_filter > 0) {
    $query .= " AND kv.id = $kelas_virtual_filter";
}

if ($mapel_filter > 0) {
    $query .= " AND mp.id = $mapel_filter";
}

$query .= " ORDER BY a.waktu_absen DESC";
$absensiQuery = $conn->query($query);

// Ambil daftar kelas virtual untuk filter
$kelasVirtualList = $conn->query("SELECT kv.id, kv.tanggal, kv.topik, mp.nama_mapel 
                                  FROM kelas_virtual kv
                                  JOIN mata_pelajaran mp ON kv.mapel_id = mp.id
                                  WHERE mp.guru_id = {$guru['id']}
                                  ORDER BY kv.tanggal DESC");

// Ambil daftar mata pelajaran untuk filter
$mapelList = $conn->query("SELECT * FROM mata_pelajaran WHERE guru_id = {$guru['id']} ORDER BY nama_mapel");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi - Guru</title>
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
                <li><a href="kelas_virtual.php">🖥 Kelola Kelas Virtual</a></li>
                <li><a href="rekap_absensi.php" class="active">📋 Rekap Absensi</a></li>
                <li><a href="kelola_nilai.php">📝 Kelola Nilai</a></li>
                <li><a href="../index.php">🏠 Ke Beranda</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Rekap Absensi Siswa</h1>
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
                                    <?= $m['nama_mapel'] ?> (<?= $m['kelas'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Filter Kelas Virtual</label>
                        <select name="kelas_virtual" onchange="this.form.submit()">
                            <option value="0">Semua Pertemuan</option>
                            <?php while ($kv = $kelasVirtualList->fetch_assoc()): ?>
                                <option value="<?= $kv['id'] ?>" <?= $kelas_virtual_filter == $kv['id'] ? 'selected' : '' ?>>
                                    <?= $kv['nama_mapel'] ?> - <?= date('d/m/Y', strtotime($kv['tanggal'])) ?>
                                    <?= $kv['topik'] ? ' (' . $kv['topik'] . ')' : '' ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <?php if ($kelas_virtual_filter > 0 || $mapel_filter > 0): ?>
                        <a href="rekap_absensi.php" class="btn btn-secondary">✕ Reset Filter</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabel Rekap Absensi -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Absensi</h2>
                    <button onclick="window.print()" class="btn btn-secondary">🖨 Cetak</button>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Mata Pelajaran</th>
                                <th>Topik</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Waktu Absen</th>
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
                                    <td><?= $absen['nis'] ?></td>
                                    <td><?= $absen['nama_siswa'] ?></td>
                                    <td><span class="badge badge-success"><?= $absen['kelas_siswa'] ?></span></td>
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
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 3rem; color: #6b7280;">
                                        <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                                        Tidak ada data absensi
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Statistik Per Siswa (jika filter kelas virtual aktif) -->
            <?php if ($kelas_virtual_filter > 0): ?>
                <?php
                // Ambil semua siswa di kelas tersebut
                $kvInfo = $conn->query("SELECT mp.kelas FROM kelas_virtual kv 
                                       JOIN mata_pelajaran mp ON kv.mapel_id = mp.id 
                                       WHERE kv.id = $kelas_virtual_filter")->fetch_assoc();
                
                $siswaKelas = $conn->query("SELECT s.id, s.nis, s.nama,
                                           (SELECT status FROM absensi WHERE kelas_virtual_id = $kelas_virtual_filter AND siswa_id = s.id) as status_absen
                                           FROM siswa s 
                                           WHERE s.kelas = '{$kvInfo['kelas']}'
                                           ORDER BY s.nama");
                ?>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Daftar Kehadiran Siswa</h2>
                    </div>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($siswa = $siswaKelas->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $siswa['nis'] ?></td>
                                    <td><?= $siswa['nama'] ?></td>
                                    <td>
                                        <?php if ($siswa['status_absen']): ?>
                                            <?php 
                                            $badgeClass = '';
                                            switch($siswa['status_absen']) {
                                                case 'hadir': $badgeClass = 'badge-success'; break;
                                                case 'izin': $badgeClass = 'badge-warning'; break;
                                                case 'sakit': $badgeClass = 'badge-info'; break;
                                                case 'alpha': $badgeClass = 'badge-danger'; break;
                                            }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>">
                                                <?= strtoupper($siswa['status_absen']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">BELUM ABSEN</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <style>
        @media print {
            .sidebar, .dashboard-header, .card-header button, form {
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
            }
        }
    </style>
</body>
</html>