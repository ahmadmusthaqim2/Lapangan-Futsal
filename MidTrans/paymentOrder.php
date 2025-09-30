<?php 
/*Install Midtrans PHP Library (https://github.com/Midtrans/midtrans-php)
composer require midtrans/midtrans-php
                              
Alternatively, if you are not using **Composer**, you can download midtrans-php library 
(https://github.com/Midtrans/midtrans-php/archive/master.zip), and then require 
the file manually.   */

/* ------------------ 1. Wajib paling atas ------------------ */
session_start();                        // kita butuh $_SESSION['id_penyewa']
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);                // untuk dev, log ke file saja
ini_set('display_errors', 0);

/*require_once dirname(__FILE__) . '/pathofproject/Midtrans.php'; */
require_once '../conf/koneksi.php';   // path relatif dari folder MidTrans
require_once dirname(__FILE__) . '/midtrans-php-master/Midtrans.php';
//SAMPLE REQUEST START HERE

use Midtrans\Config;
use Midtrans\Snap;

$id_penyewa    = $_SESSION['id_penyewa'] ?? 0;  
$id_lapangan   = (int) ($_POST['id_lapangan'] ?? 0);

// Terima data dari jam.php
$name_penyewa = $_POST['name_penyewa'] ?? '';
$email_penyewa = $_POST['email_penyewa'] ?? '';
$tlp_penyewa = $_POST['tlp_penyewa'] ?? '';
$total_price = (int) ($_POST['total_price'] ?? 0);
$selected_times = json_decode($_POST['selected_times'] ?? '[]', true);
$selected_date = $_POST['selected_date'] ?? '';
$lapangan_price  = (int) ($_POST['lapangan_price'] ?? 0);

// Validasi data
if (empty($name_penyewa) || $total_price <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak valid']);
    exit;
}

// Set konfigurasi Midtrans
Config::$serverKey = 'SB-Mid-server-iA6tHgdEGuSYImrY_qYalj-g'; // Ganti dengan server key Anda
Config::$isProduction = false; // Set ke true untuk production
Config::$isSanitized = true;
Config::$is3ds = true;

// Format data untuk Midtrans
$transaction_details = [
    'order_id' => 'FC-' . time() . '-' . rand(1000, 9999),
    'gross_amount' => $total_price
];

$customer_details = [
    'first_name' => $name_penyewa,
    'email' => $email_penyewa,
    'phone' => $tlp_penyewa
];

// Buat item details
$item_details = [];
foreach ($selected_times as $time) {
    $item_details[] = [
        'id' => 'time-' . substr($time, 0, 5),
        'price' => $lapangan_price,
        'quantity' => 1,
        'name' => "Jam Booking ($time)"
    ];
}

// Tambahkan item tambahan
$item_details[] = [
    'id' => 'lapangan-1',
    'price' => 0,
    'quantity' => 1,
    'name' => 'Lapangan Futsal Indoor'
];

$item_details[] = [
    'id' => 'tanggal',
    'price' => 0,
    'quantity' => 1,
    'name' => "Tanggal: $selected_date"
];

// Buat parameter transaksi
$params = [
    'transaction_details' => $transaction_details,
    'customer_details' => $customer_details,
    'item_details' => $item_details
];

try {
    // Dapatkan Snap Token
    $snapToken = Snap::getSnapToken($params);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

$order_id = $transaction_details['order_id'];
$tanggal = date('Y-m-d', strtotime($selected_date));

/* ------------------ 6. Simpan booking ke DB ---------------- */
$stmt = $conn->prepare(
  "INSERT INTO booking (id_penyewa, id_lapangan, tanggal, jam_mulai, jam_selesai, order_id, status) VALUES (?,?,?,?,?,?, 'pending')"
);
foreach ($selected_times as $slot) {
    $mulai  = $slot;
    $selesai = date('H:i', strtotime($slot . ' +1 hour'));
    $stmt->bind_param('iissss',$id_penyewa, $id_lapangan,$tanggal,$mulai,$selesai,$order_id);
    $stmt->execute();
}

echo json_encode(['snapToken' => $snapToken]);
?>