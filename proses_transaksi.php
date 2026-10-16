<?php
include 'koneksi.php';//manggil koneksi.php untuk koneksi ke database

// Pastikan data dikirim melalui method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { //memeriksa apakah halaman dibuka dengan setelah form dikirm dgn metode POST
    header("Location: index.php");//mengarahkan broswer ke halaman index.php jika tidak melalui POST
    exit;
}

// 1. Ambil semua data yang dikirim dari form ringkasan
$showtimeID     = $_POST['showtime_id']; // ID jadwal tayang
$selected_seats = $_POST['selected_seats']; // Berupa array ID kursi
$total_price    = $_POST['total_price']; // Total harga tiket
$customer_name  = mysqli_real_escape_string($koneksi, $_POST['customer_name']); //mengamankan nama pelanggan sebelum dimasukkan ke query SQL
$customer_email = mysqli_real_escape_string($koneksi, $_POST['customer_email']); //mengamankan email pelanggan sebelum dimasukkan ke query SQL
$customer_phone = mysqli_real_escape_string($koneksi, $_POST['customer_phone']);//mengamankan nomor telepon pelanggan sebelum dimasukkan ke query SQL
$payment_method = $_POST['payment_method']; // Metode pembayaran 

// --- TAHAP 1: Kelola Data Pelanggan (customers) ---
// Cek apakah email pelanggan sudah pernah ada
$checkCustomer = mysqli_query($koneksi, "SELECT customerID FROM customers WHERE email = '$customer_email'"); //mengeceknya dengan query SQL di tabel customers. $koneksi memberitau mysqli_query() database mana yang digunakan.

if (mysqli_num_rows($checkCustomer) > 0) { 
    // Jika pelanggan lama, gunakan ID yang sudah ada
    $cust = mysqli_fetch_assoc($checkCustomer); //mengambil data pelanggan dari hasil query dan menyimpannya dalam array asosiatif $cust
    $customerID = $cust['customerID']; //mengambil nilai customerID dari array asosiatif $cust dan menyimpannya dalam variabel $customerID
} else {
    // Jika pelanggan baru, tambahkan ke tabel customers
    $queryNewCust = "INSERT INTO customers (name, email, password, phone) #menyimpan data pelanggan baru ke tabel customers dengan query SQL. $koneksi memberitau mysqli_query() database mana yang digunakan.
                     VALUES ('$customer_name', '$customer_email', '123456', '$customer_phone')";
    mysqli_query($koneksi, $queryNewCust); //menjalankan query dengan koneksi ke database, kemudian menyimpan hasilnya di variabel $queryNewCust
    // Ambil ID pelanggan baru yang baru saja dibuat
    $customerID = mysqli_insert_id($koneksi); //mengambil ID pelanggan baru yang baru saja dibuat dan menyimpannya dalam variabel $customerID
}

// --- TAHAP 2: Simpan Data Pembayaran (transactions) ---
$queryTransaction = "INSERT INTO transactions (customerID, total, payment_method, transaction_date) #menyimpan data transaksi ke tabel transactions dengan query SQL. $koneksi memberitau mysqli_query() database mana yang digunakan.
                     VALUES ('$customerID', '$total_price', '$payment_method', NOW())";
mysqli_query($koneksi, $queryTransaction); //menjalankan query dengan koneksi ke database, kemudian menyimpan hasilnya di variabel $queryTransaction

// Ambil ID transaksi yang baru saja dibuat
$transactionID = mysqli_insert_id($koneksi); //mengambil ID transaksi yang baru saja dibuat dan menyimpannya dalam variabel $transactionID

// --- TAHAP 3: Cetak Tiket untuk Setiap Kursi (ticket) ---
// Hitung harga per tiket
$harga_satuan = $total_price / count($selected_seats); //menghitung harga per tiket dengan membagi total harga dengan jumlah kursi yang dipilih, kemudian menyimpannya dalam variabel $harga_satuan

//forreach untuk menyimpan setiap kursi yang dipilih ke tabel ticket, melakukan hal yang sama untuk setiap kursi yang dipilih oleh pengguna
foreach ($selected_seats as $seatID) { //mengulang setiap ID kursi yang dipilih oleh pengguna
    // Membuat kode tiket acak yang unik, misal: TKT72819
    $ticketCode = 'TKT' . rand(10000, 99999);//membuat kode tiket acak yang unik dengan menambahkan 'TKT' di depan angka acak antara 10000 dan 99999, kemudian menyimpannya dalam variabel $ticketCode
    
    $queryTicket = "INSERT INTO ticket (transactionID, showtimeID, seatID, price, ticketCode, ticketStatus) #menyimpan data tiket ke tabel ticket dengan query SQL. $koneksi memberitau mysqli_query() database mana yang digunakan.
                    VALUES ('$transactionID', '$showtimeID', '$seatID', '$harga_satuan', '$ticketCode', 'Unused')";
    mysqli_query($koneksi, $queryTicket); //menjalankan query dengan koneksi ke database, kemudian menyimpan hasilnya di variabel $queryTicket
}

// --- TAHAP 4: Arahkan ke halaman bukti pembayaran/tiket ---  //trx_id = transaction ID, digunakan untuk menampilkan bukti tiket yang baru saja dibuat
header("Location: bukti_tiket.php?trx_id=" . $transactionID); //mengarahkan broswer ke halaman bukti_tiket.php dengan menambahkan parameter trx_id di URL yang berisi ID transaksi yang baru saja dibuat
exit;
?>