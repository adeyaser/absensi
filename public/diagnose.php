<?php
/**
 * Diagnose file untuk mencari penyebab Error 500
 * Upload file ini ke folder public_html atau public di hosting
 * Akses via: https://absensi.kalibaru.my.id/diagnose.php
 */

echo "<h1>Diagnosa Sistem</h1>";

// 1. Cek PHP Version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
if (version_compare(PHP_VERSION, '8.1', '<')) {
    echo "<span style='color:red'>❌ GAGAL: PHP harus versi 8.1 atau lebih tinggi!</span><br>";
} else {
    echo "<span style='color:green'>✅ OK</span><br>";
}

// 2. Cek Extensions
echo "<h2>2. PHP Extensions</h2>";
$required = ['intl', 'mbstring', 'json', 'mysqlnd', 'curl'];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "<span style='color:green'>✅ {$ext} - Aktif</span><br>";
    } else {
        echo "<span style='color:red'>❌ {$ext} - TIDAK AKTIF (wajib diaktifkan!)</span><br>";
    }
}

// 3. Cek Writable folder
echo "<h2>3. Folder Writable</h2>";
$writablePath = dirname(__DIR__) . '/writable';
if (is_dir($writablePath)) {
    echo "Folder writable ditemukan: {$writablePath}<br>";
    if (is_writable($writablePath)) {
        echo "<span style='color:green'>✅ Folder writable bisa ditulis</span><br>";
    } else {
        echo "<span style='color:red'>❌ Folder writable TIDAK bisa ditulis! Ubah permission ke 775 atau 777</span><br>";
    }
} else {
    echo "<span style='color:red'>❌ Folder writable TIDAK DITEMUKAN di: {$writablePath}</span><br>";
    echo "Coba cek path alternatif...<br>";
    
    // Coba path alternatif
    $altPaths = [
        __DIR__ . '/../writable',
        '/home/' . get_current_user() . '/writable',
        '/home/' . get_current_user() . '/public_html/../writable',
    ];
    foreach ($altPaths as $path) {
        if (is_dir($path)) {
            echo "Ditemukan di: {$path}<br>";
        }
    }
}

// 4. Cek file paths.php
echo "<h2>4. Cek App Paths</h2>";
$pathsFile = dirname(__DIR__) . '/app/Config/Paths.php';
if (file_exists($pathsFile)) {
    echo "<span style='color:green'>✅ File Paths.php ditemukan: {$pathsFile}</span><br>";
} else {
    echo "<span style='color:red'>❌ File Paths.php TIDAK DITEMUKAN: {$pathsFile}</span><br>";
    echo "Ini berarti path di index.php mungkin salah!<br>";
}

// 5. Cek vendor autoload
echo "<h2>5. Cek Vendor (Composer)</h2>";
$vendorPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($vendorPath)) {
    echo "<span style='color:green'>✅ Vendor autoload ditemukan</span><br>";
} else {
    echo "<span style='color:red'>❌ Vendor autoload TIDAK DITEMUKAN: {$vendorPath}</span><br>";
    echo "Anda perlu menjalankan 'composer install' atau upload folder vendor!</span><br>";
}

// 6. Info tambahan
echo "<h2>6. Info Server</h2>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
echo "Current Dir: " . __DIR__ . "<br>";
echo "Parent Dir: " . dirname(__DIR__) . "<br>";

echo "<hr><p>Setelah semua ✅ OK, hapus file diagnose.php ini!</p>";
