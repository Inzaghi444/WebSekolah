<?php
require_once '../config.php';
requireRole('admin');

// Handle Tambah Mata Pelajaran
if (isset($_POST['tambah_mapel'])) {
    $kode_mapel = $conn->real_escape_string($_POST['kode_mapel']);
    $nama_mapel = $conn->real_escape_string($_POST['nama_mapel']);
    $guru_id = intval($_POST['guru_id']);
    $kelas = $conn->real_escape_string($_POST['kelas']);
    $hari = $conn->real_escape_string($_POST['hari']);
    $jam_mulai = $conn->real_escape_string($_POST['jam_mulai']);
    $jam_selesai = $conn->real_escape_string($_POST['jam_selesai']);
    
    $insert = $conn->query("INSERT INTO mata_pelajaran (kode_mapel, nama_mapel, guru_id, kelas, hari, jam_mulai, jam_selesai) 
                            VALUES ('$kode_mapel', '$nama_mapel', $guru_id, '$kelas', '$hari', '$jam_mulai', '$jam_selesai')");
    
    if ($insert) {
        echo "<script>alert('✓ Mata pelajaran berhasil ditambahkan!'); window.location.href='kelola_mapel.php';</script>";
    } else {
        echo "<script>alert('✕ Kode mata pelajaran sudah digunakan!');</script>";
    }
}

// Handle Hapus Mata Pelajaran
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $conn->query("DELETE FROM mata_pelajaran WHERE id = $id");
    echo "<script>alert('✓ Mata pelajaran berhasil dihapus!'); window.location.href='kelola_mapel.php';</script>";
}

// Ambil semua mata pelajaran
$mapelQuery = $conn->query("SELECT mp.*, g.nama as nama_guru 
                            FROM mata_pelajaran mp 
                            LEFT JOIN guru g ON mp.guru_id = g.id 
                            ORDER BY mp.kelas, mp.hari, mp.jam_mulai");

// Ambil daftar guru untuk dropdown
$guruList = $conn->query("SELECT id, nama FROM guru ORDER BY nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mata Pelajaran - Admin</title>
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
                <li><a href="kelola_prestasi.php">🏆 Kelola Prestasi</a></li>
                <li><a href="kelola_mapel.php" class="active">📚 Mata Pelajaran</a></li>
                <li><a href="../index.php">🏠 Ke Beranda</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Kelola Mata Pelajaran</h1>
            </div>

            <!-- Form Tambah Mata Pelajaran -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Tambah Mata Pelajaran Baru</h2>
                </div>
                
                <form method="POST">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label>Kode Mata Pelajaran *</label>
                            <input type="text" name="kode_mapel" placeholder="Contoh: MAT-X1" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Mata Pelajaran *</label>
                            <input type="text" name="nama_mapel" placeholder="Contoh: Matematika" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Guru Pengampu *</label>
                            <select name="guru_id" required>
                                <option value="">Pilih Guru</option>
                                <?php while ($guru = $guruList->fetch_assoc()): ?>
                                    <option value="<?= $guru['id'] ?>"><?= $guru['nama'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Kelas *</label>
                            <select name="kelas" required>
                                <option value="">Pilih Kelas</option>
                                <option value="X-1">X-1</option>
                                <option value="X-2">X-2</option>
                                <option value="XI-1">XI-1</option>
                                <option value="XI-2">XI-2</option>
                                <option value="XII-1">XII-1</option>
                                <option value="XII-2">XII-2</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Hari *</label>
                            <select name="hari" required>
                                <option value="">Pilih Hari</option>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Jam Mulai *</label>
                            <input type="time" name="jam_mulai" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Jam Selesai *</label>
                            <input type="time" name="jam_selesai" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="tambah_mapel" class="btn btn-primary">➕ Tambah Mata Pelajaran</button>
                </form>
            </div>

            <!-- Daftar Mata Pelajaran -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Mata Pelajaran</h2>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru</th>
                                <th>Kelas</th>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($mapel = $mapelQuery->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge badge-info"><?= $mapel['kode_mapel'] ?></span></td>
                                <td><strong><?= $mapel['nama_mapel'] ?></strong></td>
                                <td><?= $mapel['nama_guru'] ?></td>
                                <td><span class="badge badge-success"><?= $mapel['kelas'] ?></span></td>
                                <td><?= $mapel['hari'] ?></td>
                                <td><?= substr($mapel['jam_mulai'], 0, 5) ?> - <?= substr($mapel['jam_selesai'], 0, 5) ?></td>
                                <td>
                                    <a href="?hapus=<?= $mapel['id'] ?>" 
                                       onclick="return confirm('Yakin hapus mata pelajaran ini?')" 
                                       class="btn btn-danger" 
                                       style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                        🗑 Hapus
                                    </a>
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