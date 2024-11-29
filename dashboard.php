<?php
session_start();
include 'includes/db_config.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Ambil data pengguna dari database berdasarkan username
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    // Jika pengguna tidak ditemukan, arahkan ke login
    header('Location: login.php');
    exit;
}

$user_id = $user['id'];

// Ambil data profil pengguna dari tabel user_profile
$stmt_profile = $pdo->prepare("SELECT * FROM user_profile WHERE user_id = ?");
$stmt_profile->execute([$user_id]);
$profile = $stmt_profile->fetch();

// Cek apakah profil ditemukan
if (!$profile) {
    // Jika profil tidak ditemukan, tampilkan pesan atau arahkan ke halaman lain
    echo "Profil pengguna tidak ditemukan. Silakan update profil Anda.";
    exit;
}
// Menangani update sorotan
if (isset($_POST['update_highlights'])) {
    $new_highlight = $_POST['new_highlight'];

    // Mengambil sorotan yang sudah ada dan menambah sorotan baru
    $current_highlights = json_decode($profile['highlights'], true); // Mengambil sorotan dalam format array
    if (!$current_highlights) {
        $current_highlights = []; // Jika kosong, buat array baru
    }
    $current_highlights[] = $new_highlight; // Menambah sorotan baru

    // Update highlights dengan array sorotan yang baru
    $updated_highlights = json_encode($current_highlights); // Mengubah kembali menjadi format JSON

    // Update database
    $stmt_update = $pdo->prepare("UPDATE user_profile SET highlights = ? WHERE user_id = ?");
    $stmt_update->execute([$updated_highlights, $user_id]);

    header('Location: dashboard.php'); // Refresh halaman setelah update
    exit;
}


// Menangani hapus sorotan
if (isset($_GET['delete_highlight'])) {
    $highlight_to_delete = $_GET['delete_highlight'];

    // Mengambil sorotan yang sudah ada dan menghapus sorotan yang dipilih
    $current_highlights = json_decode($profile['highlights'], true);
    if (($key = array_search($highlight_to_delete, $current_highlights)) !== false) {
        unset($current_highlights[$key]); // Menghapus sorotan dari array
    }

    // Update database dengan sorotan yang sudah dihapus
    $updated_highlights = json_encode(array_values($current_highlights)); // Re-index array

    $stmt_delete = $pdo->prepare("UPDATE user_profile SET highlights = ? WHERE user_id = ?");
    $stmt_delete->execute([$updated_highlights, $user_id]);

    header('Location: dashboard.php'); // Refresh halaman setelah hapus
    exit;
}


?>

<h1>Dashboard - <?= htmlspecialchars($user['username']) ?></h1>

<!-- Form untuk edit profil -->
<form action="edit_profile.php" method="POST">
    <label>Nama:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($profile['name']) ?>"><br>

    <label>Sorotan:</label>
    <textarea name="highlights"><?= htmlspecialchars($profile['highlights']) ?></textarea><br>

    <label>Link Sosial Media (JSON format):</label>
    <textarea name="social_media_links"><?= htmlspecialchars($profile['social_media_links']) ?></textarea><br>

    <label>Tentang (Bio):</label>
    <textarea name="bio"><?= htmlspecialchars($profile['bio']) ?></textarea><br>

    <label>Portfolio (JSON format):</label>
    <textarea name="portfolio"><?= htmlspecialchars($profile['portfolio']) ?></textarea><br>

    <label>Skills (JSON format):</label>
    <textarea name="skills"><?= htmlspecialchars($profile['skills']) ?></textarea><br>

    <label>Kontak:</label>
    <textarea name="contact"><?= htmlspecialchars($profile['contact']) ?></textarea><br>

    <button type="submit" name="update_profile">Update Profil</button>
</form>

<!-- Form untuk upload foto profil -->
<form action="upload_profile_photo.php" method="POST" enctype="multipart/form-data">
    <label>Foto Profil:</label>
    <input type="file" name="profile_photo"><br>
    <button type="submit" name="upload_photo">Upload Foto</button>
</form>

<!-- Form untuk tambah sorotan -->
<h3>Tambah Sorotan Baru:</h3>
<form action="dashboard.php" method="POST">
    <label>Sorotan Baru:</label>
    <input type="text" name="new_highlight" required><br>
    <button type="submit" name="update_highlights">Tambah Sorotan</button>
</form>

<!-- Menampilkan hasil dari profil -->
<h2>Profil Pengguna:</h2>
<?php if (!empty($profile['profile_photo'])): ?>
    <img src="assets/images/<?= htmlspecialchars($profile['profile_photo']) ?>" alt="Foto Profil" width="100"><br>
<?php endif; ?>

<p>Nama: <?= htmlspecialchars($profile['name']) ?></p>
<p>Sorotan: <?= htmlspecialchars($profile['highlights']) ?></p>
<p>Bio: <?= htmlspecialchars($profile['bio']) ?></p>
<p>Portfolio: <?= htmlspecialchars($profile['portfolio']) ?></p>
<p>Skills: <?= htmlspecialchars($profile['skills']) ?></p>
<p>Kontak: <?= htmlspecialchars($profile['contact']) ?></p>

<!-- Menampilkan sorotan yang bisa dihapus atau diedit -->
<h3>Daftar Sorotan:</h3>
<?php
$highlights = json_decode($profile['highlights'], true); // Menampilkan sorotan dalam format array JSON
if (!empty($highlights)) {
    foreach ($highlights as $highlight) {
        echo "<p>" . htmlspecialchars($highlight) . " <a href='dashboard.php?delete_highlight=" . urlencode($highlight) . "'>Hapus</a></p>";
    }
} else {
    echo "<p>Belum ada sorotan.</p>";
}
?>

<a href="logout.php">Logout</a>
