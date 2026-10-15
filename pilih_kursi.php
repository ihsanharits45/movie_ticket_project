<?php
session_start();//session_start() dipanggil untuk memulai sesi pengguna, memungkinkan penggunaan variabel session di seluruh halaman web.
include 'koneksi.php';//menghubungkan file koneksi.php untuk mengakses database

// 1. Validasi showtime_id di URL
if (!isset($_GET['showtime_id'])) { //mengecek apakah parameter 'showtime_id' ada di URL.
    header("Location: index.php"); //jika tidak ada, maka langsung alihkan ke halaman index.php
    exit;
}

$showtimeID = $_GET['showtime_id'];//mengambil nilai parameter 'showtime_id' dari URL dan menyimpannya dalam variabel $showtimeID

// 2. Ambil detail jadwal (film, studio, harga)
$queryJadwal = "SELECT showtime.*, movies.title, movies.poster, studio.studio_name #mengambil semua data dari tabel showtime, judul film dan poster dari tabel movies, dan nama studio dari tabel studio
                FROM showtime #dijadikan sumber data utama
                JOIN movies ON showtime.movieID = movies.movieID #menggabungkan tabel showtime dengan movies untuk mendapatkan judul film dan poster
                JOIN studio ON showtime.studioID = studio.studioID #menggabungkan tabel showtime dengan studio untuk mendapatkan nama studio
                WHERE showtime.showtimeID = '$showtimeID'"; #dimana showtimeID di tabel showtime sama dengan $showtimeID
$resultJadwal = mysqli_query($koneksi, $queryJadwal);//mengirim query ke database menggunakan koneksi $koneksi dan menyimpan hasilnya dalam variabel $resultJadwal
$jadwal = mysqli_fetch_assoc($resultJadwal);//mengambil satu baris hasil query sebagai array asosiatif dan menyimpannya dalam variabel $jadwal

if (!$jadwal) {//! = not, jika $jadwal kosong atau tidak ada data
    die("Jadwal tayang tidak ditemukan!");//jika jadwal tayang tidak ditemukan, maka hentikan eksekusi dan tampilkan pesan error
}

$studioID = $jadwal['studioID'];//mengambil nilai studioID dari hasil query jadwal dan menyimpannya dalam variabel $studioID

// 3. Ambil daftar ID kursi yang SUDAH DIBELI untuk jadwal ini
$queryTerjual = "SELECT seatID FROM ticket WHERE showtimeID = '$showtimeID'"; //mengambil semua seatID dari tabel ticket yang sudah terjual untuk showtime tertentu
$resultTerjual = mysqli_query($koneksi, $queryTerjual); //mengirim query ke database menggunakan koneksi $koneksi dan menyimpan hasilnya dalam variabel $resultTerjual

$kursi_terjual = [];
while ($row = mysqli_fetch_assoc($resultTerjual)) { //mengambil setiap baris hasil query sebagai array asosiatif dan menyimpannya dalam variabel $row
    $kursi_terjual[] = $row['seatID']; //menambahkan seatID yang sudah terjual ke dalam array $kursi_terjual
}

// 4. Ambil SEMUA kursi di studio ini (diurutkan rapi berdasarkan seatID) //ASC = ascending, mengurutkan dari nilai terkecil ke terbesar
$queryKursi = "SELECT * FROM seat WHERE studioID = '$studioID' ORDER BY seatID ASC"; //mengambil semua data kursi dari tabel seat untuk studio tertentu dan mengurutkannya berdasarkan seatID secara ascending
$resultKursi = mysqli_query($koneksi, $queryKursi); //mengirim query ke database menggunakan koneksi $koneksi dan menyimpan hasilnya dalam variabel $resultKursi

// 5. Kelompokkan kursi per baris huruf (A, B, C, D, E)
$kursi_per_baris = [];
while ($seat = mysqli_fetch_assoc($resultKursi)) { //mengambil setiap baris hasil query sebagai array asosiatif dan menyimpannya dalam variabel $seat
    $huruf = substr($seat['seat_code'], 0, 1); //mengambil huruf pertama dari seat_code untuk menentukan baris kursi (misal: A1, B2, C3) //substr() digunakan untuk mengambil sebagian string, di sini mengambil karakter pertama dari seat_code
    $kursi_per_baris[$huruf][] = $seat; //menambahkan kursi ke dalam array $kursi_per_baris berdasarkan huruf barisnya
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Kursi - <?php echo $jadwal['title']; ?></title>
    
    <!-- Google Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* Trik CSS: saat checkbox dicentang, tombol kursi langsung hijau menyala */
        .seat-checkbox { display: none; }
        .seat-checkbox:checked + .seat-btn {
            background-color: var(--seat-selected) !important;
            border-color: #34d399 !important;
            color: #ffffff !important;
            transform: scale(1.08);
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.6);
        }
    </style>
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

<main class="py-4">
    <div class="container">
        
        <!-- Tombol Kembali -->
        <a href="jadwal.php?id=<?php echo $jadwal['movieID']; ?>" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Jadwal
        </a>

        <!-- Header Info Jadwal -->
        <div class="card bg-dark bg-opacity-75 border border-secondary border-opacity-25 rounded-4 p-3 mb-4">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3">
                        <img src="assets/img/<?php echo $jadwal['poster']; ?>" alt="<?php echo $jadwal['title']; ?>" class="rounded-3" style="width: 55px; height: 75px; object-fit: cover;">
                        <div>
                            <h4 class="fw-bold mb-1 text-light"><?php echo $jadwal['title']; ?></h4>
                            <div class="text-secondary small d-flex flex-wrap gap-2">
                                <span><i class="bi bi-camera-reels text-warning me-1"></i><?php echo $jadwal['studio_name']; ?></span>
                                <span>&bull;</span>
                                <span><i class="bi bi-calendar-event me-1"></i><?php echo $jadwal['show_date']; ?></span>
                                <span>&bull;</span>
                                <span class="text-warning fw-semibold"><i class="bi bi-clock me-1"></i><?php echo $jadwal['start_time']; ?> WIB</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-2 mt-md-0">
                    <div class="text-secondary small">Harga per Tiket:</div>
                    <div class="fs-4 fw-bold text-success">Rp <?php echo number_format($jadwal['price'], 0, ',', '.'); ?></div>
                </div>
            </div>
        </div>

        <!-- Form Pemilihan Kursi -->
        <form action="ringkasan_order.php" method="POST">
            <input type="hidden" name="showtime_id" value="<?php echo $showtimeID; ?>">

            <div class="card bg-dark bg-opacity-50 border border-secondary border-opacity-25 rounded-4 p-4 text-center">
                
                <!-- Keterangan Warna Kursi (Legend) -->
                <div class="seat-legend">
                    <div class="legend-item">
                        <div class="legend-box" style="background-color: var(--seat-available); border: 1px solid var(--seat-available-border); width: 18px; height: 18px; border-radius: 4px;"></div>
                        <span class="small text-secondary">Tersedia</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box" style="background-color: var(--seat-selected); border: 1px solid #34d399; width: 18px; height: 18px; border-radius: 4px;"></div>
                        <span class="small text-light">Pilihan Anda</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box" style="background-color: #3f1a24; border: 1px solid #7f1d1d; width: 18px; height: 18px; border-radius: 4px;"></div>
                        <span class="small text-danger">Sudah Terisi (Booked)</span>
                    </div>
                </div>

                <!-- Layar Bioskop Efek Lengkung -->
                <div class="screen-container">
                    <div class="cinema-screen">
                        <span class="screen-text"><i class="bi bi-tv me-2"></i>Layar Bioskop</span>
                    </div>
                </div>

                <!-- Matriks Baris Kursi -->
                <div class="seat-matrix my-4">
                    <?php foreach ($kursi_per_baris as $baris => $daftar_kursi) { ?>
                        <div class="seat-row">
                            <!-- Label Huruf Kiri -->
                            <div class="row-label"><?php echo $baris; ?></div>

                            <!-- Tombol Kursi -->
                            <?php 
                            $nomor = 0;
                            foreach ($daftar_kursi as $seat) { 
                                $nomor++;
                                $is_taken = in_array($seat['seatID'], $kursi_terjual);
                                $is_aisle = ($nomor === 5); // Beri celah lorong jalan di antara kursi nomor 5 dan 6
                            ?>
                                <?php if ($is_taken) { ?>
                                    <!-- Kursi sudah dibeli (merah) -->
                                    <div class="seat-btn seat-booked <?php echo $is_aisle ? 'seat-aisle-right' : ''; ?>" title="Kursi <?php echo $seat['seat_code']; ?> (Sudah Terisi)">
                                        <?php echo $seat['seat_code']; ?>
                                    </div>
                                <?php } else { ?>
                                    <!-- Kursi tersedia: Klik untuk memilih -->
                                    <input type="checkbox" name="selected_seats[]" value="<?php echo $seat['seatID']; ?>" id="seat-<?php echo $seat['seatID']; ?>" class="seat-checkbox">
                                    <label for="seat-<?php echo $seat['seatID']; ?>" class="seat-btn seat-available <?php echo $is_aisle ? 'seat-aisle-right' : ''; ?>" title="Pilih Kursi <?php echo $seat['seat_code']; ?>">
                                        <?php echo $seat['seat_code']; ?>
                                    </label>
                                <?php } ?>
                            <?php } ?>

                            <!-- Label Huruf Kanan -->
                            <div class="row-label"><?php echo $baris; ?></div>
                        </div>
                    <?php } ?>
                </div>

                <div class="text-secondary small mt-3">
                    <i class="bi bi-info-circle me-1"></i> Klik kursi abu-abu yang tersedia untuk memilih. Kursi yang kamu pilih akan menyala hijau.
                </div>

                <!-- Tombol Lanjut -->
                <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <button type="submit" class="btn btn-danger btn-lg px-5 fw-bold shadow">
                        Lanjut ke Pemesanan <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>

            </div>
        </form>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>