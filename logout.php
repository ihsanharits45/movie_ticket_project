<?php
session_start();

// Hancurkan semua data session (menggunting gelang tangan)
session_destroy();

// Kembalikan ke halaman utama
header("Location: index.php");
exit;
?>