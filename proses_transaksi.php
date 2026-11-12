<?php
include 'koneksi.php';

// Pastikan data dikirim melalui method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header("Location: index.php");
    exit;
}

// 1. Ambil semua data yang dikirim dari form ringkasan
$showtimeID     = $_POST['showtime_id']; // ID jadwal tayang
$selected_seats = $_POST['selected_seats']; // Berupa array ID kursi
$total_price    = $_POST['total_price']; // Total harga tiket
$customer_name  = mysqli_real_escape_string($koneksi, $_POST['customer_name']); 
$customer_email = mysqli_real_escape_string($koneksi, $_POST['customer_email']);
$customer_phone = mysqli_real_escape_string($koneksi, $_POST['customer_phone']);
$payment_method = $_POST['payment_method'];  

// --- TAHAP 1: Kelola Data Pelanggan (customers) ---
// Cek apakah email pelanggan sudah pernah ada
$checkCustomer = mysqli_query($koneksi, "SELECT customerID FROM customers WHERE email = '$customer_email'"); //mengeceknya dengan query SQL di tabel customers. $koneksi memberitau mysqli_query() database mana yang digunakan.

if (mysqli_num_rows($checkCustomer) > 0) { 
    // Jika pelanggan lama, gunakan ID yang sudah ada
    $cust = mysqli_fetch_assoc($checkCustomer); 
    $customerID = $cust['customerID']; 
} else {
    // Jika pelanggan baru, tambahkan ke tabel customers
    $queryNewCust = "INSERT INTO customers (name, email, password, phone)
                     VALUES ('$customer_name', '$customer_email', '123456', '$customer_phone')";
    mysqli_query($koneksi, $queryNewCust); 
    // Ambil ID pelanggan baru yang baru saja dibuat
    $customerID = mysqli_insert_id($koneksi); 
}

// --- TAHAP 2: Simpan Data Pembayaran (transactions) ---
$queryTransaction = "INSERT INTO transactions (customerID, total, payment_method, transaction_date)
                     VALUES ('$customerID', '$total_price', '$payment_method', NOW())";
mysqli_query($koneksi, $queryTransaction); 

// Ambil ID transaksi yang baru saja dibuat
$transactionID = mysqli_insert_id($koneksi); 

// --- TAHAP 3: Cetak Tiket untuk Setiap Kursi (ticket) ---
// Hitung harga per tiket
$harga_satuan = $total_price / count($selected_seats); 


foreach ($selected_seats as $seatID) { 
    // Membuat kode tiket acak yang unik, misal: TKT72819
    $ticketCode = 'TKT' . rand(10000, 99999);
    
    $queryTicket = "INSERT INTO ticket (transactionID, showtimeID, seatID, price, ticketCode, ticketStatus)
                    VALUES ('$transactionID', '$showtimeID', '$seatID', '$harga_satuan', '$ticketCode', 'Unused')";
    mysqli_query($koneksi, $queryTicket); 
}

// --- TAHAP 4: Arahkan ke halaman bukti pembayaran/tiket ---  
header("Location: bukti_tiket.php?trx_id=" . $transactionID);
exit;
?>