<?php
session_start();//manggil session_start() untuk memulai sesi pengguna
include 'koneksi.php';//manggil koneksi.php untuk koneksi ke database

// 1. Validasi trx_id di URL
if (!isset($_GET['trx_id'])) {
    header("Location: index.php");
    exit;
}

$trxID = $_GET['trx_id'];

// 2. Ambil data transaksi & pelanggan
$queryTrx = "SELECT transactions.*, customers.name, customers.email, customers.phone 
             FROM transactions 
             JOIN customers ON transactions.customerID = customers.customerID 
             WHERE transactions.transactionID = '$trxID'";
$resultTrx = mysqli_query($koneksi, $queryTrx);
$trx = mysqli_fetch_assoc($resultTrx);

if (!$trx) {
    die("Transaksi tidak ditemukan!");
}

// 3. Ambil daftar tiket pada transaksi ini (termasuk poster film)
$queryTiket = "SELECT ticket.*, seat.seat_code, movies.title, movies.poster, studio.studio_name, showtime.show_date, showtime.start_time 
               FROM ticket 
               JOIN seat ON ticket.seatID = seat.seatID 
               JOIN showtime ON ticket.showtimeID = showtime.showtimeID 
               JOIN movies ON showtime.movieID = movies.movieID 
               JOIN studio ON showtime.studioID = studio.studioID 
               WHERE ticket.transactionID = '$trxID'";
$resultTiket = mysqli_query($koneksi, $queryTiket);
$jumlah_tiket = mysqli_num_rows($resultTiket);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket #<?php echo $trxID; ?> - CineTix</title>
    
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

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Notifikasi Berhasil -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-3 shadow" style="width: 70px; height: 70px;">
                        <i class="bi bi-check-lg display-5"></i>
                    </div>
                    <h2 class="fw-bold text-white">Pemesanan Tiket Berhasil!</h2>
                    <p class="text-secondary">Simpan E-Ticket ini atau tunjukkan kode tiket ke petugas bioskop saat penukaran.</p>
                </div>

                <!-- Kartu Tiket Utama -->
                <div class="ticket-card mb-4 shadow-lg">
                    
                    <!-- Header Tiket -->
                    <div class="ticket-header d-flex justify-content-between align-items-center p-4">
                        <div>
                            <span class="badge bg-black bg-opacity-50 text-light mb-1">CINETIX E-TICKET</span>
                            <h4 class="fw-bold mb-0 text-white">ID Transaksi: #TRX-<?php echo $trx['transactionID']; ?></h4>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success fs-6 mb-1"><i class="bi bi-check-circle me-1"></i>Lunas</span>
                            <div><span class="badge bg-danger"><i class="bi bi-qr-code me-1"></i><?php echo $trx['payment_method']; ?></span></div>
                        </div>
                    </div>

                    <!-- Isi Tiket -->
                    <div class="ticket-body p-4">
                        <div class="row mb-3">
                            <div class="col-sm-6 mb-2 mb-sm-0">
                                <div class="text-secondary small">Nama Pemesan:</div>
                                <div class="fw-bold text-light"><?php echo $trx['name']; ?></div>
                                <div class="text-secondary small"><?php echo $trx['email']; ?></div>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <div class="text-secondary small">Waktu Transaksi:</div>
                                <div class="fw-semibold text-light"><?php echo $trx['transaction_date']; ?> WIB</div>
                                <div class="text-secondary small">Total Bayar: <strong class="text-success">Rp <?php echo number_format($trx['total'], 0, ',', '.'); ?></strong></div>
                            </div>
                        </div>

                        <!-- Garis Perforasi Khas Tiket Bioskop -->
                        <div class="ticket-perforation my-4"></div>

                        <h5 class="fw-bold mb-3 text-light">
                            <i class="bi bi-ticket-detailed text-danger me-2"></i>Daftar Tiket (<?php echo $jumlah_tiket; ?> Tiket)
                        </h5>

                        <!-- Daftar Tiket Per Kursi -->
                        <div class="d-flex flex-column gap-3">
                            <?php while ($t = mysqli_fetch_assoc($resultTiket)) { ?>
                                <div class="p-3 rounded-3 border border-secondary border-opacity-25" style="background-color: #151d30;">
                                    <div class="row align-items-center">
                                        <div class="col-md-7 mb-2 mb-md-0">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="assets/img/<?php echo $t['poster']; ?>" alt="<?php echo $t['title']; ?>" class="rounded-2" style="width: 45px; height: 60px; object-fit: cover;">
                                                <div>
                                                    <h6 class="fw-bold text-light mb-1"><?php echo $t['title']; ?></h6>
                                                    <div class="text-secondary small">
                                                        <span><i class="bi bi-camera-reels text-warning me-1"></i><?php echo $t['studio_name']; ?></span> &bull;
                                                        <span><i class="bi bi-calendar-event me-1"></i><?php echo $t['show_date']; ?></span> &bull;
                                                        <span class="text-warning"><?php echo $t['start_time']; ?> WIB</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5 text-md-end">
                                            <div class="d-flex align-items-center justify-content-md-end gap-3">
                                                <div class="text-center">
                                                    <div class="text-secondary small">Kursi</div>
                                                    <span class="badge bg-danger fs-5 px-3 py-1"><?php echo $t['seat_code']; ?></span>
                                                </div>
                                                <div class="text-end">
                                                    <div class="text-secondary small">Kode Tiket</div>
                                                    <code class="text-warning fw-bold fs-6"><?php echo $t['ticketCode']; ?></code>
                                                    <div class="small mt-1">
                                                        <span class="badge <?php echo ($t['ticketStatus'] === 'Unused') ? 'bg-success' : 'bg-secondary'; ?>">
                                                            <?php echo $t['ticketStatus']; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex justify-content-center gap-3">
                    <button onclick="window.print()" class="btn btn-outline-light px-4">
                        <i class="bi bi-printer me-2"></i>Cetak E-Ticket
                    </button>
                    <a href="index.php" class="btn btn-danger px-4">
                        <i class="bi bi-house me-2"></i>Kembali ke Beranda
                    </a>
                </div>

            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>