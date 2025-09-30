<?php
    session_start();
    include 'conf/koneksi.php';
    include 'include/header.php';

    // Fungsi untuk mengamankan input dan output
    function esc($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // Ambil ID penyewa dari session
    $id_penyewa = $_SESSION['id_penyewa'] ?? null;
    $nama_penyewa = '';
    $email_penyewa = '';
    $tlp_penyewa = '';
    $tanggalTerakhirBooking = 'Belum pernah booking';

    // Ambil data user
    if ($id_penyewa) {
        $userQuery = "SELECT * FROM penyewa WHERE id_penyewa = '$id_penyewa'";
        $resultUser = $conn->query($userQuery);
        if ($resultUser && $resultUser->num_rows > 0) {
            $userData = $resultUser->fetch_assoc();
            $nama_penyewa = $userData['nama_penyewa'];
            $email_penyewa = $userData['email_penyewa'];
            $tlp_penyewa = $userData['no_telp'];
        }

        // Ambil booking terakhir berdasarkan tanggal DESC atau ID
        $queryLastBooking = "SELECT tanggal FROM booking 
                            WHERE id_penyewa = '$id_penyewa' 
                            ORDER BY tanggal DESC LIMIT 1";
        $resultLastBooking = $conn->query($queryLastBooking);
        if ($resultLastBooking && $resultLastBooking->num_rows > 0) {
            $rowBooking = $resultLastBooking->fetch_assoc();
            $tanggalTerakhirBooking = date('d F Y', strtotime($rowBooking['tanggal']));
        }
    }

    // Ambil data lapangan
    $lapanganList = [];
    $lapanganQuery = "SELECT * FROM lap WHERE deleted_at IS NULL ORDER BY id_lapangan ASC";
    $resultLapangan = $conn->query($lapanganQuery);

    // Periksa jika ada hasil query
    if ($resultLapangan) {
        while ($row = $resultLapangan->fetch_assoc()) {
            $lapanganList[] = $row;
        }
    }

?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const pilihJamButtons = document.querySelectorAll('a[href*="jam.php"]');
    pilihJamButtons.forEach(btn => {
        btn.addEventListener("click", function (e) {
            const selectedDate = localStorage.getItem("selectedDate");
            if (selectedDate) {
                // Langsung gunakan tanggal yang sudah dalam format YYYY-MM-DD
                const url = new URL(this.href);
                url.searchParams.set("date", selectedDate);
                this.href = url.toString();
            }
        });
    });
});
</script>

        <main class="flex-grow container mx-auto px-4 py-8">
          <!-- User Profile Section - Full Width -->
          <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Ringkasan Profil</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div class="flex items-center">
                <div class="w-16 h-16 flex items-center justify-center bg-primary/10 rounded-full mr-4">
                  <i class="ri-user-line text-primary text-2xl"></i>
                </div>
                <div>
                  <h3 class="font-medium text-gray-800"><?= $nama_penyewa; ?></h3>
                  <p class="text-gray-600 text-sm">Bergabung sejak Mei 2025</p>
                </div>
              </div>
              
              <div class="flex items-center">
                <div class="w-8 h-8 flex items-center justify-center mr-3 text-primary">
                  <i class="ri-mail-line"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Email</p>
                  <p class="text-gray-800 text-sm"><?=$email_penyewa; ?></p>
                </div>
              </div>
              
              <div class="flex items-center">
                <div class="w-8 h-8 flex items-center justify-center mr-3 text-primary">
                  <i class="ri-phone-line"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Telepon</p>
                  <p class="text-gray-800 text-sm"><?=$tlp_penyewa; ?></p>
                </div>
              </div>
              
              <div class="flex items-center">
                <div class="w-8 h-8 flex items-center justify-center mr-3 text-primary">
                  <i class="ri-history-line"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Booking Terakhir</p>
                  <p class="text-gray-800 text-sm"><?= esc($tanggalTerakhirBooking) ?></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Date Selection Section - Full Width -->
          <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
              <h2 class="text-xl font-semibold text-white flex items-center">
                <i class="ri-calendar-line mr-3"></i>
                Pilih Tanggal Booking
              </h2>
            </div>
            
            <div class="p-6">
              <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Calendar Section -->
                <div class="xl:col-span-2">
                  <div class="bg-gray-50 rounded-xl p-6">
                    <!-- Calendar Header -->
                    <div class="flex items-center justify-between mb-6">
                      <button id="prev-month" class="p-3 rounded-full hover:bg-white shadow-sm transition-all duration-200 hover:shadow-md">
                        <i class="ri-arrow-left-s-line text-gray-600 text-xl"></i>
                      </button>
                      <div class="text-center">
                        <h3 class="text-2xl font-bold text-gray-800" id="current-month">Juli 2025</h3>
                        <p class="text-sm text-gray-500 mt-1">Pilih tanggal yang tersedia</p>
                      </div>
                      <button id="next-month" class="p-3 rounded-full hover:bg-white shadow-sm transition-all duration-200 hover:shadow-md">
                        <i class="ri-arrow-right-s-line text-gray-600 text-xl"></i>
                      </button>
                    </div>
                    
                    <!-- Day Headers -->
                    <div class="grid grid-cols-7 gap-2 mb-4">
                      <div class="text-center py-3 text-sm font-semibold text-gray-600">Min</div>
                      <div class="text-center py-3 text-sm font-semibold text-gray-600">Sen</div>
                      <div class="text-center py-3 text-sm font-semibold text-gray-600">Sel</div>
                      <div class="text-center py-3 text-sm font-semibold text-gray-600">Rab</div>
                      <div class="text-center py-3 text-sm font-semibold text-gray-600">Kam</div>
                      <div class="text-center py-3 text-sm font-semibold text-gray-600">Jum</div>
                      <div class="text-center py-3 text-sm font-semibold text-gray-600">Sab</div>
                    </div>
                    
                    <!-- Calendar Days -->
                    <div class="grid grid-cols-7 gap-2" id="calendar-days">
                      <!-- Days akan ter-generate lewat JS -->
                    </div>
                  </div>
                </div>
                
                <!-- Selected Date Display -->
                <div class="xl:col-span-1">
                  <div class="sticky top-6">
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl p-6 border border-emerald-100">
                      <div class="text-center mb-6">
                        <div class="w-16 h-16 mx-auto bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                          <i class="ri-calendar-check-line text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Tanggal Terpilih</h3>
                      </div>
                      
                      <div class="text-center">
                        <div class="bg-white rounded-lg p-4 shadow-sm border border-emerald-100">
                          <p class="text-2xl font-bold text-emerald-600 mb-1" id="selected-date">Sabtu, 19 Juli 2025</p>
                          <p class="text-sm text-gray-600">Tanggal booking Anda</p>
                        </div>
                      </div>
                      
                      <div class="mt-6 space-y-3">
                        <div class="flex items-center text-sm text-gray-600">
                          <i class="ri-time-line mr-2 text-emerald-500"></i>
                          <span>Jam booking akan dipilih selanjutnya</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                          <i class="ri-information-line mr-2 text-emerald-500"></i>
                          <span>Pilih lapangan setelah memilih tanggal</span>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="mt-4 grid grid-cols-2 gap-3">
                      <button id="today-btn" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="ri-calendar-today-line mr-1"></i>
                        Hari Ini
                      </button>
                      <button id="tomorrow-btn" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="ri-calendar-event-line mr-1"></i>
                        Besok
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pilih Lapangan - 3 Columns -->
          <div class="mb-6">
            <h2 class="text-xl font-semibold mb-6 text-gray-800">Pilih Lapangan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <?php foreach ($lapanganList as $lapangan): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                  <div class="h-48 bg-gray-200">
                    <?php if (!empty($lapangan['gambar']) && file_exists('img/' . $lapangan['gambar'])): ?>
                      <img src="img/<?= esc($lapangan['gambar']) ?>" 
                           alt="<?= esc($lapangan['nama_lapangan']) ?>" 
                           class="w-full h-full object-cover"/>
                    <?php else: ?>
                      <img src="https://via.placeholder.com/600x300.png?text=<?= urlencode($lapangan['nama_lapangan']) ?>" 
                           alt="<?= esc($lapangan['nama_lapangan']) ?>"
                           class="w-full h-full object-cover"/>
                    <?php endif; ?>
                  </div>
                  <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2"><?= esc($lapangan['nama_lapangan']) ?></h3>
                    
                    <div class="flex items-center justify-between mb-4">
                      <span class="text-gray-600 text-sm">Harga per Jam</span>
                      <span class="text-primary font-semibold text-lg">Rp <?= number_format($lapangan['harga_jam'], 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="mb-4">
                      <h4 class="font-medium text-gray-800 mb-2 text-sm">Fasilitas:</h4>
                      <p class="text-gray-600 text-sm"><?= esc($lapangan['fasilitas']) ?></p>
                    </div>
                    
                    <a href="jam.php?lapangan_id=<?= esc($lapangan['id_lapangan']) ?>&date=<?= date('Y-m-d') ?>"
                       class="block text-center w-full bg-primary text-white py-3 px-4 rounded-lg font-medium hover:bg-primary/90 transition-colors duration-200">
                        <i class="ri-time-line mr-2"></i>Pilih Jam
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </main>
        
        <?php include 'include/footer.php';?>
      </div>
    </div>
    
    <script>
      if (!sessionStorage.getItem('alertShown')) {
        // Tampilkan alert
        Swal.fire({
          position: "center",
          icon: "success",
          title: "Selamat datang di sistem booking lapangan!",
          showConfirmButton: false,
          timer: 1500
        });

        // Set flag bahwa alert telah ditampilkan
        sessionStorage.setItem('alertShown', 'true');
      }

      // Calendar functionality
      document.addEventListener('DOMContentLoaded', function() {
        const currentMonthElement = document.getElementById('current-month');
        const calendarDays = document.getElementById('calendar-days');
        const selectedDateElement = document.getElementById('selected-date');
        const prevMonthBtn = document.getElementById('prev-month');
        const nextMonthBtn = document.getElementById('next-month');

        let currentDate = new Date();
        let selectedDate = new Date();

        const monthNames = [
          'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        function updateSelectedDateDisplay() {
          const dayName = dayNames[selectedDate.getDay()];
          const day = selectedDate.getDate();
          const month = monthNames[selectedDate.getMonth()];
          const year = selectedDate.getFullYear();
          selectedDateElement.textContent = `${dayName}, ${day} ${month} ${year}`;
            
          // Simpan dalam format YYYY-MM-DD langsung (lebih aman)
          const yyyy = selectedDate.getFullYear();
          const mm = String(selectedDate.getMonth() + 1).padStart(2, '0');
          const dd = String(selectedDate.getDate()).padStart(2, '0');
          const dateString = `${yyyy}-${mm}-${dd}`;
            
          localStorage.setItem('selectedDate', dateString);
        }

        function generateCalendar(year, month) {
          const firstDay = new Date(year, month, 1);
          const lastDay = new Date(year, month + 1, 0);
          const startDate = new Date(firstDay);
          startDate.setDate(startDate.getDate() - firstDay.getDay());

          currentMonthElement.textContent = `${monthNames[month]} ${year}`;
          calendarDays.innerHTML = '';

          for (let i = 0; i < 42; i++) {
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + i);

            const dayElement = document.createElement('div');
            dayElement.className = 'h-12 flex items-center justify-center cursor-pointer rounded-lg transition-all duration-200 text-sm font-medium';
            dayElement.textContent = date.getDate();

            const today = new Date();
            const isToday = date.toDateString() === today.toDateString();
            const isSelected = date.toDateString() === selectedDate.toDateString();
            const isPast = date < new Date().setHours(0, 0, 0, 0);
            const isCurrentMonth = date.getMonth() === month;

            if (!isCurrentMonth) {
              dayElement.className += ' text-gray-300 cursor-not-allowed';
            } else if (isPast) {
              dayElement.className += ' text-gray-400 cursor-not-allowed bg-gray-100';
            } else if (isSelected) {
              dayElement.className += ' bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg transform scale-110';
            } else if (isToday) {
              dayElement.className += ' bg-emerald-100 text-emerald-800 border-2 border-emerald-300';
            } else {
              dayElement.className += ' text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 hover:scale-105';
            }

            if (isCurrentMonth && !isPast) {
              dayElement.addEventListener('click', () => {
                selectedDate = new Date(date);
                updateSelectedDateDisplay();
                generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
              });
            }

            calendarDays.appendChild(dayElement);
          }
        }

        prevMonthBtn.addEventListener('click', () => {
          currentDate.setMonth(currentDate.getMonth() - 1);
          generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
        });

        nextMonthBtn.addEventListener('click', () => {
          currentDate.setMonth(currentDate.getMonth() + 1);
          generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
        });

        // Quick action buttons
        const todayBtn = document.getElementById('today-btn');
        const tomorrowBtn = document.getElementById('tomorrow-btn');

        todayBtn.addEventListener('click', () => {
          const today = new Date();
          selectedDate = new Date(today);
          currentDate = new Date(today);
          updateSelectedDateDisplay();
          generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
        });

        tomorrowBtn.addEventListener('click', () => {
          const tomorrow = new Date();
          tomorrow.setDate(tomorrow.getDate() + 1);
          selectedDate = new Date(tomorrow);
          currentDate = new Date(tomorrow);
          updateSelectedDateDisplay();
          generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
        });

        // Initialize calendar
        generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
        updateSelectedDateDisplay();
      });
    </script>
  </body>
</html>