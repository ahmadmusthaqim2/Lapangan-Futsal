<?php
    session_start();
    include 'conf/koneksi.php';
    include 'include/header.php';

    $id_penyewa = $_SESSION['id_penyewa'];
    $query = mysqli_query($conn, "SELECT * FROM penyewa WHERE id_penyewa = '$id_penyewa'");
    $penyewa = mysqli_fetch_assoc($query);
    $nama_penyewa = $penyewa['nama_penyewa'];
    $email_penyewa = $penyewa['email_penyewa'];
    $tlp_penyewa = $penyewa['no_telp'];

    // Dapatkan token dari URL
    $snapToken = $_GET['snap_token'] ?? '';
    if (!$snapToken) {
    die("Token pembayaran tidak valid");
}
?>
<head>
    <!-- Midtrans -->
    <script type="text/javascript" src="https://app.stg.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-7s3_nEg1ByLHhmTd"></script>
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-7s3_nEg1ByLHhmTd"></script>
</head>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Ambil dan tampilkan tanggal
    const savedDate = localStorage.getItem("selectedDate");
    if (savedDate) {
        const date = new Date(savedDate);
        const dayNames = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        const dayName = dayNames[date.getDay()];
        const dateNum = date.getDate();
        const monthName = monthNames[date.getMonth()];
        const year = date.getFullYear();
        const formatted = `${dayName}, ${dateNum} ${monthName} ${year}`;
        document.getElementById("selected-date").textContent = formatted;
        const summary = document.getElementById("selected-date-summary");
        if (summary) summary.textContent = formatted;
    }

    // Ambil dan tampilkan jam
    const selectedTimes = JSON.parse(localStorage.getItem("selectedTimes") || "[]");
    const timeContainer = document.querySelector('.space-y-1'); // div untuk jam
    timeContainer.innerHTML = ''; // kosongkan

    selectedTimes.forEach(time => {
        const p = document.createElement("p");
        p.classList.add("font-medium");
        p.textContent = time;
        timeContainer.appendChild(p);
    });

    // Tampilkan total durasi
    const duration = localStorage.getItem("totalDuration") || "0";
    document.getElementById("total-duration").textContent = `${duration} jam`;

    // Tampilkan total harga
    const totalPrice = parseInt(localStorage.getItem("totalPrice") || "0");
    document.getElementById("total-price").textContent = `Rp ${totalPrice.toLocaleString()}`;

    // Embed Midtrans payment form
    if (window.snap && "<?= $snapToken ?>") {
        snap.embed("<?= $snapToken ?>", {
            embedId: 'snap-container',
            onSuccess: function(result) {
                console.log('Payment success', result);
                localStorage.setItem("order_id", result.order_id);
                window.location.href = 'success.php';
            },
            onPending: function(result) {
                console.log('Payment pending', result);
                // Handle pending payment
            },
            onError: function(result) {
                console.log('Payment error', result);
                // Handle error
            },
            onClose: function() {
                console.log('Payment popup closed');
            }
        });
    }
});
    
</script>
<body class="overflow-x-hidden max-w-screen scroll-smooth">
        <main class="flex-grow max-w-screen mx-auto px-4 sm:px-6 md:px-8 py-6">
          <div class="flex items-center mb-6">
            <button id="back-to-time" class="mr-3 p-2 hover:bg-gray-100 rounded-full">
              <a href="jam.php">
                <i class="ri-arrow-left-line text-gray-600"></i>
              </a>
            </button>
            <h2 class="text-2xl font-semibold text-gray-800">Pembayaran</h2>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-4">
              <div id="snap-container" class="bg-white rounded-lg shadow-md p-9 mb-6 w-full mx-auto">
                <h3 class="text-xl font-semibold mb-4 text-gray-800">Ringkasan Booking</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <div class="space-y-4">
                      <div>
                        <p class="text-gray-500 text-sm">Lapangan</p>
                        <p class="font-medium" id="lapangan-name">Memuat...</p>
                      </div>
                      <div>
                        <p class="text-gray-500 text-sm">Tanggal</p>
                        <p class="font-medium" id="selected-date">Kamis, 1 Mei 2025</p>
                      </div>
                      <div>
                        <p class="text-gray-500 text-sm">Jam</p>
                        <div class="space-y-1" id="jam-list">
                          <p class="font-medium" >0 jam</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div>
                    <div class="space-y-4">
                      <div>
                        <p class="text-gray-500 text-sm">Pemesan</p>
                        <p class="font-medium"><?= $nama_penyewa; ?></p>
                      </div>
                      <div>
                        <p class="text-gray-500 text-sm">Email</p>
                        <p class="font-medium"><?= $email_penyewa; ?></p>
                      </div>
                      <div>
                        <p class="text-gray-500 text-sm">Telepon</p>
                        <p class="font-medium"><?= $tlp_penyewa; ?></p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="border-t border-gray-200 mt-6 pt-4">
                  <div class="flex justify-between items-center">
                    <div>
                      <p class="text-gray-500 text-sm">Total Durasi</p>
                      <p class="font-medium" id="total-duration">0 jam</p>
                    </div>
                      <button id="pay-button">Pay!</button>
                      <div id="snap-container"></div>
                    <div class="text-right">
                      <p class="text-gray-500 text-sm">Total Pembayaran</p>
                      <p class="text-xl font-semibold text-primary" id="total-price">
                        Rp 0
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
        <?php include 'include/footer.php';?>
      </div>
    </div>
    
    <script>
    document.addEventListener("DOMContentLoaded", function () {

    // ==== TARIK DATA DARI localStorage ====
    const lapanganName   = localStorage.getItem("lapanganName") || "Lapangan N/A";
    const lapanganPrice  = parseInt(localStorage.getItem("lapanganPrice") || "0");
    const selectedTimes  = JSON.parse(localStorage.getItem("selectedTimes") || "[]");
    const totalDuration  = localStorage.getItem("totalDuration") || "0";
    const totalPrice     = parseInt(localStorage.getItem("totalPrice") || "0");
    const selectedDate   = localStorage.getItem("selectedDate") || "";

    // ==== MASUKKAN KE DOM ====
    document.getElementById("lapangan-name").textContent   = lapanganName;

    // tanggal
    if (selectedDate) {
        const date = new Date(selectedDate);
        const dayNames = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
        const monthNames= ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
        document.getElementById("selected-date").textContent =
            `${dayNames[date.getDay()]}, ${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
    }

    // jam-jam terpilih
    const jamList = document.getElementById("jam-list");
    jamList.innerHTML = "";                      // bersihin dulu
    selectedTimes.forEach(jam=>{
        const p = document.createElement("p");
        p.className = "font-medium";
        p.textContent = jam;
        jamList.appendChild(p);
    });

    // total durasi & total harga
    document.getElementById("total-duration").textContent = `${totalDuration} jam`;
    document.getElementById("total-price").textContent    = `Rp ${totalPrice.toLocaleString()}`;

    // ====== SNAP PAYMENT ======
    function startSnap() {
        snap.embed('<?= $snapToken ?>', {
            embedId: 'snap-container',
            onSuccess: res => window.location.href = 'success.php',
            onPending: res => alert("Pembayaran tertunda, selesaikan dulu ya!"),
            onError:   err => alert("Pembayaran gagal: " + err.status_message),
            onClose:   ()  => alert("Lo nutup pembayaran sebelum selesai.")
        });
    }
    if (window.snap) { startSnap(); }
    else {
        const wait = setInterval(()=>{
            if (window.snap){ clearInterval(wait); startSnap(); }
        }, 100);
    }
});

    </script>
</body>
</html>