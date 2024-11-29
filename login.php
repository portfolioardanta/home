<?php
session_start();
session_regenerate_id(true); // Mengganti ID session untuk mencegah serangan session fixation

include 'includes/db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek pengguna di database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);

    $user = $stmt->fetch();

    // Jika pengguna ada, verifikasi password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username']; // Simpan session
        header('Location: dashboard.php');
        exit;
    } else {
        echo "Username atau password salah!";
    }
}
?>

<form method="POST" action="">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>
