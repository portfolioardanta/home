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

// Menangani upload foto profil
if (isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        // Cek ekstensi file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['profile_photo']['name'];
        $file_tmp = $_FILES['profile_photo']['tmp_name'];
        $file_size = $_FILES['profile_photo']['size'];

        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Validasi ekstensi file
        if (!in_array($file_ext, $allowed_extensions)) {
            echo "Format file tidak valid. Harap upload file JPG, JPEG, PNG, atau GIF.";
            exit;
        }

        // Validasi ukuran file (maksimum 2MB)
        if ($file_size > 2 * 1024 * 1024) {
            echo "Ukuran file terlalu besar. Maksimal 2MB.";
            exit;
        }

        // Membuat nama file unik
        $new_file_name = uniqid('profile_', true) . '.' . $file_ext;

        // Menyimpan file di folder "assets/images/"
        $target_dir = 'assets/images/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Membuat folder jika belum ada
        }

        $target_file = $target_dir . $new_file_name;

        // Pindahkan file ke folder yang sesuai
        if (move_uploaded_file($file_tmp, $target_file)) {
            // Update nama file foto profil di database
            $stmt_update_photo = $pdo->prepare("UPDATE user_profile SET profile_photo = ? WHERE user_id = ?");
            $stmt_update_photo->execute([$new_file_name, $user_id]);

            // Redirect kembali ke halaman edit profil atau dashboard
            header("Location: edit_profile.php"); // atau dashboard.php jika diperlukan
            exit;
        } else {
            echo "Terjadi kesalahan saat mengupload file.";
        }
    } else {
        echo "Tidak ada file yang diupload atau terjadi kesalahan dalam proses upload.";
    }
}
?>
