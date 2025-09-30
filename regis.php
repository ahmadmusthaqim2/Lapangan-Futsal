<?php
    session_start();
    include 'conf/koneksi.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futsal Court Booking</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"/>
    <link rel="stylesheet" href="css/style.css">
    <style>
       .auth-container {
            background-image: url('img/bg.jpg');
            background-size: cover;
            background-position: center;
       }
    </style>
</head>
<body>
      <div id="login-page" class="min-h-screen flex items-center justify-center">
        <div class="auth-container w-full min-h-screen flex items-center justify-center p-4">
          <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-8 relative z-10">
            <div class="text-center mb-8">
              <h1 class="font-['Pacifico'] text-3xl text-primary">Futsal Court</h1>
              <p class="text-gray-600 mt-2">Booking lapangan futsal jadi lebih mudah</p>
            </div>
            <div id="register-form" class="block">
              <h2 class="text-2xl font-semibold mb-6 text-gray-800">Daftar</h2>
              <form name="form" action="conf/signupDB.php" method="POST">
                <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-medium mb-2" for="fullname">Nama Lengkap</label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                      <i class="ri-user-line text-gray-400"></i>
                    </div>
                    <input id="fullname" name="nama_penyewa" type="text" required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Nama lengkap Anda"/>
                  </div>
                </div>
                <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-medium mb-2" for="reg-email">Email</label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                      <i class="ri-mail-line text-gray-400"></i>
                    </div>
                    <input id="reg-email" type="email" name="email_penyewa" required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Masukkan email Anda"/>
                  </div>
                </div>
                <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-medium mb-2" for="reg-password">Password</label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                      <i class="ri-lock-line text-gray-400"></i>
                    </div>
                    <input id="reg-password" type="password" name="password_penyewa" required minlength="8" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Minimal 8 karakter"/>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer toggle-password">
                      <i class="ri-eye-off-line text-gray-400"></i>
                    </div>
                  </div>
                </div>
                <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-medium mb-2" for="confirm-password">Konfirmasi Password</label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                      <i class="ri-lock-line text-gray-400"></i>
                    </div>
                    <input id="confirm-password" type="password" name="confirm_password" required minlength="8" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Ulangi password"/>
                  </div>
                </div>
                <div class="mb-6">
                  <label class="block text-gray-700 text-sm font-medium mb-2" for="phone">Nomor Telepon</label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                      <i class="ri-phone-line text-gray-400"></i>
                    </div>
                    <input id="phone" type="tel" name="no_telp" required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" placeholder="08xxxxxxxxxx" pattern="[0-9]{10,15}" title="Nomor telepon harus 10-15 digit angka" oninput="this.value = this.value.replace(/[^0-9]/g, '')"/>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">Contoh: 081234567890 (hanya angka)</p>
                </div>
                <button
                  type="submit"
                  name="signup"
                  value="signup"
                  id="register-button"
                  class="w-full bg-primary text-white py-2 px-4 !rounded-button font-medium hover:bg-primary/90 transition-colors whitespace-nowrap">Daftar</button>
              </form>
              <div class="mt-6 text-center">
                <p class="text-gray-600">Sudah punya akun?
                  <a href="index.php" class="text-primary font-medium hover:underline">Masuk</a>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script src="https://kit.fontawesome.com/ef9e5793a4.js" crossorigin="anonymous"></script>
      <?php if (isset($_SESSION['error'])): ?>
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: '<?= $_SESSION['error']; ?>',
        });
      </script>
      <?php unset($_SESSION['error']); endif; ?>
        <script>
        document.addEventListener("DOMContentLoaded", function () {
          const form = document.querySelector("form");

          if (form) {
            form.addEventListener("submit", function (e) {
              const password = document.getElementById("reg-password").value;
              const confirmPassword = document.getElementById("confirm-password").value;
              const phone = document.getElementById("phone").value;
              const phoneRegex = /^[0-9]{10,15}$/;

              if (password.length < 8) {
                e.preventDefault();
                Swal.fire({
                  icon: "warning",
                  title: "Password Terlalu Pendek",
                  text: "Password minimal harus 8 karakter!",
                });
                return;
              }

              if (password !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                  icon: "error",
                  title: "Password Tidak Cocok",
                  text: "Password dan konfirmasi password tidak sama!",
                });
              }
              if (!phoneRegex.test(phone)) {
                e.preventDefault();
                Swal.fire({
                  icon: "error",
                  title: "Nomor Telepon Invalid",
                  text: "Nomor telepon harus 10-15 digit angka!",
                });
              }
            });
          } else {
            console.error("Form tidak ditemukan.");
          }
        });
      </script>
      <script src="js/script.js"></script>
</body>
</html>