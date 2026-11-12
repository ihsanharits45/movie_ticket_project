<?php
// Pengaturan koneksi database
$host     = "localhost";
$user     = "root";
$password = "";
$database = "db_movieticket";

// Membuat koneksi ke database MySQL
$koneksi = mysqli_connect($host, $user, $password, $database);

// Cek apakah koneksi berhasil atau gagal
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Catatan: Jika berhasil, kode di bawah ini bisa dihapus/dikomentari nanti
// echo "Koneksi ke database berhasil!";
?>