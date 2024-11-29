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

// Ambil data profil pengguna dari tabel user_profile
$stmt_profile = $pdo->prepare("SELECT * FROM user_profile WHERE user_id = ?");
$stmt_profile->execute([$user_id]);
$profile = $stmt_profile->fetch();

// Jika profil pengguna tidak ditemukan, buat profil baru
if (!$profile) {
    // Menyediakan data profil default
    $profile = [
        'name' => 'Nama Anda',
        'bio' => 'Bio Anda',
        'profile_photo' => '',
        'social_media_links' => '[]',  // Format JSON untuk social media links
        'highlights' => '[]',          // Format JSON untuk highlights
        'portfolio' => '[]',           // Format JSON untuk portfolio
        'skills' => '[]',              // Format JSON untuk skills
        'contact' => 'Contoh: email@domain.com'
    ];

    // Membuat profil baru secara default
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt_insert = $pdo->prepare("INSERT INTO user_profile (user_id, name, bio, social_media_links, highlights, portfolio, skills, contact) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->execute([
            $user_id,
            $_POST['name'],
            $_POST['bio'],
            json_encode($_POST['social_media_links']), // JSON encoding
            json_encode($_POST['highlights']),
            json_encode($_POST['portfolio']),
            json_encode($_POST['skills']),
            $_POST['contact']
        ]);

        // Setelah profil dibuat, arahkan pengguna ke dashboard
        header("Location: dashboard.php");
        exit;
    }
}

// Update data profil jika form disubmit
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $highlights = $_POST['highlights'];
    $social_media_links = $_POST['social_media_links'];
    $bio = $_POST['bio'];
    $portfolio = $_POST['portfolio'];
    $skills = $_POST['skills'];
    $contact = $_POST['contact'];

    // Update data profil di database
    $stmt_update = $pdo->prepare("UPDATE user_profile SET name = ?, highlights = ?, social_media_links = ?, bio = ?, portfolio = ?, skills = ?, contact = ? WHERE user_id = ?");
    $stmt_update->execute([$name, $highlights, $social_media_links, $bio, $portfolio, $skills, $contact, $user_id]);

    // Setelah data diperbarui, arahkan kembali ke dashboard
    header("Location: dashboard.php");
    exit;
}

// Hapus profil jika form disubmit
if (isset($_POST['delete_profile'])) {
    $stmt_delete = $pdo->prepare("DELETE FROM user_profile WHERE user_id = ?");
    $stmt_delete->execute([$user_id]);
    header("Location: dashboard.php"); // Redirect to dashboard
    exit;
}
?>

<h1>Edit Profil</h1>
<form method="POST" action="">
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

<!-- Tombol untuk menghapus profil -->
<form method="POST" action="">
    <button type="submit" name="delete_profile">Hapus Profil</button>
</form>

<a href="logout.php">Logout</a>
