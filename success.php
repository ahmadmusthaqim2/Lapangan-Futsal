<?php
session_start();
include 'conf/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_penyewa   = $_SESSION['id_penyewa'];
    $id_lapangan  = $_POST['id_lapangan'];
    $tanggal      = $_POST['tanggal'];
    $jam_mulai    = $_POST['jam_mulai'];
    $jam_selesai  = $_POST['jam_selesai'];
    $total_jam    = $_POST['durasi'];
    $harga_total  = $_POST['harga_total'];
    $order_id     = $_POST['order_id'];

    // Simpan ke tabel booking
    $query = "INSERT INTO booking (id_penyewa, id_lapangan, tanggal, jam_mulai, jam_selesai, durasi, total_harga, status, order_id)
              VALUES ('$id_penyewa', '$id_lapangan', '$tanggal', '$jam_mulai', '$jam_selesai', '$total_jam', '$harga_total', 'lunas', '$order_id')";
    
    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        http_response_code(500);
        echo "error: " . mysqli_error($conn);
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transaksi Berhasil</title>
</head>
<body>
    <h1>Terima kasih, pembayaran berhasil!</h1>
    <p>Data booking kamu sedang diproses...</p>

    <script>
        const lapanganName   = localStorage.getItem("lapanganName");
        const idLapangan     = localStorage.getItem("lapanganID"); // pastikan ini disimpan di localStorage juga
        const selectedDate   = localStorage.getItem("selectedDate");
        const selectedTimes  = JSON.parse(localStorage.getItem("selectedTimes") || "[]");
        const totalDuration  = localStorage.getItem("totalDuration");
        const totalPrice     = localStorage.getItem("totalPrice");
        const orderId        = localStorage.getItem("order_id"); // pastikan order_id disimpan

        const jam_mulai = selectedTimes[0];
        const jam_selesai = selectedTimes[selectedTimes.length - 1];

        fetch('success.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                id_lapangan: idLapangan,
                tanggal: selectedDate,
                jam_mulai: jam_mulai,
                jam_selesai: jam_selesai,
                durasi: totalDuration,
                harga_total: totalPrice,
                order_id: orderId
            })
        }).then(res => {
            if (res.ok) {
                // bersihin localStorage biar gak dobel
                localStorage.clear();
                // redirect ke halaman riwayat
                window.location.href = "Dashboard.php";
            } else {
                alert("Gagal menyimpan booking, coba hubungi admin.");
            }
        });
    </script>
</body>
</html>
