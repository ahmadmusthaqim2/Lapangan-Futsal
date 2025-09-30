<?php
require_once 'Midtrans/midtrans-php-master/Midtrans.php';
include 'conf/koneksi.php';

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-iA6tHgdEGuSYImrY_qYalj-g'; // Ganti ini bro
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Hanya jalankan jika diakses dari admin atau cron job
if(php_sapi_name() === 'cli' || strpos($_SERVER['REQUEST_URI'], 'admin.php') !== false) {
    // Ambil semua data booking dengan status pending dalam 24 jam terakhir
    $sql = "SELECT id_booking, order_id FROM booking 
            WHERE status = 'pending' 
            AND order_id <> '' 
            AND created_at >= NOW() - INTERVAL 1 DAY";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $orderId = $row['order_id'];
        $idBooking = $row['id_booking'];

        try {
            $status = \Midtrans\Transaction::status($orderId);
            $transactionStatus = $status->transaction_status;

            // Hanya update jika status berbeda
            if($transactionStatus !== 'pending') {
                $update = $conn->prepare("UPDATE booking SET status = ? WHERE id_booking = ?");
                
                if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                    $update->bind_param("si", 'lunas', $idBooking);
                } elseif ($transactionStatus === 'cancel' || $transactionStatus === 'expire' || $transactionStatus === 'deny') {
                    $update->bind_param("si", 'batal', $idBooking);
                }
                
                $update->execute();
                $update->close();
            }

        } catch (Exception $e) {
            error_log("Gagal ambil status dari Midtrans untuk order $orderId: " . $e->getMessage());
        }
    }
}
?>
