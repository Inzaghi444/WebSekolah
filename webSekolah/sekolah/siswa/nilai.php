<?php
require_once '../config.php';
requireRole('siswa');

// Ambil data siswa
$siswa = $conn->query("SELECT * FROM siswa WHERE user_id = " . $_SESSION['user_id'])->fetch_assoc();

// Filter
$mapel_filter = isset($_GET['mapel']) ? intval($_GET['mapel']) : 0;

// Query nilai
$query = "SELECT n.*, mp.nama_mapel, mp.kode_mapel, g.nama as nama_guru
          FROM nilai n
          JOIN mata_pelajaran mp ON n.mapel_id = mp.id
          LEFT JOIN guru g ON mp.guru_id = g.id
          WHERE n.siswa_id = {$siswa['id']}";

if ($mapel_filter > 0) {
    $query .= " AND mp.id = $mapel_filter";
}

$query .= " ORDER BY mp.nama_mapel, n.tanggal DESC";
$nilaiQuery = $conn->query($query);

// Ambil daftar mata pelajaran untuk filter
$mapelList = $conn->query("SELECT * FROM mata_pelajaran WHERE kelas = '{$siswa['kelas']}' ORDER BY nama_mapel");

// Hitung rata-rata per mata pelajaran
$rataMapelQuery = $conn->query("SELECT mp.id, mp.nama_mapel, mp.kode_mapel,
                                AVG(n.nilai) as rata_rata,
                                COUNT(n.id) as jumlah_nilai
                                FROM mata_pelajaran mp
                                LEFT JOIN nilai n ON mp.id = n.mapel_id AND n.siswa_id = {$siswa['id']}
                                WHERE mp.kelas = '{$siswa['kelas']}'
                                GROUP BY mp.id, mp.nama_mapel, mp.kode_mapel
                                ORDER BY mp.nama_mapel");

// Hitung rata-rata keseluruhan
$rataKeseluruhan = $conn->query("SELECT AVG(nilai) as rata FROM nilai WHERE siswa_id = {$siswa['id']}")->fetch_assoc()['rata'];
$rataKeseluruhan = $rataKeseluruhan ? round($rataKeseluruhan, 2) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nilai Saya - Siswa</title>
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
                <li><a href="rekap_absensi.php">📋 Rekap Absensi</a></li>
                <li><a href="nilai.php" class="active">📝 Nilai Saya</a></li>
                <li><a href="../index.php">🏠 Ke Beranda</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Nilai Saya</h1>
            </div>

            <!-- Rata-rata Keseluruhan -->
            <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div style="text-align: center; padding: 1rem 0;">
                    <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;">
                        <?= $rataKeseluruhan ?>
                    </div>
                    <div style="font-size: 1.2rem; opacity: 0.9;">
                        Rata-rata Nilai Keseluruhan
                    </div>
                    <div style="font-size: 0.95rem; opacity: 0.8; margin-top: 0.5rem;">
                        <?php if ($rataKeseluruhan >= 85): ?>
                            ⭐ Prestasi Sangat Baik!
                        <?php elseif ($rataKeseluruhan >= 75): ?>
                            👍 Prestasi Baik!
                        <?php elseif ($rataKeseluruhan >= 65): ?>
                            📈 Terus Tingkatkan!
                        <?php else: ?>
                            💪 Semangat Belajar!
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Rata-rata Per Mata Pelajaran -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Rata-rata Per Mata Pelajaran</h2>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                    <?php while ($rataMapel = $rataMapelQuery->fetch_assoc()): ?>
                    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #667eea;">
                        <div style="margin-bottom: 0.5rem;">
                            <span class="badge badge-info"><?= $rataMapel['kode_mapel'] ?></span>
                        </div>
                        <h4 style="color: #1f2937; margin-bottom: 1rem;"><?= $rataMapel['nama_mapel'] ?></h4>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-size: 2rem; font-weight: 700; color: <?= $rataMapel['rata_rata'] >= 75 ? '#10b981' : '#ef4444' ?>;">
                                    <?= $rataMapel['rata_rata'] ? round($rataMapel['rata_rata'], 1) : '-' ?>
                                </div>
                                <div style="font-size: 0.85rem; color: #6b7280;">
                                    <?= $rataMapel['jumlah_nilai'] ?> nilai
                                </div>
                            </div>
                            <a href="?mapel=<?= $rataMapel['id'] ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                Detail
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Filter -->
            <?php if ($mapel_filter > 0): ?>
            <div class="card">
                <form method="GET" style="display: flex; gap: 1rem; align-items: end;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label>Filter Mata Pelajaran</label>
                        <select name="mapel" onchange="this.form.submit()">
                            <option value="0">Semua Mata Pelajaran</option>
                            <?php 
                            $mapelList->data_seek(0);
                            while ($m = $mapelList->fetch_assoc()): 
                            ?>
                                <option value="<?= $m['id'] ?>" <?= $mapel_filter == $m['id'] ? 'selected' : '' ?>>
                                    <?= $m['nama_mapel'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <a href="nilai.php" class="btn btn-secondary">✕ Reset Filter</a>
                </form>
            </div>
            <?php endif; ?>

            <!-- Tabel Detail Nilai -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <?= $mapel_filter > 0 ? 'Detail Nilai' : 'Semua Nilai' ?>
                    </h2>
                    <button onclick="window.print()" class="btn btn-secondary">🖨 Cetak</button>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru</th>
                                <th>Jenis Nilai</th>
                                <th>Nilai</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($nilaiQuery->num_rows > 0): ?>
                                <?php while ($nilai = $nilaiQuery->fetch_assoc()): ?>
                                <tr>
                                    <td><?= formatTanggal($nilai['tanggal']) ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= $nilai['kode_mapel'] ?></span>
                                        <strong><?= $nilai['nama_mapel'] ?></strong>
                                    </td>
                                    <td><?= $nilai['nama_guru'] ?></td>
                                    <td>
                                        <?php 
                                        $badgeClass = '';
                                        switch($nilai['jenis_nilai']) {
                                            case 'tugas': $badgeClass = 'badge-info'; break;
                                            case 'uts': $badgeClass = 'badge-warning'; break;
                                            case 'uas': $badgeClass = 'badge-danger'; break;
                                            case 'praktek': $badgeClass = 'badge-success'; break;
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= strtoupper($nilai['jenis_nilai']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <strong style="font-size: 1.3rem; <?= $nilai['nilai'] >= 75 ? 'color: #10b981;' : 'color: #ef4444;' ?>">
                                                <?= $nilai['nilai'] ?>
                                            </strong>
                                            <?php if ($nilai['nilai'] >= 85): ?>
                                                <span style="font-size: 1.2rem;">⭐</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= $nilai['keterangan'] ?: '-' ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 3rem; color: #6b7280;">
                                        <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
                                        <?php if ($mapel_filter > 0): ?>
                                            Belum ada nilai untuk mata pelajaran ini
                                        <?php else: ?>
                                            Belum ada nilai yang diinput
                                        <?php endif; ?>
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
            .sidebar, .dashboard-header, .card-header button, form, .btn {
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
                margin-bottom: 1rem;
            }
        }
    </style>
</body>
</html>