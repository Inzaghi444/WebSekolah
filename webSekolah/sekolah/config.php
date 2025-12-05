<?php
ob_start();
session_start();


// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_sekolah');

// Koneksi Database
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// ============================================
// SITE CONFIGURATION
// ============================================
define('BASE_URL', 'http://localhost/sekolah/');
define('PROJECT_FOLDER', 'webSekolah');
define('UPLOAD_DIR', 'sekolah/uploads');

// ===============================
// HELPER FUNCTIONS
// ===============================

// Mengecek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Mengecek role user
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Paksa login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Paksa role tertentu
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        redirect('index.php');
    }
}

// Upload file function (improved)
function uploadFile($file, $folder = '')
{
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        return ['error' => true, 'message' => 'Tipe file tidak diperbolehkan'];
    }

    // Path fisik
    $baseUploadDir = $_SERVER['DOCUMENT_ROOT'] . '/' . PROJECT_FOLDER . '/' . UPLOAD_DIR;

    if ($folder !== '') {
        $baseUploadDir .= '/' . $folder;
    }

    if (!is_dir($baseUploadDir)) {
        mkdir($baseUploadDir, 0777, true);
    }

    $filename = uniqid() . '.' . $allowed[$mime];
    $destination = $baseUploadDir . '/' . $filename;

    move_uploaded_file($file['tmp_name'], $destination);

    // PATH UNTUK <img> SESUAI ROOT PROJECT
    $relativePath = '/' . PROJECT_FOLDER . '/' . UPLOAD_DIR;
    if ($folder !== '') {
        $relativePath .= '/' . $folder;
    }
    $relativePath .= '/' . $filename;

    return ['error' => false, 'path' => $relativePath];
}

// Format tanggal ke Bahasa Indonesia
function formatTanggal($date) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $tanggal = date('d', $timestamp);
    $bulanNama = $bulan[date('n', $timestamp)];
    $tahun = date('Y', $timestamp);
    
    return $tanggal . ' ' . $bulanNama . ' ' . $tahun;
}

// Alert message function
function alert($message, $type = 'success') {
    $icon = $type === 'success' ? '✓' : '✕';
    $color = $type === 'success' ? '#10b981' : '#ef4444';
    echo "<script>alert('$icon $message');</script>";
}

// Sanitize input
function clean($string) {
    global $conn;
    return $conn->real_escape_string(trim($string));
}

// Get current user data
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $query = $conn->query("SELECT * FROM users WHERE id = $user_id");
    return $query->fetch_assoc();
}
?>