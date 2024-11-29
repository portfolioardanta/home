<?php
session_start();
include 'includes/db_config.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "Pengguna tidak ditemukan.";
    exit;
}

$user_id = $user['id'];

// Ambil data profil pengguna
$stmt_profile = $pdo->prepare("SELECT * FROM user_profile WHERE user_id = ?");
$stmt_profile->execute([$user_id]);
$profile = $stmt_profile->fetch();

// Jika profil pengguna tidak ditemukan, buat profil baru
if (!$profile) {
    // Profil default
    $profile = [
        'name' => 'Nama Anda',
        'bio' => 'Bio Anda',
        'profile_photo' => '',
        'social_media_links' => '[]',
        'highlights' => '[]',
        'portfolio' => '[]',
        'skills' => '[]',
        'contact' => 'Contoh: email@domain.com'
    ];
}

// echo "Selamat datang, " . htmlspecialchars($user['username']) . "<br>";

if (!empty($profile['profile_photo'])) {
    echo "<img src='assets/images/" . htmlspecialchars($profile['profile_photo']) . "' alt='Foto Profil' width='100'><br>";
}
// Menampilkan profil pengguna
// echo "<h1>Profil Pengguna</h1>";
echo "<p>Nama: " . htmlspecialchars($profile['name']) . "</p>";
echo "<p>Sorotan: " . htmlspecialchars($profile['highlights']) . "</p>";
echo "<p>Bio: " . htmlspecialchars($profile['bio']) . "</p>";
echo "<p>Portfolio: " . htmlspecialchars($profile['portfolio']) . "</p>";
echo "<p>Skills: " . htmlspecialchars($profile['skills']) . "</p>";
echo "<p>Kontak: " . htmlspecialchars($profile['contact']) . "</p>";
?>
