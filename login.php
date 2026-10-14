<?php
session_start();// Wajib ditaruh paling atas agar PHP bisa menggunakan sistem Session (gelang tangan)
include 'koneksi.php';//manggil koneksi.php untuk koneksi ke database

//mengecek session, dengan nama customerID sudah dibuat atau sudah ada
// Jika pengguna sudah login, langsung alihkan ke beranda
if (isset($_SESSION['customerID'])) {
    header("Location: index.php");//mengarahkan broswer ke index.php
    exit;
}

$pesan_error = '';//variabel untuk menampung pesan error jika login gagal

if ($_SERVER['REQUEST_METHOD'] === 'POST') {//memeriksa apakah halaman dibuka dengan setelah form dikirm dgn metode POST
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);//mengamankan email sebelum dimasukkan ke query SQL
    $password = $_POST['password'];

    $query = "SELECT * FROM customers WHERE email = '$email'";//panggilan untk mencari data di tabel customers berdasarkan email yang dimasukkan pengguna
    $result = mysqli_query($koneksi, $query);//menjalankan query dengan koneksi ke database, kemudian menyimpan hasilnya di variabel $result

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);//mengambil data pengguna dari hasil query dan menyimpannya dalam array asosiatif $user

        //mengecek password lalu menyimpan data pengguna ke session jika password benar
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            //menyimpan data pengguna ke session agar bisa digunakan di halaman lain
            $_SESSION['customerID']    = $user['customerID'];
            $_SESSION['customerName']  = $user['name'];
            $_SESSION['customerEmail'] = $user['email'];
            $_SESSION['customerPhone'] = $user['phone'];

            //balik ke halaman index.php jika login berhasil
            header("Location: index.php");//mengarahkan broswer ke halaman lain
            exit;
        } else {
            $pesan_error = "Password yang kamu masukkan salah!";
        }
    } else {
        $pesan_error = "Akun dengan email tersebut tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Akun - CineTix</title>
    
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
            <div href="index.php" class="text-decoration-none">
                <span class="brand-badge fs-4"><i class="bi bi-film text-white"></i> CINE</span><span class="fs-4 fw-bolder text-white">TIX</span>
            </div>
            <p class="text-secondary small mt-2">Masuk untuk memesan tiket dan melihat riwayat Anda</p>
        </div>

        <!-- Kartu Form Login -->
        <div class="card bg-dark bg-opacity-75 border border-secondary border-opacity-25 rounded-4 p-4 shadow-lg">
            <h4 class="fw-bold mb-4 text-white text-center">Masuk ke Akun</h4>

            <?php if (!empty($pesan_error)) { ?>
                <div class="alert alert-danger py-2 small d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?php echo $pesan_error; ?></div>
                </div>
            <?php } ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-semibold">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" required class="form-control bg-dark border-secondary text-light" placeholder="nama@email.com">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" required class="form-control bg-dark border-secondary text-light" placeholder="Masukkan password">
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold shadow">
                    Masuk Sekarang <i class="bi bi-box-arrow-in-right ms-1"></i>
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-25 text-secondary small">
                Belum punya akun? <a href="register.php" class="text-danger fw-semibold text-decoration-none">Daftar di sini</a>
                <div class="mt-2">
                    <a href="index.php" class="text-secondary text-decoration-none">&larr; Kembali ke Beranda</a>
                </div>
            </div>
        </div>

    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>