<?php
    session_start();
    include 'conf/koneksi.php';

    // Tampilkan alert jika ada error
if (isset($_SESSION['login_error'])) {
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: "error",
            title: "<strong>Login Gagal!</strong>",
            html: `<p>Kombinasi email dan password salah.<br>Silakan coba lagi.</p>`,
            confirmButtonColor: "#3085d6",
            confirmButtonText: "Coba Lagi",
            footer: "<a href=\'forgot-password.php\'>Lupa password?</a>",
            background: "#f8f9fa",
            showClass: {
                popup: "animate__animated animate__shakeX"
            },
            customClass: {
                popup: "my-swal-popup"
            }
        });
    });
    </script>';
    unset($_SESSION['login_error']);
}
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="sweetalert2.min.css">
    <style href="css/style.css">
      .auth-container {
        background-image: url('img/bg.jpg');
        background-size: cover;
        background-position: center;
      }
      input:focus, before:focus {
        outline:none
      }
    </style>
</head>
<body>
  <?php if (isset($_GET['success']) && $_GET['success'] === 'password_reset'): ?>
    <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg z-50">
        <div class="flex items-center">
            <i class="ri-check-circle-line mr-2"></i>
            <span>Password berhasil direset! Silakan login dengan password baru Anda.</span>
        </div>
    </div>
    <script>
        // Auto hide success message after 5 seconds
        setTimeout(function() {
            document.querySelector('.fixed.top-4.right-4').style.display = 'none';
        }, 5000);
    </script>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_or_expired_token'): ?>
    <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50">
        <div class="flex items-center">
            <i class="ri-error-warning-line mr-2"></i>
            <span>Link reset password tidak valid atau sudah kedaluwarsa.</span>
        </div>
    </div>
    <script>
        setTimeout(function() {
            document.querySelector('.fixed.top-4.right-4').style.display = 'none';
        }, 5000);
    </script>
<?php endif; ?>

    <!-- Login Page -->
      <div id="login-page" class="min-h-screen flex items-center justify-center">
        <div class="auth-container w-full min-h-screen flex items-center justify-center p-4">
          <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-8 relative z-10">
            <div class="text-center mb-8">
              <h1 class="font-['Pacifico'] text-3xl text-primary"> Futsal Court </h1>
              <p class="text-gray-600 mt-2"> Booking lapangan futsal jadi lebih mudah </p>
            </div>
            <div id="login-form">
              <h2 class="text-2xl font-semibold mb-6 text-gray-800">Masuk</h2>
              <form name="form" action="conf/loginDB.php" method="POST">
                <div class="mb-4">
                  <label class="block text-gray-700 text-sm font-medium mb-2" for="email">Email</label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                      <i class="ri-mail-line text-gray-400"></i>
                    </div>
                    <input id="email" type="email" name="email" required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Masukkan email Anda"/>
                  </div>
                </div>
                <div class="mb-6">
                  <label class="block text-gray-700 text-sm font-medium mb-2" for="password">Password</label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                      <i class="ri-lock-line text-gray-400"></i>
                    </div>
                    <input id="password" type="password" name="password" required class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Masukkan password"/>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer toggle-password">
                      <i class="ri-eye-off-line text-gray-400"></i>
                    </div>
                  </div>
                </div>
                <div class="flex items-center justify-between mb-6">
                  <div class="flex items-center">
                    <input id="remember-me" type="checkbox" class="h-4 w-4 text-primary border-gray-300 rounded"/>
                    <label for="remember-me" class="ml-2 block text-sm text-gray-700">Ingat saya</label>
                  </div>
                  <a href="lupaPass.php" class="text-sm text-primary hover:text-primary-dark">Lupa password?</a>
                </div>
                <button type="submit" name="submit" value="login" class="w-full bg-primary text-white py-2 px-4 !rounded-button font-medium hover:bg-primary/90 transition-colors whitespace-nowrap">
                  Masuk
                </button>
              </form>
              <div class="mt-6 text-center">
                <p class="text-gray-600">Belum punya akun?<a href="regis.php" class="text-primary font-medium hover:underline">Daftar</a></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script src="js/script.js"></script>
      <script src="https://kit.fontawesome.com/ef9e5793a4.js" crossorigin="anonymous"></script>
</body>
</html>