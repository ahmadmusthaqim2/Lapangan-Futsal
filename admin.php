<?php
session_start();
include 'conf/koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php');
    exit();
}

// Fungsi untuk mengamankan input
function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk cek status Midtrans otomatis
function cekStatusMidtransOtomatis($conn) {
    require_once __DIR__ . '/Midtrans/midtrans-php-master/Midtrans.php';

    \Midtrans\Config::$serverKey = 'SB-Mid-server-iA6tHgdEGuSYImrY_qYalj-g';
    \Midtrans\Config::$isProduction = false;

    // ambil semua booking yang belum sukses/settlement
    $stmt = $conn->query("SELECT id_booking, order_id FROM booking WHERE status NOT IN ('settlement', 'capture', 'paid')");
    $updated_count = 0;
    
    while ($row = $stmt->fetch_assoc()) {
        try {
            $status = \Midtrans\Transaction::status($row['order_id']);
            $status_baru = $status->transaction_status;

            // Mapping status Midtrans ke status yang konsisten
            $final_status = $status_baru;
            switch($status_baru) {
                case 'capture':
                case 'settlement':
                    $final_status = 'paid'; // Status yang digunakan di jam.php
                    break;
                case 'pending':
                    $final_status = 'pending';
                    break;
                case 'deny':
                case 'cancel':
                case 'expire':
                    $final_status = 'cancelled';
                    break;
            }

            // update status di DB hanya jika berubah
            $check = $conn->prepare("SELECT status FROM booking WHERE id_booking = ?");
            $check->bind_param("i", $row['id_booking']);
            $check->execute();
            $current = $check->get_result()->fetch_assoc();
            $check->close();

            if ($current['status'] !== $final_status) {
                $update = $conn->prepare("UPDATE booking SET status = ? WHERE id_booking = ?");
                $update->bind_param("si", $final_status, $row['id_booking']);
                $update->execute();
                $update->close();
                $updated_count++;
            }
        } catch (Exception $e) {
            // Log error jika perlu
            error_log("Midtrans status check error for order " . $row['order_id'] . ": " . $e->getMessage());
        }
    }
    
    return $updated_count;
}

$lapanganToEdit = null;
// Proses CRUD lapangan dan booking jika ada POST
$errors = [];
$success = '';

$targetDir = 'img/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true); // bikin folder kalau belum ada
}

// Simpan filter untuk refresh
$filter_param = '';
if (isset($_GET['filter_lapangan']) && $_GET['filter_lapangan'] != 0) {
    $filter_param = 'filter_lapangan=' . (int)$_GET['filter_lapangan'];
}

// OTOMATIS CEK STATUS MIDTRANS SETIAP KALI HALAMAN DIMUAT
$updated_count = cekStatusMidtransOtomatis($conn);
if ($updated_count > 0) {
    $success = "✅ Status pembayaran diperbarui otomatis ($updated_count booking)";
}

// Proses manual cek status midtrans (untuk tombol manual jika diperlukan)
if (isset($_POST['cek_status_midtrans'])) {
    $manual_updated = cekStatusMidtransOtomatis($conn);
    $success = "🔄 Pengecekan manual selesai ($manual_updated booking diupdate)";
}

// Proses tambah lapangan
if (isset($_POST['add_lapangan'])) {
    $nama_lapangan = trim($_POST['nama_lapangan']);
    $harga_jam = intval($_POST['harga_jam']);
    $fasilitas = trim($_POST['fasilitas']);
    $gambarName = '';
    if (!empty($_FILES['gambar']['name'])) {
        $gambarName = uniqid() . '_' . basename($_FILES['gambar']['name']);
        move_uploaded_file($_FILES['gambar']['tmp_name'], $targetDir . $gambarName);
    }

    if (!$nama_lapangan || $harga_jam <= 0) {
        $errors[] = "Nama lapangan dan harga jam wajib diisi dengan benar.";
    } else {
        $stmt = $conn->prepare("INSERT INTO lap (nama_lapangan, harga_jam, fasilitas, gambar) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('siss', $nama_lapangan, $harga_jam, $fasilitas, $gambarName);
        if ($stmt->execute()) {
            $success = "Lapangan berhasil ditambahkan.";
        } else {
            $errors[] = "Gagal menambahkan lapangan.";
        }
        $stmt->close();
    }
}

// Proses hapus lapangan
if (isset($_GET['delete_lapangan'])) {
    $id_lapangan = intval($_GET['delete_lapangan']);
    $stmt = $conn->prepare("UPDATE lap SET deleted_at = NOW() WHERE id_lapangan = ?");
    $stmt->bind_param('i', $id_lapangan);
    if ($stmt->execute()) {
        $success = "Lapangan berhasil dihapus.";
    } else {
        $errors[] = "Gagal menghapus lapangan.";
    }
    $stmt->close();
}

// Ambil data untuk diedit jika ada edit_lapangan
if (isset($_GET['edit_lapangan'])) {
    $id = (int)$_GET['edit_lapangan'];
    $stmt = $conn->prepare("SELECT * FROM lap WHERE id_lapangan = ? AND deleted_at IS NULL");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows) {
        $lapanganToEdit = $result->fetch_assoc();
    }
    $stmt->close();
}

// Proses update lapangan
if (isset($_POST['edit_lapangan'])) {
    $id_lapangan   = (int)$_POST['id_lapangan'];
    $nama_lapangan = trim($_POST['nama_lapangan']);
    $harga_jam     = (int)$_POST['harga_jam'];
    $fasilitas     = trim($_POST['fasilitas']);

    if (!$id_lapangan || !$nama_lapangan || $harga_jam <= 0) {
        $errors[] = "Semua field wajib diisi dengan benar.";
    } else {
        /* --- ambil nama gambar lama --- */
        $stmt = $conn->prepare("SELECT gambar FROM lap WHERE id_lapangan=?");
        $stmt->bind_param('i', $id_lapangan);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $gambarName = $old['gambar'] ?? '';

        /* --- kalau ada file baru, upload & ganti nama --- */
        if (!empty($_FILES['gambar']['name'])) {
            if ($gambarName && file_exists($targetDir.$gambarName)) {
                unlink($targetDir.$gambarName);        // hapus lama
            }
            $gambarName = uniqid().'_'.basename($_FILES['gambar']['name']);
            move_uploaded_file($_FILES['gambar']['tmp_name'], $targetDir.$gambarName);
        }

        /* --- update semua kolom sekaligus --- */
        $stmt = $conn->prepare(
            "UPDATE lap 
             SET nama_lapangan=?, harga_jam=?, fasilitas=?, gambar=? 
             WHERE id_lapangan=?"
        );
        $stmt->bind_param('sissi', $nama_lapangan, $harga_jam, $fasilitas, $gambarName, $id_lapangan);

        if ($stmt->execute()) {
            $success = "Lapangan berhasil diperbarui.";
        } else {
            $errors[] = "Gagal memperbarui lapangan.";
        }
        $stmt->close();
    }
}

// Proses hapus booking
// if (isset($_GET['delete_booking'])) {
//     $id_booking = intval($_GET['delete_booking']);
//     $stmt = $conn->prepare("DELETE FROM booking WHERE id_booking = ?");
//     $stmt->bind_param('i', $id_booking);
//     if ($stmt->execute()) {
//         $success = "Booking berhasil dihapus.";
//     } else {
//         $errors[] = "Gagal menghapus booking.";
//     }
//     $stmt->close();
// }

// Ambil data lapangan untuk filter
$lapanganListFilter = [];
$lapanganQueryFilter = "SELECT id_lapangan, nama_lapangan FROM lap WHERE deleted_at IS NULL ORDER BY nama_lapangan ASC";
$resultLapanganFilter = $conn->query($lapanganQueryFilter);
if ($resultLapanganFilter) {
    while ($row = $resultLapanganFilter->fetch_assoc()) {
        $lapanganListFilter[] = $row;
    }
}

// Ambil data booking
$bookingList = [];
$bookingQuery = "
    SELECT b.id_booking, b.tanggal, b.jam_mulai, b.jam_selesai, b.status, 
           p.nama_penyewa, l.nama_lapangan, b.order_id
    FROM booking b
    JOIN penyewa p ON b.id_penyewa = p.id_penyewa
    JOIN lap l ON b.id_lapangan = l.id_lapangan
    WHERE b.status != 'deleted'
";

// Tambahkan filter lapangan jika ada
$filter_lapangan_id = isset($_GET['filter_lapangan']) ? intval($_GET['filter_lapangan']) : 0;
if ($filter_lapangan_id > 0) {
    $bookingQuery .= " AND b.id_lapangan = " . $filter_lapangan_id;
}

$bookingQuery .= " ORDER BY b.id_booking DESC, b.jam_mulai ASC LIMIT 10";

$bookingQueryAll = "
    SELECT b.id_booking, b.tanggal, b.jam_mulai, b.jam_selesai, b.status,
           p.nama_penyewa, l.nama_lapangan, b.order_id
    FROM booking b
    JOIN penyewa p ON b.id_penyewa = p.id_penyewa
    JOIN lap l ON b.id_lapangan = l.id_lapangan
    WHERE b.status != 'deleted'
";
if ($filter_lapangan_id > 0) {
    $bookingQueryAll .= " AND b.id_lapangan = " . $filter_lapangan_id;
}
$bookingQueryAll .= " ORDER BY b.id_booking DESC, b.jam_mulai ASC";

$bookingAll = [];
$resAll = $conn->query($bookingQueryAll);
while ($row = $resAll->fetch_assoc()) {
    $bookingAll[] = $row;
}

$result = $conn->query($bookingQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $bookingList[] = $row;
    }
} else {
    $errors[] = "Terjadi kesalahan saat mengambil data booking";
}

// Ambil data lapangan (untuk pengelolaan lapangan)
$lapanganList = [];
$lapanganQuery = "SELECT * FROM lap WHERE deleted_at IS NULL ORDER BY id_lapangan ASC";
$resultLapangan = $conn->query($lapanganQuery);
if ($resultLapangan) {
    while ($row = $resultLapangan->fetch_assoc()) {
        $lapanganList[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - Futsal Court (Auto Update)</title>
    <script src="https://cdn.tailwindcss.com/3.4.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />

    <!-- Auto refresh setiap 60 detik - akan otomatis cek Midtrans -->
    <meta http-equiv="refresh" content="60;url=admin.php?<?= $filter_param ?>">
    
    <style>
    .booked-slot {
        cursor: not-allowed !important;
        pointer-events: none !important;
        position: relative;
    }
    
    .booked-slot::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: repeating-linear-gradient(
            45deg,
            rgba(0,0,0,0.1),
            rgba(0,0,0,0.1) 5px,
            transparent 5px,
            transparent 10px
        );
        z-index: 1;
    }

    .status-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .status-paid { background-color: #10b981; }
    .status-pending { background-color: #f59e0b; }
    .status-cancelled { background-color: #ef4444; }
    .status-settlement { background-color: #10b981; }
    .status-capture { background-color: #10b981; }

    .auto-update-badge {
        background: linear-gradient(45deg, #10b981, #059669);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    </style>
</head>

<body class="bg-gray-50 min-h-screen font-inter">

    <header class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-semibold font-pacifico">Admin Futsal Court</h1>
            <div class="flex items-center gap-4">
                <div class="auto-update-badge text-xs px-3 py-1 rounded-full text-white font-medium">
                    <i class="ri-refresh-line"></i> Auto-Update Aktif
                </div>
                <a href="logout.php" class="hover:underline flex items-center gap-1">
                    <i class="ri-logout-box-r-line"></i> Keluar
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto py-8 px-4">
        <?php if ($success): ?>
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md border-l-4 border-green-500">
            <div class="flex items-center">
                <i class="ri-check-circle-fill text-green-600 mr-2"></i>
                <?= esc($success) ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($errors): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md border-l-4 border-red-500">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $error): ?>
                <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Info Panel -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center text-blue-800">
                <i class="ri-information-line text-blue-600 mr-2"></i>
                <span class="font-medium">Status Pembayaran Midtrans akan dicek otomatis setiap kali halaman dimuat/refresh (60 detik sekali)</span>
            </div>
        </div>

        <section class="mb-12">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Daftar Booking</h2>

            <!-- Form Filter Lapangan -->
            <form method="get" action="" class="mb-4 p-4 bg-white rounded-lg shadow-md flex items-center space-x-4">
                <label for="filter_lapangan" class="font-medium text-gray-700">Filter Lapangan:</label>
                <select id="filter_lapangan" name="filter_lapangan" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="0">Semua Lapangan</option>
                    <?php foreach ($lapanganListFilter as $lapangan): ?>
                        <option value="<?= esc($lapangan['id_lapangan']) ?>"
                            <?= ($filter_lapangan_id == $lapangan['id_lapangan']) ? 'selected' : '' ?>>
                            <?= esc($lapangan['nama_lapangan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Tombol refresh manual -->
                <button type="button" onclick="location.reload()" 
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-2">
                    <i class="ri-refresh-line"></i> Refresh Sekarang
                </button>

                <button type="button" name="lempar"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-800"
                        onclick="location.href='https://dashboard.sandbox.midtrans.com/beta/transactions?start_created_at=2025-06-24T00%3A00%3A00%2B07%3A00&end_created_at=2025-07-25T23%3A59%3A59%2B07%3A00'">
                    <i class="ri-bar-chart-line"></i> Rincian Keuangan
                </button>
            </form>

            <?php if (count($bookingList) === 0): ?>
            <p class="text-gray-600">Belum ada data booking tersedia.</p>
            <?php else: ?>
            <div class="overflow-x-auto rounded-lg shadow-lg bg-white">
                <table id="bookingTable" class="min-w-full bg-white divide-y divide-gray-200">
                    <thead>
                        <tr class="text-left text-gray-700 bg-gray-100">
                            <th class="p-3">ID Booking</th>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Jam Mulai</th>
                            <th class="p-3">Jam Selesai</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Penyewa</th>
                            <th class="p-3">Lapangan</th>
                            <!-- <th class="p-3">Aksi</th> -->
                        </tr>
                    </thead>

                    <!-- 10 baris pertama -->
                    <tbody id="booking10">
                        <?php foreach ($bookingList as $booking): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-3"><?= esc($booking['id_booking']) ?></td>
                            <td class="p-3"><?= esc($booking['tanggal']) ?></td>
                            <td class="p-3"><?= esc($booking['jam_mulai']) ?></td>
                            <td class="p-3"><?= esc($booking['jam_selesai']) ?></td>
                            <td class="p-3">
                                <span class="status-indicator status-<?= esc($booking['status']) ?>"></span>
                                <span class="font-medium"><?= esc($booking['status']) ?></span>
                            </td>
                            <td class="p-3"><?= esc($booking['nama_penyewa']) ?></td>
                            <td class="p-3"><?= esc($booking['nama_lapangan']) ?></td>
                            <!-- <td class="p-3">
                                <a href="?delete_booking=<?= esc($booking['id_booking']) ?>"
                                onclick="return confirm('Yakin hapus booking ini?')"
                                class="text-red-600 hover:text-red-800" title="Hapus Booking">
                                    <i class="ri-delete-bin-line text-lg"></i>
                                </a>
                            </td> -->
                        </tr>
                        <?php endforeach; ?>
                    </tbody>

                    <!-- semua baris (sembunyiakan dulu) -->
                    <tbody id="bookingAll" class="hidden">
                        <?php foreach ($bookingAll as $booking): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-3"><?= esc($booking['id_booking']) ?></td>
                            <td class="p-3"><?= esc($booking['tanggal']) ?></td>
                            <td class="p-3"><?= esc($booking['jam_mulai']) ?></td>
                            <td class="p-3"><?= esc($booking['jam_selesai']) ?></td>
                            <td class="p-3">
                                <span class="status-indicator status-<?= esc($booking['status']) ?>"></span>
                                <span class="font-medium"><?= esc($booking['status']) ?></span>
                            </td>
                            <td class="p-3"><?= esc($booking['nama_penyewa']) ?></td>
                            <td class="p-3"><?= esc($booking['nama_lapangan']) ?></td>
                            <td class="p-3">
                                <a href="?delete_booking=<?= esc($booking['id_booking']) ?>"
                                onclick="return confirm('Yakin hapus booking ini?')"
                                class="text-red-600 hover:text-red-800" title="Hapus Booking">
                                    <i class="ri-delete-bin-line text-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="p-4 border-t">
                    <button id="btnLihat"
                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-800 transition-colors">
                        <i class="ri-eye-line mr-1"></i> Lihat Selengkapnya
                    </button>
                </div>

                <script>
                document.getElementById('btnLihat').addEventListener('click', function () {
                    document.getElementById('booking10').classList.add('hidden');
                    document.getElementById('bookingAll').classList.remove('hidden');
                    this.style.display = 'none';
                });
                </script>
            </div>
            <?php endif; ?>
        </section>

        <section>
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Pengelolaan Lapangan</h2>
            <form method="POST" enctype="multipart/form-data" class="mb-6 bg-white p-4 rounded-lg shadow-lg">
            <?php if ($lapanganToEdit): ?>
                <input type="hidden" name="id_lapangan" value="<?= esc($lapanganToEdit['id_lapangan']) ?>">
            <?php endif; ?>

            <div class="grid gap-4 grid-cols-1 md:grid-cols-3">
                <div>
                    <label for="nama_lapangan" class="block text-sm font-medium text-gray-700 mb-1">Nama Lapangan</label>
                    <input id="nama_lapangan" name="nama_lapangan" type="text" required
                        value="<?= $lapanganToEdit ? esc($lapanganToEdit['nama_lapangan']) : '' ?>"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label for="harga_jam" class="block text-sm font-medium text-gray-700 mb-1">Harga per Jam (Rp)</label>
                    <input id="harga_jam" name="harga_jam" type="number" min="0" required
                        value="<?= $lapanganToEdit ? esc($lapanganToEdit['harga_jam']) : '' ?>"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label for="fasilitas" class="block text-sm font-medium text-gray-700 mb-1">Fasilitas</label>
                    <input id="fasilitas" name="fasilitas" type="text"
                        value="<?= $lapanganToEdit ? esc($lapanganToEdit['fasilitas']) : '' ?>"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label for="gambar" class="block text-sm font-medium text-gray-700 mb-1">Upload Gambar</label>
                    <input id="gambar" name="gambar" type="file"
                        class="w-full border border-gray-300 rounded px-3 py-2 bg-white focus:ring-blue-500 focus:border-blue-500" accept="image/*" />
                </div>
            </div>

            <div class="flex justify-center items-center mt-6">
                <?php if ($lapanganToEdit): ?>
                    <button type="submit" name="edit_lapangan"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <i class="ri-save-line"></i> Simpan Perubahan
                    </button>
                <?php else: ?>
                    <button type="submit" name="add_lapangan"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <i class="ri-add-line"></i> Tambah Lapangan
                    </button>
                <?php endif; ?>
            </div>
        </form>

            <?php if (empty($lapanganList)): ?>
            <p class="text-gray-600">Belum ada data lapangan tersedia.</p>
            <?php else: ?>
            <div class="overflow-x-auto rounded-lg shadow-lg bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100 text-gray-700 font-medium">
                        <tr>
                            <th class="px-4 py-3 text-left">ID</th>
                            <th class="px-4 py-3 text-left">Nama Lapangan</th>
                            <th class="px-4 py-3 text-left">Harga per Jam (Rp)</th>
                            <th class="px-4 py-3 text-left">Fasilitas</th>
                            <th class="px-4 py-3 text-left">Gambar</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($lapanganList as $lapangan): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><?= esc($lapangan['id_lapangan']) ?></td>
                            <td class="px-4 py-3 font-medium"><?= esc($lapangan['nama_lapangan']) ?></td>
                            <td class="px-4 py-3 text-green-600 font-semibold">Rp <?= number_format($lapangan['harga_jam'], 0, ',', '.') ?></td>
                            <td class="px-4 py-3"><?= esc($lapangan['fasilitas']) ?></td>
                            <td class="px-4 py-3">
                                <?php if (!empty($lapangan['gambar'])): ?>
                                    <img src="img/<?= esc($lapangan['gambar']) ?>" alt="Gambar Lapangan" class="h-12 w-12 object-cover rounded border" />
                                <?php else: ?>
                                    <span class="text-gray-400 italic">Belum ada</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-3">
                                    <a href="?edit_lapangan=<?= esc($lapangan['id_lapangan']) ?>"
                                    class="text-yellow-600 hover:text-yellow-800 transition-colors"
                                    title="Edit Lapangan">
                                        <i class="ri-edit-box-line text-lg"></i>
                                    </a>
                                    <a href="?delete_lapangan=<?= esc($lapangan['id_lapangan']) ?>"
                                        onclick="return confirm('Yakin hapus lapangan ini?')" 
                                        class="text-red-600 hover:text-red-800 transition-colors"
                                        title="Hapus Lapangan">
                                        <i class="ri-delete-bin-line text-lg"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Footer info -->
    <footer class="bg-gray-800 text-white p-4 mt-12">
        <div class="container mx-auto text-center text-sm">
            <p>Auto-Update Midtrans aktif • Halaman akan refresh otomatis setiap 60 detik</p>
        </div>
    </footer>

</body>

</html>