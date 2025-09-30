<?php
date_default_timezone_set('Asia/Jakarta');
session_start();
include 'conf/koneksi.php';

// Handle AJAX request for booked slots
if (!empty($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    
    $lapId = (int)($_GET['lapangan_id'] ?? 0);
    $date  = $_GET['date'] ?? date('Y-m-d');
    $currentUserId = $_SESSION['id_penyewa'] ?? 0; // Ambil ID user saat ini
    
    try {
        // Query dengan logika: hanya disable jika sudah dibayar ATAU bukan milik user saat ini
        $stmt = $conn->prepare(
            "SELECT DISTINCT 
                DATE_FORMAT(jam_mulai, '%H:%i') AS jam_mulai,
                id_penyewa,
                status
             FROM booking 
             WHERE tanggal = ? AND id_lapangan = ? 
                AND (
                    status IN ('confirmed', 'paid', 'settlement, 'capture') 
                    OR (status = 'pending' AND id_penyewa != ?)
                )
             ORDER BY jam_mulai"
        );
        $stmt->bind_param('sii', $date, $lapId, $currentUserId);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $booked = [];
        $userPendingSlots = []; // Slot pending milik user saat ini
        
        while ($row = $res->fetch_assoc()) {
            // Normalisasi format jam (pastikan HH:MM)
            $jam = $row['jam_mulai'];
            if (strlen($jam) === 4) $jam = '0' . $jam; // Tambah leading zero jika perlu
            
            if ($row['status'] === 'pending' && $row['id_penyewa'] == $currentUserId) {
                $userPendingSlots[] = $jam;
            } else {
                $booked[] = $jam;
            }
        }
        
        // Remove duplicates
        $booked = array_unique($booked);
        $booked = array_values($booked);
        $userPendingSlots = array_unique($userPendingSlots);
        $userPendingSlots = array_values($userPendingSlots);
        
        echo json_encode([
            'success' => true,
            'bookedSlots' => $booked,
            'userPendingSlots' => $userPendingSlots,
            'timestamp' => time() // Untuk debugging
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'bookedSlots' => [],
            'userPendingSlots' => []
        ]);
    }
    
    $stmt->close();
    exit;
}

include 'include/header.php';

function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Ambil daftar lapangan
$lapanganList = [];
$lapanganQuery = "SELECT * FROM lap WHERE deleted_at IS NULL ORDER BY id_lapangan ASC";
$resultLapangan = $conn->query($lapanganQuery);
if ($resultLapangan) {
    while ($row = $resultLapangan->fetch_assoc()) {
        $lapanganList[] = $row;
    }
}

// Data penyewa
$id_penyewa = $_SESSION['id_penyewa'];
$query = mysqli_query($conn, "SELECT * FROM penyewa WHERE id_penyewa = '$id_penyewa'");
$penyewa = mysqli_fetch_assoc($query);
$nama_penyewa = $penyewa['nama_penyewa'];
$email_penyewa = $penyewa['email_penyewa'];
$tlp_penyewa = $penyewa['no_telp'];

// Ambil lapangan yang dipilih
$selectedLapangan = [];
if (isset($_GET['lapangan_id']) && ctype_digit($_GET['lapangan_id'])) {
    $lapangan_id = (int) $_GET['lapangan_id'];
    $stmt = $conn->prepare("SELECT * FROM lap WHERE id_lapangan = ?");
    $stmt->bind_param("i", $lapangan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $selectedLapangan = $result->fetch_assoc();
    $stmt->close();
}

// Fallback ke lapangan pertama
if (empty($selectedLapangan) && !empty($lapanganList)) {
    $selectedLapangan = $lapanganList[0];
    $lapangan_id = $selectedLapangan['id_lapangan'];
} elseif (empty($selectedLapangan)) {
    $selectedLapangan = ['id_lapangan' => 0, 'nama_lapangan' => 'Lapangan Tidak Ditemukan', 'harga_jam' => 0];
    $lapangan_id = 0;
}

// Ambil tanggal
$selectedDate = isset($_GET['date']) ? $_GET['date'] : (isset($_COOKIE['selectedDate']) ? $_COOKIE['selectedDate'] : date('Y-m-d'));

// Ambil jam yang sudah dibooking dan pending milik user
$bookedSlots = [];
$userPendingSlots = [];

if (!empty($selectedLapangan['id_lapangan'])) {
    // Query untuk slot yang benar-benar tidak tersedia (sudah dibayar atau pending milik orang lain)
    $stmt = $conn->prepare(
        "SELECT DISTINCT 
            DATE_FORMAT(jam_mulai, '%H:%i') AS jam_mulai,
            id_penyewa,
            status
         FROM booking 
         WHERE tanggal = ? AND id_lapangan = ? 
            AND (
                status IN ('confirmed', 'paid', 'settlement', 'capture') 
                OR (status = 'pending' AND id_penyewa != ?)
            )
         ORDER BY jam_mulai"
    );
    $stmt->bind_param("sii", $selectedDate, $selectedLapangan['id_lapangan'], $id_penyewa);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $jam = $row['jam_mulai'];
        // Normalisasi format jam (pastikan HH:MM)
        if (strlen($jam) === 4) $jam = '0' . $jam;
        
        if ($row['status'] === 'pending' && $row['id_penyewa'] == $id_penyewa) {
            $userPendingSlots[] = $jam;
        } else {
            $bookedSlots[] = $jam;
        }
    }
    $stmt->close();
    
    // Query terpisah untuk slot pending milik user saat ini
    $stmt2 = $conn->prepare(
        "SELECT DISTINCT DATE_FORMAT(jam_mulai, '%H:%i') AS jam_mulai 
         FROM booking 
         WHERE tanggal = ? AND id_lapangan = ? AND id_penyewa = ? AND status = 'pending'
         ORDER BY jam_mulai"
    );
    $stmt2->bind_param("sii", $selectedDate, $selectedLapangan['id_lapangan'], $id_penyewa);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    while ($row = $result2->fetch_assoc()) {
        $jam = $row['jam_mulai'];
        if (strlen($jam) === 4) $jam = '0' . $jam;
        $userPendingSlots[] = $jam;
    }
    $stmt2->close();
    
    $bookedSlots = array_unique($bookedSlots);
    $bookedSlots = array_values($bookedSlots);
    $userPendingSlots = array_unique($userPendingSlots);
    $userPendingSlots = array_values($userPendingSlots);
}

// Daftar jam yang tersedia dengan format konsisten HH:MM
$availableSlots = [
    '08:00' => '08:00 - 09:00',
    '09:00' => '09:00 - 10:00',
    '10:00' => '10:00 - 11:00',
    '11:00' => '11:00 - 12:00',
    '12:00' => '12:00 - 13:00',
    '13:00' => '13:00 - 14:00',
    '14:00' => '14:00 - 15:00',
    '15:00' => '15:00 - 16:00',
    '16:00' => '16:00 - 17:00',
    '17:00' => '17:00 - 18:00',
    '18:00' => '18:00 - 19:00',
    '19:00' => '19:00 - 20:00',
    '20:00' => '20:00 - 21:00',
    '21:00' => '21:00 - 22:00',
    '22:00' => '22:00 - 23:00',
    '23:00' => '23:00 - 00:00',
];
?>

<script>
// Clear previous selections
localStorage.removeItem("selectedDate");
localStorage.removeItem("selectedTimes");

const urlParams = new URLSearchParams(window.location.search);
const lapanganID = parseInt(urlParams.get('lapangan_id') || '<?= $lapangan_id ?>');
const selectedDate = urlParams.get('date') || '<?= $selectedDate ?>';
const currentUserId = <?= $id_penyewa ?>;

// Fungsi normalisasi format jam (HH:MM)
function normalizeTime(time) {
    if (!time) return time;
    const parts = time.split(':');
    if (parts.length === 2) {
        const hours = parts[0].padStart(2, '0');
        const minutes = parts[1].padStart(2, '0');
        return `${hours}:${minutes}`;
    }
    return time;
}

// Initialize booked slots from PHP
let bookedSlots = <?= json_encode($bookedSlots) ?>;
let userPendingSlots = <?= json_encode($userPendingSlots) ?>;

// Normalisasi semua slot
bookedSlots = bookedSlots.map(slot => normalizeTime(slot));
bookedSlots = [...new Set(bookedSlots)]; // Remove duplicates

userPendingSlots = userPendingSlots.map(slot => normalizeTime(slot));
userPendingSlots = [...new Set(userPendingSlots)]; // Remove duplicates

function isSlotBooked(jamMulai) {
    const normalizedJam = normalizeTime(jamMulai);
    return bookedSlots.includes(normalizedJam);
}

function isUserPendingSlot(jamMulai) {
    const normalizedJam = normalizeTime(jamMulai);
    return userPendingSlots.includes(normalizedJam);
}

function isSlotExpired(jamMulai) {
    const now = new Date();
    const today = now.toISOString().split('T')[0];
    
    if (selectedDate !== today) {
        return false;
    }
    
    const [hours, minutes] = normalizeTime(jamMulai).split(':').map(Number);
    const slotTime = new Date();
    slotTime.setHours(hours, minutes, 0, 0);
    
    return now >= slotTime;
}

function updateSlotStatus(slot) {
    const jamMulai = slot.dataset.jam;
    const statusSpan = slot.querySelector('.slot-status');
    
    // Reset classes first
    slot.className = 'time-slot p-3 border rounded';
    
    if (isSlotBooked(jamMulai)) {
        // Slot sudah dibooking oleh orang lain atau sudah dibayar
        slot.className += ' border-red-400 bg-red-100 cursor-not-allowed';
        slot.style.pointerEvents = 'none';
        if (statusSpan) {
            statusSpan.textContent = 'Dibooking';
            statusSpan.className = 'text-red-600 text-sm slot-status';
        }
        return 'booked';
    } else if (isUserPendingSlot(jamMulai)) {
        // Slot pending milik user saat ini - masih bisa dipilih ulang
        slot.className += ' border-yellow-400 bg-yellow-100 cursor-pointer hover:shadow-md';
        slot.style.pointerEvents = 'auto';
        if (statusSpan) {
            statusSpan.textContent = 'Menunggu Bayar';
            statusSpan.className = 'text-yellow-600 text-sm slot-status';
        }
        return 'user-pending';
    } else if (isSlotExpired(jamMulai)) {
        slot.className += ' border-gray-400 bg-gray-200 cursor-not-allowed';
        slot.style.pointerEvents = 'none';
        if (statusSpan) {
            statusSpan.textContent = 'Kadaluarsa';
            statusSpan.className = 'text-gray-600 text-sm slot-status';
        }
        return 'expired';
    } else {
        slot.className += ' border-green-400 bg-green-100 cursor-pointer hover:shadow-md';
        slot.style.pointerEvents = 'auto';
        if (statusSpan) {
            statusSpan.textContent = 'Tersedia';
            statusSpan.className = 'text-green-600 text-sm slot-status';
        }
        return 'available';
    }
}

function refreshBookedSlots() {
    if (!lapanganID) return;
    
    const ajaxUrl = `jam.php?ajax=1&lapangan_id=${lapanganID}&date=${selectedDate}&_t=${Date.now()}`;
    
    fetch(ajaxUrl, {
        method: 'GET',
        headers: {
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Refresh data received:', data); // Debug log
        
        if (data.success && data.bookedSlots) {
            const newBookedSlots = data.bookedSlots.map(slot => normalizeTime(slot));
            const uniqueNewSlots = [...new Set(newBookedSlots)];
            
            const newUserPendingSlots = (data.userPendingSlots || []).map(slot => normalizeTime(slot));
            const uniqueUserPendingSlots = [...new Set(newUserPendingSlots)];
            
            // Selalu update jika ada perubahan
            const currentSorted = bookedSlots.sort();
            const newSorted = uniqueNewSlots.sort();
            const currentUserPendingSorted = userPendingSlots.sort();
            const newUserPendingSorted = uniqueUserPendingSlots.sort();
            
            if (JSON.stringify(currentSorted) !== JSON.stringify(newSorted) || 
                JSON.stringify(currentUserPendingSorted) !== JSON.stringify(newUserPendingSorted)) {
                
                console.log('Updating slots:', {
                    oldBooked: bookedSlots,
                    newBooked: uniqueNewSlots,
                    oldUserPending: userPendingSlots,
                    newUserPending: uniqueUserPendingSlots
                });
                
                bookedSlots = uniqueNewSlots;
                userPendingSlots = uniqueUserPendingSlots;
                
                // Update semua slot yang tidak sedang dipilih user
                const timeSlots = document.querySelectorAll('.time-slot');
                timeSlots.forEach(slot => {
                    // Jangan update slot yang sudah dipilih user
                    if (!slot.classList.contains('text-primary')) {
                        const prevStatus = slot.style.pointerEvents;
                        updateSlotStatus(slot);
                        
                        // Log perubahan untuk debugging
                        if (prevStatus !== slot.style.pointerEvents) {
                            console.log(`Slot ${slot.dataset.jam} status changed`);
                        }
                    }
                });
                
                // Update display jika ada
                const statusInfo = document.getElementById('booking-status-info');
                if (statusInfo) {
                    statusInfo.textContent = `Terakhir diperbarui: ${new Date().toLocaleTimeString()}`;
                }
            }
        }
    })
    .catch(error => {
        console.error('Error refreshing booked slots:', error);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    // Format tanggal untuk display
    const dayNames = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const dateObj = new Date(selectedDate + 'T00:00:00');
    const formatted = `${dayNames[dateObj.getDay()]}, ${dateObj.getDate()} ${monthNames[dateObj.getMonth()]} ${dateObj.getFullYear()}`;
    
    document.getElementById("selected-date").textContent = formatted;
    document.getElementById("selected-date-summary").textContent = formatted;

    const selectedTimes = [];
    const timeSlots = document.querySelectorAll('.time-slot');
    const nextToPaymentButton = document.getElementById("next-to-payment");

    // Initialize all slot statuses
    timeSlots.forEach(slot => updateSlotStatus(slot));

    // Refresh booked slots
    setTimeout(refreshBookedSlots, 500);
    const refreshInterval = setInterval(refreshBookedSlots, 3000);
    
    // Cleanup interval saat halaman di-unload
    window.addEventListener('beforeunload', function() {
        clearInterval(refreshInterval);
    });

    // Time slot selection logic
    timeSlots.forEach(slot => {
        slot.addEventListener('click', function () {
            if (this.style.pointerEvents === 'none' || this.classList.contains('cursor-not-allowed')) {
                return;
            }
            
            const jamMulai = this.dataset.jam;
            const jamTeks = this.querySelector('.slot-time').textContent;
            const harga = parseInt(this.dataset.harga);
            
            if (selectedTimes.includes(jamMulai)) return;
            
            // Double check slot status - hanya blok jika benar-benar tidak tersedia
            if (isSlotBooked(jamMulai)) {
                updateSlotStatus(this);
                return;
            }
            
            // Jika slot pending milik user, berikan peringatan tapi tetap izinkan
            if (isUserPendingSlot(jamMulai)) {
                const confirm = window.confirm('Jam ini sedang menunggu pembayaran Anda. Apakah ingin memilih ulang?');
                if (!confirm) return;
            }
            
            selectedTimes.push(jamMulai);
            
            const summary = document.getElementById('selected-time-summary');
            const emptyState = summary.querySelector('.empty-state');
            if (emptyState) emptyState.remove();
            
            summary.innerHTML += `
                <div class="flex items-center justify-between bg-primary/10 p-2 rounded mb-2" data-time="${jamMulai}">
                    <span class="font-medium">${jamTeks}</span>
                    <div class="flex items-center">
                        <span class="text-gray-600 mr-2">Rp ${harga.toLocaleString()}</span>
                        <button class="text-red-500 hover:text-red-700 remove-time-slot ml-2">Hapus</button>
                    </div>
                </div>
            `;
            
            this.classList.remove('bg-green-100', 'border-green-400', 'bg-yellow-100', 'border-yellow-400');
            this.classList.add('bg-primary/20', 'border-primary', 'text-primary', 'cursor-not-allowed');
            this.style.pointerEvents = 'none';
            
            const totalDuration = selectedTimes.length;
            const totalPrice = totalDuration * harga;
            
            document.getElementById('total-duration').textContent = `${totalDuration} jam`;
            document.getElementById('total-price').textContent = `Rp ${totalPrice.toLocaleString()}`;
            nextToPaymentButton.disabled = false;
        });
    });

    // Remove time slot functionality
    document.getElementById('selected-time-summary').addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-time-slot');
        if (!btn) return;
        
        const container = btn.closest('[data-time]');
        const jamMulai = container.dataset.time;
        const index = selectedTimes.indexOf(jamMulai);
        
        if (index > -1) selectedTimes.splice(index, 1);
        container.remove();

        const slot = [...timeSlots].find(s => s.dataset.jam === jamMulai);
        if (slot) updateSlotStatus(slot);

        const totalDuration = selectedTimes.length;
        const harga = parseInt(slot?.dataset.harga || 0);
        const totalPrice = totalDuration * harga;

        document.getElementById('total-duration').textContent = `${totalDuration} jam`;
        document.getElementById('total-price').textContent = `Rp ${totalPrice.toLocaleString()}`;
        nextToPaymentButton.disabled = selectedTimes.length === 0;
        
        if (selectedTimes.length === 0) {
            const summary = document.getElementById('selected-time-summary');
            summary.innerHTML = `
                <div class="empty-state text-center py-4 border border-dashed border-gray-300 rounded-lg">
                    <div class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded-full mx-auto mb-3">
                        <i class="ri-time-line text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-gray-500">Belum ada jam yang dipilih</p>
                    <p class="text-sm text-gray-400 mt-1">Pilih minimal 1 jam</p>
                </div>
            `;
        }
    });

    // Payment button functionality
    nextToPaymentButton.addEventListener('click', function () {
        if (selectedTimes.length === 0) {
            alert('Pilih minimal 1 jam terlebih dahulu');
            return;
        }
        
        localStorage.setItem("selectedTimes", JSON.stringify(selectedTimes));
        localStorage.setItem("selectedDate", selectedDate);
        localStorage.setItem("lapanganName", "<?= esc($selectedLapangan['nama_lapangan']) ?>");
        localStorage.setItem("lapanganPrice", <?= (int)$selectedLapangan['harga_jam'] ?>);
        localStorage.setItem("totalDuration", selectedTimes.length);
        localStorage.setItem("totalPrice", selectedTimes.length * <?= (int)$selectedLapangan['harga_jam'] ?>);

        fetch('MidTrans/paymentOrder.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                name_penyewa: '<?= $nama_penyewa ?>',
                email_penyewa: '<?= $email_penyewa ?>',
                tlp_penyewa: '<?= $tlp_penyewa ?>',
                total_price: selectedTimes.length * <?= (int)$selectedLapangan['harga_jam'] ?>,
                selected_times: JSON.stringify(selectedTimes),
                selected_date: selectedDate,
                lapangan_price: <?= (int)$selectedLapangan['harga_jam'] ?>,
                id_lapangan: lapanganID
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.snapToken) {
                window.location.href = `payment.php?snap_token=${data.snapToken}`;
            } else {
                alert(data.error || 'Token tidak diterima dari server');
            }
        })
        .catch(err => alert('Terjadi kesalahan saat memproses pembayaran.'));
    });
});
</script>

<body>
    <div id="app" class="min-h-screen">
        <div id="user-date-page" class="block min-h-screen">
            <main class="flex-grow container mx-auto px-4 py-8">
                <div class="flex items-center mb-6">
                    <a href="Dashboard.php" class="mr-3 p-2 hover:bg-gray-100 rounded-full">
                        <i class="ri-arrow-left-line text-gray-600"></i>
                    </a>
                    <h2 class="text-2xl font-semibold text-gray-800">Pilih Jam Booking</h2>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 flex items-center justify-center bg-primary/10 rounded-full mr-3">
                                <i class="ri-calendar-check-line text-primary"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tanggal Terpilih</p>
                                <p class="text-lg font-medium text-gray-800" id="selected-date">Memuat tanggal...</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Lapangan</p>
                            <p class="text-lg font-medium text-gray-800"><?= esc($selectedLapangan['nama_lapangan']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Keterangan</h3>
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-green-100 border border-green-400 rounded mr-2"></div>
                            <span class="text-sm text-gray-600">Tersedia</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-yellow-100 border border-yellow-400 rounded mr-2"></div>
                            <span class="text-sm text-gray-600">Menunggu Pembayaran Anda</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-red-100 border border-red-400 rounded mr-2"></div>
                            <span class="text-sm text-gray-600">Sudah Dibooking</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-gray-200 border border-gray-400 rounded mr-2"></div>
                            <span class="text-sm text-gray-600">Kadaluarsa</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-primary/20 border border-primary rounded mr-2"></div>
                            <span class="text-sm text-gray-600">Dipilih</span>
                        </div>
                    </div>
                </div>

                <!-- Slot Waktu -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Pilih Jam</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <?php foreach ($availableSlots as $jam => $label): 
                            $normalizedJam = $jam; // Format sudah HH:MM
                            $isBooked = in_array($normalizedJam, $bookedSlots);
                            $isUserPending = in_array($normalizedJam, $userPendingSlots);
                            $baseClass = 'time-slot p-3 border rounded relative';
                            
                            if ($isBooked) {
                                $slotClass = $baseClass . ' border-red-400 bg-red-100 cursor-not-allowed';
                                $statusText = 'Dibooking';
                                $pointerEvents = 'none';
                            } else if ($isUserPending) {
                                $slotClass = $baseClass . ' border-yellow-400 bg-yellow-100 cursor-pointer hover:shadow-md';
                                $statusText = 'Menunggu Bayar';
                                $pointerEvents = 'auto';
                            } else {
                                $slotClass = $baseClass . ' border-green-400 bg-green-100 cursor-pointer hover:shadow-md';
                                $statusText = 'Tersedia';
                                $pointerEvents = 'auto';
                            }
                        ?>
                            <div class="<?= $slotClass ?>"
                                 data-jam="<?= $normalizedJam ?>"
                                 data-harga="<?= $selectedLapangan['harga_jam'] ?>"
                                 style="pointer-events: <?= $pointerEvents ?>;">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium slot-time"><?= $label ?></span>
                                    <span class="<?= $isBooked ? 'text-red-600' : ($isUserPending ? 'text-yellow-600' : 'text-green-600') ?> text-sm slot-status"><?= $statusText ?></span>
                                </div>
                                <div class="text-gray-600 text-sm mt-1">Rp <?= number_format($selectedLapangan['harga_jam'], 0, ',', '.') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Ringkasan Booking -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">Ringkasan Booking</h3>
                    <div class="space-y-4 mb-6">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Lapangan</span>
                            <span class="font-medium"><?= esc($selectedLapangan['nama_lapangan']) ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Tanggal</span>
                            <span class="font-medium" id="selected-date-summary">Memuat tanggal...</span>
                        </div>
                        <div id="selected-slots">
                            <div class="text-gray-600 mb-2">Jam Terpilih</div>
                            <div id="selected-time-summary">
                                <div class="empty-state text-center py-4 border border-dashed border-gray-300 rounded-lg">
                                    <div class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded-full mx-auto mb-3">
                                        <i class="ri-time-line text-gray-400 text-xl"></i>
                                    </div>
                                    <p class="text-gray-500">Belum ada jam yang dipilih</p>
                                    <p class="text-sm text-gray-400 mt-1">Pilih minimal 1 jam</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-600">Total Durasi</span>
                            <span class="font-medium" id="total-duration">0 jam</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total Harga</span>
                            <span class="font-medium text-lg" id="total-price">Rp 0</span>
                        </div>
                    </div>
                    <button id="next-to-payment" class="block flex justify-center w-full bg-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-primary/90 transition-colors whitespace-nowrap disabled:bg-gray-300 disabled:cursor-not-allowed mt-4" disabled>
                        Lanjut ke Pembayaran
                    </button>
                </div>
            </main>
            <?php include 'include/footer.php'; ?>
        </div>
    </div>
</body>
</html>