<?php
session_start();
session_unset(); // Menghapus semua session variables
session_destroy(); // Menghancurkan sesi

header('Location: login.php');
exit;
?>
