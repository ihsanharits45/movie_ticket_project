<?php
session_start();//manggil session_start() untuk memulai sesi pengguna

// Hancurkan semua data session (menggunting gelang tangan)
session_destroy();

// Kembalikan ke halaman utama
header("Location: index.php");
exit;
?>