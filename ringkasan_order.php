<?php
session_start();
include 'koneksi.php';

// 1. Cek apakah data dikirim via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$showtimeID = $_POST['showtime_id'] ?? null;
$selected_seats = $_POST['selected_seats'] ?? [];

// 2. Validasi: Jangan izinkan jika belum memilih kursi
if (empty($selected_seats)) {
    echo "<script>
            alert('Silakan pilih minimal 1 kursi!');
            window.location.href = 'pilih_kursi.php?showtime_id=$showtimeID';
          </script>";
    exit;
}

// 3. Ambil informasi jadwal tayang
$queryJadwal = "SELECT showtime.*, movies.title, movies.poster, studio.studio_name 
                FROM showtime 
                JOIN movies ON showtime.movieID = movies.movieID 
                JOIN studio ON showtime.studioID = studio.studioID 
                WHERE showtime.showtimeID = '$showtimeID'";
$resultJadwal = mysqli_query($koneksi, $queryJadwal);
$jadwal = mysqli_fetch_assoc($resultJadwal);

// 4. Ambil nama/kode kursi yang dipilih
$seat_ids_string = implode(',', array_map('intval', $selected_seats));
$queryKursi = "SELECT seat_code FROM seat WHERE seatID IN ($seat_ids_string) ORDER BY seatID ASC";
$resultKursi = mysqli_query($koneksi, $queryKursi);

$nama_kursi = [];
while ($seat = mysqli_fetch_assoc($resultKursi)) {
    $nama_kursi[] = $seat['seat_code'];
}

// 5. Hitung total biaya
$jumlah_tiket = count($selected_seats);
$harga_satuan = $jadwal['price'];
$total_harga  = $jumlah_tiket * $harga_satuan;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - CineTix</title>
    
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

<main class="py-4">
    <div class="container">
        
        <!-- Tombol Kembali -->
        <a href="pilih_kursi.php?showtime_id=<?php echo $showtimeID; ?>" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pilih Kursi
        </a>

        <h2 class="h3 fw-bold mb-4 text-white">
            <i class="bi bi-wallet2 text-danger me-2"></i>Konfirmasi &amp; Pembayaran
        </h2>

        <form action="proses_transaksi.php" method="POST">
            <!-- Data tersembunyi yang diteruskan ke proses transaksi -->
            <input type="hidden" name="showtime_id" value="<?php echo $showtimeID; ?>">
            <?php foreach ($selected_seats as $seat_id) { ?>
                <input type="hidden" name="selected_seats[]" value="<?php echo $seat_id; ?>">
            <?php } ?>
            <input type="hidden" name="total_price" value="<?php echo $total_harga; ?>">
            <input type="hidden" name="payment_method" value="QRIS">

            <div class="row g-4">
                
                <!-- KOLOM KIRI: Data Pemesan & Pembayaran QRIS -->
                <div class="col-lg-7">
                    
                    <!-- Informasi Pemesan -->
                    <div class="card bg-dark bg-opacity-50 border border-secondary border-opacity-25 rounded-4 p-4 mb-4">
                        <h5 class="fw-bold mb-3 text-light">
                            <i class="bi bi-person-badge text-warning me-2"></i>Informasi Pemesan
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary small">Nama Lengkap</label>
                                <input type="text" name="customer_name" required 
                                       class="form-control bg-dark border-secondary text-light"
                                       value="<?php echo $_SESSION['customerName'] ?? ''; ?>" 
                                       placeholder="Contoh: Budi Santoso">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary small">Email</label>
                                <input type="email" name="customer_email" required 
                                       class="form-control bg-dark border-secondary text-light"
                                       value="<?php echo $_SESSION['customerEmail'] ?? ''; ?>" 
                                       placeholder="Contoh: budi@gmail.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-secondary small">Nomor HP</label>
                                <input type="text" name="customer_phone" required 
                                       class="form-control bg-dark border-secondary text-light"
                                       value="<?php echo $_SESSION['customerPhone'] ?? ''; ?>" 
                                       placeholder="Contoh: 08123456789">
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Pembayaran QRIS -->
                    <div class="card bg-dark bg-opacity-50 border border-secondary border-opacity-25 rounded-4 p-4">
                        <h5 class="fw-bold mb-2 text-light">
                            <i class="bi bi-qr-code-scan text-danger me-2"></i>Metode Pembayaran: QRIS
                        </h5>
                        <p class="text-secondary small mb-3">Pembayaran diproses secara instan melalui <strong>QRIS Nasional</strong>.</p>

                        <!-- Box Barcode QRIS -->
                        <div class="p-3 rounded-3 border border-danger border-opacity-50 mb-3 text-center" style="background-color: #0f172a;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-white text-dark fw-bolder px-2 py-1 fs-6">QRIS</span>
                                <span class="badge bg-success"><i class="bi bi-lightning-charge-fill me-1"></i>Otomatis</span>
                            </div>

                            <div class="bg-white p-3 rounded-3 text-center text-dark mx-auto my-3" style="max-width: 230px; box-shadow: 0 4px 15px rgba(0,0,0,0.4);">
                                <div class="fw-bold small text-uppercase text-secondary mb-1">CINETIX CINEMA</div>
                                <div class="fw-bold text-danger small mb-2">NMID: ID1020304050607</div>
                                
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=CINETIX-QRIS-<?php echo urlencode($jadwal['title']); ?>-<?php echo $total_harga; ?>" 
                                     alt="QRIS Code" 
                                     class="img-fluid rounded border p-1 mb-2"
                                     style="width: 160px; height: 160px;">

                                <div class="fw-bold fs-5 text-dark">Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></div>
                                <div class="text-muted" style="font-size: 0.7rem;">Scan dengan Mobile Banking / E-Wallet</div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- KOLOM KANAN: Ringkasan Pesanan & Tombol Bayar -->
                <div class="col-lg-5">
                    <div class="card bg-dark bg-opacity-75 border border-secondary border-opacity-25 rounded-4 p-4 sticky-top" style="top: 90px;">
                        <h5 class="fw-bold mb-3 text-light d-flex align-items-center gap-2">
                            <i class="bi bi-receipt text-danger"></i>
                            <span>Ringkasan Pesanan</span>
                        </h5>

                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom border-secondary border-opacity-25">
                            <img src="assets/img/<?php echo $jadwal['poster']; ?>" alt="<?php echo $jadwal['title']; ?>" class="rounded-3" style="width: 60px; height: 85px; object-fit: cover;">
                            <div>
                                <h6 class="fw-bold text-light mb-1"><?php echo $jadwal['title']; ?></h6>
                                <div class="text-secondary small mb-1">
                                    <i class="bi bi-camera-reels text-warning me-1"></i><?php echo $jadwal['studio_name']; ?>
                                </div>
                                <div class="text-secondary small">
                                    <i class="bi bi-calendar-event me-1"></i><?php echo $jadwal['show_date']; ?> (<?php echo $jadwal['start_time']; ?>)
                                </div>
                            </div>
                        </div>

                        <!-- Kursi yang Dipilih -->
                        <div class="mb-3">
                            <div class="text-secondary small mb-2">Kursi yang Dipilih:</div>
                            <div>
                                <?php foreach ($nama_kursi as $code) { ?>
                                    <span class="badge bg-danger fs-6 px-3 py-2 me-1 mb-1"><?php echo $code; ?></span>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Rincian Harga -->
                        <div class="pt-3 border-top border-secondary border-opacity-25">
                            <div class="d-flex justify-content-between text-secondary small mb-2">
                                <span>Harga Tiket (x<?php echo $jumlah_tiket; ?>):</span>
                                <span>Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between text-secondary small mb-3">
                                <span>Biaya Layanan:</span>
                                <span class="text-success fw-bold">GRATIS</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-50 mb-4">
                                <span class="fw-bold text-light fs-5">Total Pembayaran:</span>
                                <span class="fw-bold text-success fs-4">Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></span>
                            </div>

                            <!-- Tombol Bayar -->
                            <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold shadow">
                                <i class="bi bi-shield-check me-2"></i>Bayar &amp; Konfirmasi
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </form>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>