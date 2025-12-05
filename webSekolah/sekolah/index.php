<?php
require_once 'config.php';

// Ambil data profil sekolah
$profilQuery = $conn->query("SELECT * FROM profil_sekolah LIMIT 1");
$profil = $profilQuery->fetch_assoc();

// Ambil data guru untuk ditampilkan
$guruQuery = $conn->query("SELECT * FROM guru WHERE tampil_di_beranda = TRUE ORDER BY created_at DESC");

// Ambil kegiatan sekolah terbaru
$kegiatanQuery = $conn->query("SELECT * FROM kegiatan_sekolah ORDER BY tanggal_kegiatan DESC LIMIT 6");

// Ambil prestasi sekolah terbaru
$prestasiQuery = $conn->query("SELECT * FROM prestasi_sekolah ORDER BY tahun DESC, tingkat DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $profil['nama_sekolah'] ?> - Beranda</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1><?= htmlspecialchars($profil['nama_sekolah']) ?></h1>
                </div>
                <nav class="nav">
                    <a href="index.php">Beranda</a>
                    <a href="#profil">Profil</a>
                    <a href="#guru">Guru</a>
                    <a href="#kegiatan">Kegiatan</a>
                    <a href="#achievement">Prestasi</a>
                    <a href="#kontak">Kontak</a>
                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <?php if (hasRole('admin')): ?>
                            <a href="admin/dashboard.php">Dashboard Admin</a>
                        <?php elseif (hasRole('guru')): ?>
                            <a href="guru/dashboard.php">Dashboard Guru</a>
                        <?php elseif (hasRole('siswa')): ?>
                            <a href="siswa/dashboard.php">Dashboard Siswa</a>
                        <?php endif; ?>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">Login</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Selamat Datang di <?= htmlspecialchars($profil['nama_sekolah']) ?></h2>
                <p class="hero-subtitle">Membentuk Generasi Cerdas, Berkarakter, dan Berprestasi</p>
            </div>
        </div>
    </section>

    <!-- Statistik -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-number"><?= $profil['jumlah_siswa_aktif'] ?></div>
                    <div class="stat-label">Siswa Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="stat-number"><?= $profil['jumlah_guru_aktif'] ?></div>
                    <div class="stat-label">Guru Aktif</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏫</div>
                    <div class="stat-number"><?= $profil['jumlah_ruang_kelas'] ?></div>
                    <div class="stat-label">Ruang Kelas</div>
                </div>
            </div>
        </div>

    <!-- Statistik Sekolah (Chart) -->
        <div class="container">
            <div style="width:100%; max-width:600px; margin:0 auto;">
                <canvas id="chartSekolah"></canvas>
            </div>
        </div>
    </section>

    <!-- Profil -->
    <section id="profil" class="section">
        <div class="container">
            <h2 class="section-title">Profil Sekolah</h2>
            <div class="profil-grid">
                <div class="profil-card">
                    <h3>Visi</h3>
                    <p><?= nl2br(htmlspecialchars($profil['visi'])) ?></p>
                </div>
                <div class="profil-card">
                    <h3>Misi</h3>
                    <p><?= nl2br(htmlspecialchars($profil['misi'])) ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Guru -->
    <section id="guru" class="section section-gray">
        <div class="container">
            <h2 class="section-title">Profil Guru</h2>
            <div class="guru-grid">
                <?php while ($guru = $guruQuery->fetch_assoc()): ?>
                <div class="guru-card">
                    <div class="guru-photo">
                        <?php if ($guru['foto']): ?>
                            <img src="<?= UPLOAD_DIR . $guru['foto'] ?>" alt="<?= htmlspecialchars($guru['nama']) ?>">
                        <?php else: ?>
                            <div class="guru-placeholder">👨‍🏫</div>
                        <?php endif; ?>
                    </div>
                    <div class="guru-info">
                        <h3><?= htmlspecialchars($guru['nama']) ?></h3>
                        <p class="guru-mapel"><?= htmlspecialchars($guru['mata_pelajaran']) ?></p>
                        <p class="guru-pendidikan"><?= htmlspecialchars($guru['pendidikan']) ?></p>
                        <?php if ($guru['email']): ?>
                            <p class="guru-contact">✉ <?= htmlspecialchars($guru['email']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Kegiatan -->
    <section id="kegiatan" class="section">
        <div class="container">
            <h2 class="section-title">Kegiatan Sekolah</h2>
            <div class="kegiatan-grid">
                <?php while ($kegiatan = $kegiatanQuery->fetch_assoc()): ?>
                <div class="kegiatan-card">
                    <div class="kegiatan-image">
                        <?php if ($kegiatan['foto']): ?>
                            <img src="<?= UPLOAD_DIR . $kegiatan['foto'] ?>" alt="<?= htmlspecialchars($kegiatan['judul']) ?>">
                        <?php else: ?>
                            <div class="kegiatan-placeholder">📸</div>
                        <?php endif; ?>
                    </div>
                    <div class="kegiatan-content">
                        <div class="kegiatan-date"><?= formatTanggal($kegiatan['tanggal_kegiatan']) ?></div>
                        <h3><?= htmlspecialchars($kegiatan['judul']) ?></h3>
                        <p><?= substr($kegiatan['deskripsi'], 0, 150) ?>...</p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Prestasi -->
    <section id="achievement" class="section">
        <div class="container">
            <h2 class="section-title">Prestasi Sekolah</h2>
            <div class="achievement-grid">
                <?php while ($prestasi = $prestasiQuery->fetch_assoc()): ?>
                <div class="achievement-card">
                    <div class="achievement-image">
                        <?php if ($prestasi['foto']): ?>
                            <img src="<?= UPLOAD_DIR . $prestasi['foto'] ?>" alt="<?= htmlspecialchars($prestasi['nama_prestasi']) ?>">
                        <?php else: ?>
                            <div class="achievement-placeholder">🏆</div>
                        <?php endif; ?>
                    </div>
                    <div class="achievement-content">
                        <div class="achievement-badge"><?= htmlspecialchars($prestasi['peringkat']) ?></div>
                        <h3><?= htmlspecialchars($prestasi['nama_prestasi']) ?></h3>
                        <p class="achievement-level"><?= htmlspecialchars($prestasi['tingkat']) ?></p>
                        <p class="achievement-year"><?= htmlspecialchars($prestasi['tahun']) ?></p>
                        <?php if ($prestasi['keterangan']): ?>
                            <p class="achievement-desc"><?= substr($prestasi['keterangan'], 0, 100) ?>...</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Kontak -->
    <section id="kontak" class="section">
        <div class="container">
            <h2 class="section-title">Kontak Kami</h2>
            <div class="kontak-grid">
                <div class="kontak-item">
                    <div class="kontak-icon">📍</div>
                    <div>
                        <h4>Alamat</h4>
                        <p><?= htmlspecialchars($profil['alamat']) ?></p>
                    </div>
                </div>
                <div class="kontak-item">
                    <div class="kontak-icon">📞</div>
                    <div>
                        <h4>Telepon</h4>
                        <p><?= htmlspecialchars($profil['telepon']) ?></p>
                    </div>
                </div>
                <div class="kontak-item">
                    <div class="kontak-icon">✉</div>
                    <div>
                        <h4>Email</h4>
                        <p><?= htmlspecialchars($profil['email']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?= htmlspecialchars($profil['nama_sekolah']) ?>. All Rights Reserved.</p>
        </div>
    </footer>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('chartSekolah');
        if (!ctx) {
            console.error("Canvas chartSekolah tidak ditemukan!");
            return;
        }

        const jumlahSiswaAktif = <?= (int)$profil['jumlah_siswa_aktif'] ?>;
        const jumlahGuruAktif  = <?= (int)$profil['jumlah_guru_aktif'] ?>;
        const jumlahRuangKelas = <?= (int)$profil['jumlah_ruang_kelas'] ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Siswa Aktif', 'Guru Aktif', 'Ruang Kelas'],
                datasets: [{
                    label: 'Jumlah Data',
                    data: [jumlahSiswaAktif, jumlahGuruAktif, jumlahRuangKelas],
                    backgroundColor: ['#4F46E5', '#22C55E', '#F59E0B'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    });
</script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
