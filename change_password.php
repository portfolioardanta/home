<?php
session_start();
include 'includes/db_config.php'; // Koneksi ke database

// Fungsi untuk mengganti password
function change_password($old_password, $new_password) {
    global $pdo;

    // Ambil username dari sesi login
    $username = $_SESSION['username'];

    // Cek apakah pengguna sudah login
    if (empty($username)) {
        return "Anda harus login terlebih dahulu.";
    }

    // Ambil data pengguna dari database berdasarkan username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        return "Pengguna tidak ditemukan.";
    }

    // Verifikasi password lama
    if (!password_verify($old_password, $user['password'])) {
        return "Password lama tidak benar.";
    }

    // Hash password baru
    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password baru ke database
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->execute([$hashed_new_password, $username]);

    return "Password berhasil diganti.";
}

// Cek jika form ganti password disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    
    $result = change_password($old_password, $new_password);
    echo $result;
}
?>

<form method="POST" action="">
    Password Lama: <input type="password" name="old_password" required><br>
    Password Baru: <input type="password" name="new_password" required><br>
    <button type="submit">Ganti Password</button>
</form>
