<?php
session_start();
include 'koneksi.php';

// 1. Wajib login untuk membuka halaman ini
if (!isset($_SESSION['customerID'])) {
    header("Location: login.php");
    exit;
}

$customerID = $_SESSION['customerID'];

// 2. Ambil semua tiket milik customer ini dari database
$query = "SELECT ticket.*, transactions.transaction_date, movies.title, movies.poster, 
                 studio.studio_name, seat.seat_code, showtime.show_date, showtime.start_time
          FROM transactions
          JOIN ticket ON transactions.transactionID = ticket.transactionID
          JOIN seat ON ticket.seatID = seat.seatID
          JOIN showtime ON ticket.showtimeID = showtime.showtimeID
          JOIN movies ON showtime.movieID = movies.movieID
          JOIN studio ON showtime.studioID = studio.studioID
          WHERE transactions.customerID = '$customerID'
          ORDER BY transactions.transaction_date DESC";

$result = mysqli_query($koneksi, $query);
$jumlah_tiket = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - CineTix</title>
    
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
            <span class="text-light small me-2">
                <i class="bi bi-person-circle"></i> <?php echo $_SESSION['customerName']; ?>
            </span>
            <a href="tiket_saya.php" class="btn btn-info btn-sm text-dark fw-bold">
                <i class="bi bi-ticket-perforated me-1"></i> Tiket Saya
            </a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </a>
        </div>
    </div>
</nav>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <!-- Header Halaman -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h2 class="h3 fw-bold mb-1 text-white">
                            <i class="bi bi-ticket-perforated text-danger me-2"></i>Tiket Saya
                        </h2>
                        <p class="text-secondary small mb-0">Daftar seluruh riwayat pemesanan tiket bioskop Anda</p>
                    </div>
                    <span class="badge bg-dark border border-secondary text-secondary">
                        <?php echo $jumlah_tiket; ?> Tiket Tersimpan
                    </span>
                </div>

                <!-- Daftar Tiket -->
                <?php if ($jumlah_tiket > 0) { ?>
                    <div class="d-flex flex-column gap-3">
                        <?php while ($t = mysqli_fetch_assoc($result)) { ?>
                            <div class="card bg-dark bg-opacity-50 border border-secondary border-opacity-25 rounded-4 p-3 shadow-sm">
                                <div class="row align-items-center">
                                    
                                    <!-- Poster & Detail Film -->
                                    <div class="col-md-7 mb-3 mb-md-0">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="assets/img/<?php echo $t['poster']; ?>" alt="<?php echo $t['title']; ?>" class="rounded-3 shadow" style="width: 65px; height: 90px; object-fit: cover;">
                                            <div>
                                                <h5 class="fw-bold text-light mb-1"><?php echo $t['title']; ?></h5>
                                                <div class="text-secondary small mb-1">
                                                    <i class="bi bi-camera-reels text-warning me-1"></i><?php echo $t['studio_name']; ?> &bull; 
                                                    <i class="bi bi-calendar-event me-1"></i><?php echo $t['show_date']; ?>
                                                </div>
                                                <div class="text-warning small fw-semibold">
                                                    <i class="bi bi-clock me-1"></i><?php echo $t['start_time']; ?> WIB
                                                </div>
                                                <div class="text-secondary" style="font-size: 11px; margin-top: 4px;">
                                                    Dibeli: <?php echo $t['transaction_date']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kursi, Kode, & Tombol -->
                                    <div class="col-md-5 text-md-end">
                                        <div class="d-flex align-items-center justify-content-md-end gap-3">
                                            <div class="text-center">
                                                <div class="text-secondary small">Kursi</div>
                                                <span class="badge bg-danger fs-5 px-3 py-1"><?php echo $t['seat_code']; ?></span>
                                            </div>
                                            <div class="text-end">
                                                <div class="text-secondary small">Kode Tiket</div>
                                                <code class="text-warning fw-bold fs-6"><?php echo $t['ticketCode']; ?></code>
                                                <div class="mt-1">
                                                    <span class="badge <?php echo ($t['ticketStatus'] === 'Unused') ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo $t['ticketStatus']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="bukti_tiket.php?trx_id=<?php echo $t['transactionID']; ?>" class="btn btn-outline-light btn-sm">
                                                    <i class="bi bi-receipt me-1"></i> Struk
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <!-- Tampilan jika belum ada tiket -->
                    <div class="text-center py-5 text-secondary card bg-dark bg-opacity-25 border border-secondary border-opacity-25 rounded-4">
                        <i class="bi bi-ticket-detailed display-3 d-block mb-3 text-secondary"></i>
                        <h5 class="text-light">Belum Ada Tiket yang Dibeli</h5>
                        <p class="small text-secondary mb-3">Anda belum memiliki riwayat pemesanan tiket bioskop.</p>
                        <div>
                            <a href="index.php" class="btn btn-danger btn-sm px-4">Pesan Tiket Sekarang</a>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>