<?php
require_once '../config.php';
requireRole('guru');

// Ambil data guru
$guru = $conn->query("SELECT * FROM guru WHERE user_id = " . $_SESSION['user_id'])->fetch_assoc();

// Handle Input Nilai
if (isset($_POST['input_nilai'])) {
    $siswa_id = intval($_POST['siswa_id']);
    $mapel_id = intval($_POST['mapel_id']);
    $jenis_nilai = $conn->real_escape_string($_POST['jenis_nilai']);
    $nilai = floatval($_POST['nilai']);
    $keterangan = $conn->real_escape_string($_POST['keterangan']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
    
    $insert = $conn->query("INSERT INTO nilai (siswa_id, mapel_id, jenis_nilai, nilai, keterangan, tanggal) 
                            VALUES ($siswa_id, $mapel_id, '$jenis_nilai', $nilai, '$keterangan', '$tanggal')");
    
    if ($insert) {
        echo "<script>alert('✓ Nilai berhasil disimpan!');</script>";
    }
}

// Handle Hapus Nilai
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM nilai WHERE id = $id");
    echo "<script>alert('✓ Nilai berhasil dihapus!'); window.location.href='kelola_nilai.php';</script>";
}

// Filter
$mapel_filter = isset($_GET['mapel']) ? intval($_GET['mapel']) : 0;

// Ambil daftar mata pelajaran guru
$mapelList = $conn->query("SELECT * FROM mata_pelajaran WHERE guru_id = {$guru['id']} ORDER BY kelas, nama_mapel");

// Ambil daftar siswa berdasarkan mata pelajaran
$siswaList = [];
if ($mapel_filter > 0) {
    $mapelInfo = $conn->query("SELECT kelas FROM mata_pelajaran WHERE id = $mapel_filter")->fetch_assoc();
    $siswaList = $conn->query("SELECT * FROM siswa WHERE kelas = '{$mapelInfo['kelas']}' ORDER BY nama");
}

// Ambil rekap nilai
$nilaiQuery = null;
if ($mapel_filter > 0) {
    $nilaiQuery = $conn->query("SELECT n.*, s.nama as nama_siswa, s.nis, s.kelas as kelas_siswa, mp.nama_mapel
                                FROM nilai n
                                JOIN siswa s ON n.siswa_id = s.id
                                JOIN mata_pelajaran mp ON n.mapel_id = mp.id
                                WHERE n.mapel_id = $mapel_filter
                                ORDER BY s.nama, n.tanggal DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Nilai - Guru</title>
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
                <li><a href="rekap_absensi.php">📋 Rekap Absensi</a></li>
                <li><a href="kelola_nilai.php" class="active">📝 Kelola Nilai</a></li>
                <li><a href="../index.php">🏠 Ke Beranda</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Kelola Nilai Siswa</h1>
            </div>

            <!-- Filter Mata Pelajaran -->
            <div class="card">
                <form method="GET">
                    <div class="form-group">
                        <label>Pilih Mata Pelajaran *</label>
                        <select name="mapel" onchange="this.form.submit()" required>
                            <option value="0">-- Pilih Mata Pelajaran --</option>
                            <?php 
                            $mapelList->data_seek(0);
                            while ($m = $mapelList->fetch_assoc()): 
                            ?>
                                <option value="<?= $m['id'] ?>" <?= $mapel_filter == $m['id'] ? 'selected' : '' ?>>
                                    <?= $m['nama_mapel'] ?> (Kelas <?= $m['kelas'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </form>
            </div>

            <?php if ($mapel_filter > 0): ?>
                <!-- Form Input Nilai -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Input Nilai Baru</h2>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="mapel_id" value="<?= $mapel_filter ?>">
                        
                        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 2fr 1fr; gap: 1rem; align-items: end;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Siswa *</label>
                                <select name="siswa_id" required>
                                    <option value="">Pilih Siswa</option>
                                    <?php while ($siswa = $siswaList->fetch_assoc()): ?>
                                        <option value="<?= $siswa['id'] ?>">
                                            <?= $siswa['nama'] ?> (<?= $siswa['nis'] ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Jenis Nilai *</label>
                                <select name="jenis_nilai" required>
                                    <option value="tugas">Tugas</option>
                                    <option value="uts">UTS</option>
                                    <option value="uas">UAS</option>
                                    <option value="praktek">Praktek</option>
                                </select>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Nilai *</label>
                                <input type="number" name="nilai" min="0" max="100" step="0.01" required>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Tanggal *</label>
                                <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Keterangan</label>
                                <input type="text" name="keterangan" placeholder="Contoh: Bab 1">
                            </div>
                            
                            <button type="submit" name="input_nilai" class="btn btn-primary">
                                ➕ Simpan Nilai
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabel Rekap Nilai -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Rekap Nilai</h2>
                        <button onclick="window.print()" class="btn btn-secondary">🖨 Cetak</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Jenis Nilai</th>
                                    <th>Nilai</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($nilaiQuery && $nilaiQuery->num_rows > 0): ?>
                                    <?php while ($nilai = $nilaiQuery->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($nilai['tanggal'])) ?></td>
                                        <td><?= $nilai['nis'] ?></td>
                                        <td><?= $nilai['nama_siswa'] ?></td>
                                        <td><span class="badge badge-info"><?= $nilai['kelas_siswa'] ?></span></td>
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
                                            <strong style="font-size: 1.1rem; <?= $nilai['nilai'] < 75 ? 'color: #ef4444;' : 'color: #10b981;' ?>">
                                                <?= $nilai['nilai'] ?>
                                            </strong>
                                        </td>
                                        <td><?= $nilai['keterangan'] ?: '-' ?></td>
                                        <td>
                                            <a href="?mapel=<?= $mapel_filter ?>&hapus=<?= $nilai['id'] ?>" 
                                               onclick="return confirm('Yakin hapus nilai ini?')" 
                                               class="btn btn-danger" 
                                               style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                                🗑 Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 3rem; color: #6b7280;">
                                            <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
                                            Belum ada data nilai
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div style="text-align: center; padding: 3rem; color: #6b7280;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">📝</div>
                        <h3 style="color: #1f2937; margin-bottom: 0.5rem;">Pilih Mata Pelajaran</h3>
                        <p>Silakan pilih mata pelajaran terlebih dahulu untuk mengelola nilai</p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <style>
        @media print {
            .sidebar, .dashboard-header, .card-header button, form, td:last-child, th:last-child {
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