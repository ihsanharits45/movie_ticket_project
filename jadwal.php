<?php
session_start();//manggil session_start() untuk memulai sesi pengguna
include 'koneksi.php';//manggil koneksi.php untuk koneksi ke database

// 1. Validasi: Pastikan ada ID film di URL
if (!isset($_GET['id'])) { //mengecek apakah parameter 'id' ada di URL.
    header("Location: index.php");//jika tidak ada, maka langsung alihkan ke halaman index.php
    exit;
}

$movieID = $_GET['id'];//mengambil nilai parameter 'id' dari URL dan menyimpannya dalam variabel $movieID

// 2. Ambil detail film beserta genrenya
$queryFilm = "SELECT movies.*, GROUP_CONCAT(genres.genre_name SEPARATOR ', ') AS genre_list  #mengambil semua data dari tabel movies dan menggabungkan nama genre dari tabel genre menjadi 1 teks, hasilnya diberi nama genre_list
              FROM movies #dijadikan sumber data utama
              LEFT JOIN movie_genres ON movies.movieID = movie_genres.movieID #menggabungkan tabel movies dengan movie_genres untuk mendapatkan genre film
              LEFT JOIN genres ON movie_genres.genreID = genres.genreID  #menggabungkan tabel movie_genres dengan genres untuk mendapatkan nama genre 
              WHERE movies.movieID = '$movieID' #memfilter hasil query untuk hanya mengambil film dengan ID yang sesuai dengan $movieID
              GROUP BY movies.movieID"; //mengelompokkan hasil agar setiap film hanya muncul sekali
$resultFilm = mysqli_query($koneksi, $queryFilm); //mengirim query ke database menggunakan koneksi $koneksi dan menyimpan hasilnya dalam variabel $resultFilm
$film = mysqli_fetch_assoc($resultFilm); //mengambil satu baris hasil query sebagai array asosiatif dan menyimpannya dalam variabel $film

if (!$film) {
    die("Film tidak ditemukan!");//jika film tidak ditemukan, maka hentikan eksekusi dan tampilkan pesan error
}

// 3. Ambil jadwal tayang film ini digabung dengan studio
$queryJadwal = "SELECT showtime.*, studio.studio_name #mengambil semua data dari tabel showtime dan nama studio dari tabel studio
                FROM showtime #dijadikan sumber data utama
                JOIN studio ON showtime.studioID = studio.studioID #menggabungkan tabel showtime dengan studio untuk mendapatkan nama studio
                WHERE showtime.movieID = '$movieID' #dimana movieID di tabel showtime sama dengan $movieID
                ORDER BY showtime.show_date ASC, showtime.start_time ASC";
$resultJadwal = mysqli_query($koneksi, $queryJadwal); //mengirim query ke database menggunakan koneksi $koneksi dan menyimpan hasilnya dalam variabel $resultJadwal
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $film['title']; ?> - CineTix</title>
    
    <!-- Google Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navbar Konsisten -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-cinema sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <span class="brand-badge"><i class="bi bi-film"></i> CINE</span>TIX
        </a>
        <div class="d-flex align-items-center gap-2">
            <?php if (isset($_SESSION['customerID'])) { ?>
                <span class="text-light small me-2">
                    <i class="bi bi-person-circle"></i> <?php echo $_SESSION['customerName']; ?>
                </span>
                <a href="tiket_saya.php" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-ticket-perforated me-1"></i> Tiket Saya
                </a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
            <?php } else { ?>
                <a href="login.php" class="btn btn-outline-light btn-sm px-3">Masuk</a>
                <a href="register.php" class="btn btn-danger btn-sm px-3">Daftar</a>
            <?php } ?>
        </div>
    </div>
</nav>

<!-- Konten Jadwal -->
<main class="py-4">
    <div class="container">
        
        <!-- Tombol Kembali -->
        <a href="index.php" class="btn btn-outline-secondary btn-sm mb-4">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Film
        </a>

        <!-- Detail Film Hero -->
        <div class="row g-4 mb-5 align-items-center">
            <div class="col-md-4 col-lg-3">
                <div class="movie-poster-wrap rounded-4 shadow-lg border border-secondary border-opacity-25">
                    <span class="age-badge"><?php echo $film['age_rating']; ?></span>
                    <img src="assets/img/<?php echo $film['poster']; ?>" alt="<?php echo $film['title']; ?>">
                </div>
            </div>
            <div class="col-md-8 col-lg-9">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-danger fs-6"><?php echo $film['age_rating']; ?></span>
                    <span class="badge bg-dark border border-secondary text-secondary">
                        <i class="bi bi-clock me-1"></i><?php echo $film['duration']; ?> Menit
                    </span>
                </div>
                <h1 class="display-5 fw-bold mb-3 text-white"><?php echo $film['title']; ?></h1>
                
                <div class="mb-3">
                    <span class="text-secondary me-2">Genre:</span>
                    <?php 
                    $genres = explode(', ', $film['genre_list'] ?? 'Umum');
                    foreach ($genres as $g) { 
                    ?>
                        <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-25 me-1 px-3 py-2">
                            <?php echo $g; ?>
                        </span>
                    <?php } ?>
                </div>

                <p class="text-secondary lead">
                    Saksikan keseruan <strong><?php echo $film['title']; ?></strong> di bioskop kesayangan Anda. Pilih jadwal tayang dan pesan kursi sekarang.
                </p>
            </div>
        </div>

        <!-- Bagian Daftar Jadwal Tayang -->
        <div class="card bg-dark bg-opacity-50 border border-secondary border-opacity-25 rounded-4 p-4 text-light">
            <h3 class="h4 fw-bold mb-4 d-flex align-items-center gap-2 text-white">
                <i class="bi bi-calendar3 text-danger"></i>
                <span>Jadwal Penayangan</span>
            </h3>

            <?php if (mysqli_num_rows($resultJadwal) > 0) { ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-secondary border-secondary">
                                <th>Tanggal</th>
                                <th>Jam Tayang</th>
                                <th>Studio</th>
                                <th>Harga</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($jadwal = mysqli_fetch_assoc($resultJadwal)) { ?>
                                <tr>
                                    <td><i class="bi bi-calendar-event me-2 text-danger"></i><?php echo $jadwal['show_date']; ?></td>
                                    <td><i class="bi bi-clock me-2 text-info"></i><?php echo $jadwal['start_time']; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $jadwal['studio_name']; ?></span></td>
                                    <td class="fw-bold text-success">Rp <?php echo number_format($jadwal['price'], 0, ',', '.'); ?></td>
                                    <td class="text-end">
                                        <a href="pilih_kursi.php?showtime_id=<?php echo $jadwal['showtimeID']; ?>" class="btn btn-danger btn-sm px-3 fw-semibold">
                                            <i class="bi bi-ticket-perforated me-1"></i> Pilih Kursi
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="text-center py-4 text-secondary">
                    <i class="bi bi-calendar-x display-4 d-block mb-2"></i>
                    Belum ada jadwal penayangan yang tersedia untuk film ini.
                </div>
            <?php } ?>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>