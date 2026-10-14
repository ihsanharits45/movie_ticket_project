<?php
include 'koneksi.php';//manggil koneksi.php untuk koneksi ke database

$pesan_error = '';// Variabel untuk menampung pesan error jika pendaftaran gagal
$pesan_sukses = '';// Variabel untuk menampung pesan sukses jika pendaftaran berhasil

// Cek apakah formulir telah disubmit dengan metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = mysqli_real_escape_string($koneksi, $_POST['name']);//mengamankan nama sebelum dimasukkan ke query SQL
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);//mengamankan email sebelum dimasukkan ke query SQL
    $phone    = mysqli_real_escape_string($koneksi, $_POST['phone']);//mengamankan nomor telepon sebelum dimasukkan ke query SQL
    $password = $_POST['password'];

    // 1. Cek apakah email sudah terdaftar sebelumnya di tabel customers
    $cek_email = mysqli_query($koneksi, "SELECT * FROM customers WHERE email = '$email'");//mengecek email dengan query SQL di tabel customers. $koneksi memberitau mysqli_query() database mana yang digunakan.
    
    if (mysqli_num_rows($cek_email) > 0) {
        $pesan_error = "Email sudah terdaftar! Silakan gunakan email lain.";
    } else {
        // 2. Mengamankan password dengan hashing bawaan PHP
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // 3. Masukkan data ke tabel customers
        $query = "INSERT INTO customers (name, email, password, phone)  //menyimpan data ke tabel customers dengan query SQL. $koneksi memberitau mysqli_query() database mana yang digunakan.
                  VALUES ('$name', '$email', '$password_hash', '$phone')";//menyimpan data ke tabel customers dengan query SQL. $koneksi memberitau mysqli_query() database mana yang digunakan.
        
        if (mysqli_query($koneksi, $query)) {//menjalankan query dengan koneksi ke database
            $pesan_sukses = "Pendaftaran akun berhasil! Silakan login.";
        } else {
            $pesan_error = "Gagal mendaftar: " . mysqli_error($koneksi);//menampilkan error jika query gagal dijalankan. $koneksi memberitau mysqli_error() database mana yang digunakan.
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - CineTix</title>
    
    <!-- Google Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">

    <div class="container" style="max-width: 440px;">
        
        <!-- Logo CineTix -->
        <div class="text-center mb-4">
            <div class="d-inline-block">
                <span class="brand-badge fs-4 text-white"><i class="bi bi-film"></i> CINE</span><span class="fs-4 fw-bolder text-white">TIX</span>
            </div>
            <p class="text-secondary small mt-2">Buat akun untuk memesan tiket bioskop dengan mudah</p>
        </div>

        <!-- Kartu Form Register -->
        <div class="card bg-dark bg-opacity-75 border border-secondary border-opacity-25 rounded-4 p-4 shadow-lg">
            <h4 class="fw-bold mb-4 text-white text-center">Daftar Akun Baru</h4>

            <?php if (!empty($pesan_error)) { ?>
                <div class="alert alert-danger py-2 small d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?php echo $pesan_error; ?></div>
                </div>
            <?php } ?>

            <?php if (!empty($pesan_sukses)) { ?>
                <div class="alert alert-success py-2 small text-center">
                    <div><?php echo $pesan_sukses; ?></div>
                    <a href="login.php" class="btn btn-success btn-sm mt-2 fw-semibold">Klik di Sini untuk Login</a>
                </div>
            <?php } ?>

            <form action="register.php" method="POST">
                
                <!-- Nama Lengkap -->
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-person"></i></span>
                        <input type="text" name="name" required class="form-control bg-dark border-secondary text-light" placeholder="Contoh: Budi Santoso">
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" required class="form-control bg-dark border-secondary text-light" placeholder="nama@email.com">
                    </div>
                </div>

                <!-- Nomor HP -->
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">Nomor HP</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-telephone"></i></span>
                        <input type="text" name="phone" required class="form-control bg-dark border-secondary text-light" placeholder="08123456789">
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label class="form-label text-secondary small fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" required class="form-control bg-dark border-secondary text-light" placeholder="Buat password aman">
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold shadow">
                    Daftar Sekarang <i class="bi bi-person-plus ms-1"></i>
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-25 text-secondary small">
                Sudah punya akun? <a href="login.php" class="text-danger fw-semibold text-decoration-none">Masuk di sini</a>
                <div class="mt-2">
                    <a href="index.php" class="text-secondary text-decoration-none">&larr; Kembali ke Beranda</a>
                </div>
            </div>
        </div>

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>