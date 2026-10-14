<?php
session_start();//manggil session_start() untuk memulai sesi pengguna
include 'koneksi.php';//manggil koneksi.php untuk koneksi ke database

// Query tetap sama persis seperti yang kita pelajari
$query = "SELECT movies.*, GROUP_CONCAT(genres.genre_name SEPARATOR ', ') AS genre_list //mengambil semua data dari tabel movies dan menggabungkan nama genre dari tabel genre menjadi 1 teks, hasilnya diberi nama genre_list
          FROM movies //dijadikan sumber data utama dari tabel movies
          LEFT JOIN movie_genres ON movies.movieID = movie_genres.movieID //menggabungkan tabel movies dengan movie_genres untuk mendapatkan genre film
          LEFT JOIN genres ON movie_genres.genreID = genres.genreID  //menggabungkan tabel movie_genres dengan genres untuk mendapatkan nama genre 
          GROUP BY movies.movieID"; //mengelompokkan hasil agar setiap film hanya muncul sekali, meskipun memiliki beberapa genre

$result = mysqli_query($koneksi, $query);//menjalankan query dengan koneksi ke database, kemudian menyimpan hasilnya di variabel $result
$jumlah_film = mysqli_num_rows($result);//menghitung jumlah film yang sedang tayang dengan menghitung jumlah baris hasil query
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineTix Cinema - Film Sedang Tayang</title>
    
    <!-- 1. Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- 2. Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- 3. CSS Tema Bioskop dari Project Sebelumnya -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navbar Modern -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-cinema sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <span class="brand-badge"><i class="bi bi-film"></i> CINE</span>TIX
        </a>
        <div class="d-flex align-items-center gap-2">
            <?php if (isset($_SESSION['customerID'])) { ?>
                <!-- Menu jika pengguna SUDAH login -->
                <span class="text-light small mx-2">
                    <i class="bi bi-person-circle"></i> <?php echo $_SESSION['customerName']; ?>
                </span>
                <a href="tiket_saya.php" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-ticket-perforated me-1"></i> Tiket Saya
                </a>

                <a href="logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
            <?php } else { ?>
                <!-- Menu jika pengguna BELUM login -->
                <a href="login.php" class="btn btn-outline-light btn-sm px-3">Masuk</a>
                <a href="register.php" class="btn btn-danger btn-sm px-3">Daftar</a>
            <?php } ?>
        </div>
    </div>
</nav>

<!-- Konten Utama -->
<main class="py-4">
    <div class="container">

        <!-- Hero / Banner Promo -->
        <div class="p-4 p-md-5 mb-4 rounded-4 text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); border: 1px solid rgba(255,255,255,0.1);">
            <div class="col-lg-8 px-0">
                <span class="badge bg-danger mb-2 px-3 py-2 text-uppercase fw-bold">Now Showing</span>
                <h1 class="display-5 fw-bold">Nikmati Sensasi Menonton Terbaik</h1>
                <p class="lead my-3 text-light text-opacity-75">
                    Pilih film favorit Anda, cek jadwal tayang di berbagai studio, dan pesan kursi bioskop pilihan secara instan.
                </p>
            </div>
        </div>

        <!-- Judul Bagian -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1">
                    <i class="bi bi-film text-danger me-2"></i>Daftar Film Sedang Tayang
                </h2>
                <p class="text-secondary small mb-0">Klik pada film untuk melihat jadwal dan memesan kursi</p>
            </div>
            <span class="badge bg-dark border border-secondary text-secondary">
                <?php echo $jumlah_film; ?> Film Tersedia
            </span>
        </div>

        <!-- Grid Daftar Film -->
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4">
            <?php while ($film = mysqli_fetch_assoc($result)) { ?>
                <div class="col">
                    <div class="movie-card">
                        
                        <!-- Poster Film & Rating Usia -->
                        <div class="movie-poster-wrap">
                            <span class="age-badge"><?php echo $film['age_rating']; ?></span>
                            <img src="assets/img/<?php echo $film['poster']; ?>" alt="<?php echo $film['title']; ?>">
                        </div>

                        <!-- Keterangan Film -->
                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <div class="text-secondary small mb-1 d-flex align-items-center gap-1">
                                <i class="bi bi-clock"></i>
                                <span><?php echo $film['duration']; ?> Menit</span>
                            </div>

                            <h5 class="fw-bold mb-1 fs-6 text-truncate" title="<?php echo $film['title']; ?>">
                                <?php echo $film['title']; ?>
                            </h5>

                            <p class="text-secondary small mb-3 text-truncate">
                                <?php echo $film['genre_list'] ? $film['genre_list'] : 'Umum'; ?>
                            </p>

                            <!-- Tombol Beli Tiket -->
                            <div class="mt-auto">
                                <a href="jadwal.php?id=<?php echo $film['movieID']; ?>" class="btn btn-outline-danger btn-sm w-100 fw-semibold">
                                    <i class="bi bi-ticket-perforated me-1"></i> Beli Tiket
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            <?php } ?>
        </div>

    </div>
</main>

<!-- Footer -->
<footer class="py-4 mt-5 text-center text-secondary small border-top border-secondary border-opacity-25">
    <div class="container">
        &copy; 2026 CineTix Cinema. All rights reserved.
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>