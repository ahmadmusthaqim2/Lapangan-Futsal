<?php
session_start();
include 'conf/koneksi.php';

$id_user = $_SESSION['id_penyewa'];

// Query untuk mengambil semua data booking
$query = "SELECT b.*, l.nama_lapangan FROM booking b 
          JOIN lap l ON b.id_lapangan = l.id_lapangan 
          WHERE b.id_penyewa = '$id_user' 
          ORDER BY b.id_booking DESC";
$result = mysqli_query($conn, $query);
$all_bookings = [];
while($row = mysqli_fetch_assoc($result)) {
    $all_bookings[] = $row;
}

$total_bookings = count($all_bookings);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Booking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Styling untuk tampilan mobile */
        @media (max-width: 768px) {
            .table-header { display: none; }
            .booking-card {
                border: 1px solid #e2e8f0;
                border-radius: 0.5rem;
                padding: 1rem;
                margin-bottom: 1rem;
                background-color: white;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .card-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 0.5rem;
            }
            .card-label {
                font-weight: 600;
                color: #4a5568;
            }
            .card-value {
                text-align: right;
                color: #2d3748;
            }
            .status-badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
            }
        }
        
        /* Scrollable area styling */
        .booking-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .booking-container {
                max-height: 400px;
            }
        }
        
        /* Custom scrollbar styling */
        .booking-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .booking-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .booking-container::-webkit-scrollbar-thumb {
            background: #c6f6d5;
            border-radius: 10px;
        }
        
        .booking-container::-webkit-scrollbar-thumb:hover {
            background: #9ae6b4;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="w-full max-w-5xl bg-white p-4 md:p-6 rounded-lg shadow-md">
        <h1 class="text-2xl md:text-3xl font-bold text-green-700 mb-4 md:mb-6 text-center">Riwayat Booking Lapangan</h1>
        
        <?php if($total_bookings > 0): ?>
            <div class="booking-container">
                <!-- Desktop Table -->
                <div class="hidden md:block">
                    <table class="min-w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-green-100 text-green-800 uppercase text-sm sticky top-0 z-10">
                                <th class="px-4 py-3 border">Tanggal</th>   
                                <th class="px-4 py-3 border">Lapangan</th>
                                <th class="px-4 py-3 border">Jam</th>
                                <th class="px-4 py-3 border">Status Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_bookings as $index => $row): ?>
                                <tr class="text-center text-gray-800 hover:bg-gray-100 <?= $index >= 10 ? 'opacity-80' : '' ?>">
                                    <td class="px-4 py-2 border"><?= $row['tanggal']; ?></td>
                                    <td class="px-4 py-2 border"><?= $row['nama_lapangan']; ?></td>
                                    <td class="px-4 py-2 border"><?= $row['jam_mulai']; ?> - <?= $row['jam_selesai']; ?></td>
                                    <td class="px-4 py-2 border">
                                        <?php
                                            $status = strtolower($row['status']);
                                            $badge = match($status) {
                                                'pending' => 'bg-yellow-100 text-yellow-700',
                                                'lunas' => 'bg-green-100 text-green-700',
                                                'batal' => 'bg-red-100 text-red-700',
                                                default => 'bg-gray-100 text-gray-700'
                                            };
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $badge ?>">
                                            <?= strtoupper($status); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Mobile Cards -->
                <div class="md:hidden">
                    <?php foreach($all_bookings as $index => $row): ?>
                        <div class="booking-card <?= $index >= 10 ? 'opacity-80' : '' ?>">
                            <div class="card-row">
                                <span class="card-label">Tanggal</span>
                                <span class="card-value"><?= $row['tanggal']; ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">Lapangan</span>
                                <span class="card-value"><?= $row['nama_lapangan']; ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">Jam</span>
                                <span class="card-value"><?= $row['jam_mulai']; ?> - <?= $row['jam_selesai']; ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">Status</span>
                                <span class="card-value">
                                    <?php
                                        $status = strtolower($row['status']);
                                        $badge = match($status) {
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'lunas' => 'bg-green-100 text-green-700',
                                            'batal' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        };
                                    ?>
                                    <span class="status-badge <?= $badge ?>">
                                        <?= strtoupper($status); ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="mt-3 text-center text-sm text-gray-500">
                Menampilkan <?= $total_bookings ?> riwayat booking
                <?php if($total_bookings > 10): ?>
                    <span class="block mt-1">Gulir ke bawah untuk melihat riwayat lebih lama</span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center text-gray-500 py-6 italic">
                Belum ada riwayat booking ditemukan.
            </div>
        <?php endif; ?>
        
        <div class="mt-6">
            <a href="dashboard.php" class="inline-block mb-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition flex items-center justify-center">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>