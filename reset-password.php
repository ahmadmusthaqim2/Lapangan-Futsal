<?php
// reset-password.php
session_start();

// Ambil token dari URL
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    // Token tidak ada, redirect ke halaman error
    header('Location: index.php?error=invalid_token');
    exit();
}

// Include koneksi database
include 'conf/koneksi.php';

// Validasi token dari database
$email = null;
$tokenValid = false;

try {
    $stmt = $conn->prepare("SELECT email FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() AND used = FALSE");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $tokenValid = true;
    }
} catch (Exception $e) {
    error_log("Token validation error: " . $e->getMessage());
}

if (!$tokenValid) {
    // Token tidak valid atau expired
    header('Location: index.php?error=invalid_or_expired_token');
    exit();
}

// Cek apakah form reset password disubmit
if ($_POST && isset($_POST['new_password'])) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validasi password
    if ($newPassword !== $confirmPassword) {
        $error = "Password tidak cocok";
    } elseif (strlen($newPassword) < 8) {
        $error = "Password minimal 8 karakter";
    } else {
        // Update password di database
        try {
            // Hash password sebelum disimpan
            // $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $hashedPassword = $newPassword;
            
            // Cari tabel user yang benar
            $userTables = ['penyewa', 'users', 'tb_penyewa', 'tb_users'];
            $updateSuccess = false;
            
            foreach ($userTables as $tableName) {
                try {
                    // Coba update dengan berbagai kemungkinan nama kolom
                    $passwordColumns = ['password_penyewa', 'password'];
                    $emailColumns = ['email_penyewa', 'email'];
                    
                    foreach ($passwordColumns as $passCol) {
                        foreach ($emailColumns as $emailCol) {
                            $updateStmt = $conn->prepare("UPDATE $tableName SET $passCol = ? WHERE $emailCol = ?");
                            if ($updateStmt) {
                                $updateStmt->bind_param("ss", $hashedPassword, $email);
                                if ($updateStmt->execute() && $updateStmt->affected_rows > 0) {
                                    $updateSuccess = true;
                                    break 3; // Break semua loop
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Lanjut ke tabel berikutnya
                    continue;
                }
            }
            
            if ($updateSuccess) {
                // Tandai token sebagai sudah digunakan
                $markUsedStmt = $conn->prepare("UPDATE password_reset_tokens SET used = TRUE WHERE token = ?");
                $markUsedStmt->bind_param("s", $token);
                $markUsedStmt->execute();
                
                // Set session untuk success message
                $_SESSION['success'] = 'Password berhasil direset. Silakan login dengan password baru.';
                
                // Redirect ke halaman sukses
                header('Location: index.php?success=password_reset');
                exit();
            } else {
                $error = "Gagal mengupdate password. Email tidak ditemukan dalam sistem.";
            }
        } catch (Exception $e) {
            error_log("Password update error: " . $e->getMessage());
            $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Futsal Court</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container {
            background: url('img/bg.jpg');
            background-size: cover;
            background-position: center;
        }
        input:focus {
            outline: none;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center">
        <div class="auth-container w-full min-h-screen flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-8">
                <div class="text-center mb-8">
                    <h1 class="font-['pacifico'] text-3xl text-green-500">Futsal Court</h1>
                    <p class="text-gray-600 mt-2">Reset Password Anda</p>
                </div>
                
                <form method="POST" id="reset-password-form">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-medium mb-2">
                            Password Baru
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="ri-lock-line text-gray-400"></i>
                            </div>
                            <input type="password" name="new_password" id="new_password" required 
                                   class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   placeholder="Masukkan password baru"
                                   minlength="8"/>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer toggle-password" onclick="togglePassword('new_password')">
                                <i class="ri-eye-off-line text-gray-400"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter</p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-medium mb-2">
                            Konfirmasi Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="ri-lock-line text-gray-400"></i>
                            </div>
                            <input type="password" name="confirm_password" id="confirm_password" required 
                                   class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   placeholder="Konfirmasi password baru"
                                   minlength="8"/>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer toggle-password" onclick="togglePassword('confirm_password')">
                                <i class="ri-eye-off-line text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" id="submit-btn"
                            class="w-full bg-green-500 text-white py-2 px-4 rounded font-medium hover:bg-green-600 transition-colors mb-4">
                        Reset Password
                    </button>
                </form>
                
                <div class="text-center">
                    <a href="index.php" class="text-green-500 font-medium hover:underline">
                        Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ri-eye-line text-gray-400';
            } else {
                input.type = 'password';
                icon.className = 'ri-eye-off-line text-gray-400';
            }
        }

        // Form validation
        document.getElementById('reset-password-form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password Tidak Cocok',
                    text: 'Pastikan password dan konfirmasi password sama.',
                    confirmButtonColor: '#10b981'
                });
            }
        });
    </script>

    <?php if (isset($error)): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes($error); ?>',
            confirmButtonColor: '#10b981'
        });
    </script>
    <?php endif; ?>
    <script src="js/script.js"></script>
    <script src="https://kit.fontawesome.com/ef9e5793a4.js" crossorigin="anonymous"></script>
</body>
</html>