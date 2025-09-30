<?php
session_start();
include 'conf/koneksi.php';
include 'include/header.php';

// Cek user sudah login
if (!isset($_SESSION['email_penyewa'])) {
    header('Location: index.php'); // arahkan ke login jika belum login
    exit();
}

$emailLogin = $_SESSION['email_penyewa'];

$successMsg = '';
$errorMsg = '';

// Proses update profil atau hapus akun
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $nama = trim($_POST['nama_penyewa']);
        $email = trim($_POST['email_penyewa']);
        $telepon = trim($_POST['no_telp']);

        if (empty($nama) || empty($email) || empty($telepon)) {
            $errorMsg = 'Semua bidang wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Format email tidak valid.';
        } else {
            if ($email !== $emailLogin) {
                $stmtCheck = $conn->prepare("SELECT id_penyewa FROM penyewa WHERE email_penyewa = ? LIMIT 1");
                $stmtCheck->bind_param('s', $email);
                $stmtCheck->execute();
                $stmtCheck->store_result();
                if ($stmtCheck->num_rows > 0) {
                    $errorMsg = 'Email sudah digunakan oleh pengguna lain.';
                }
                $stmtCheck->close();
            }
        }

        if (empty($errorMsg)) {
            $stmtUpdate = $conn->prepare("UPDATE penyewa SET nama_penyewa = ?, email_penyewa = ?, no_telp = ? WHERE email_penyewa = ?");
            $stmtUpdate->bind_param('ssss', $nama, $email, $telepon, $emailLogin);
            if ($stmtUpdate->execute()) {
                $successMsg = 'Profil berhasil diperbarui.';
                if ($email !== $emailLogin) {
                    $_SESSION['email_penyewa'] = $email;
                    $emailLogin = $email;
                }
            } else {
                $errorMsg = 'Terjadi kesalahan saat menyimpan data.';
            }
            $stmtUpdate->close();
        }
    } elseif (isset($_POST['delete_account'])) {
        $stmtDelete = $conn->prepare("DELETE FROM penyewa WHERE email_penyewa = ?");
        $stmtDelete->bind_param('s', $emailLogin);
        if ($stmtDelete->execute()) {
            $stmtDelete->close();
            session_destroy();
            header('Location: goodbye.php');
            exit();
        } else {
            $errorMsg = 'Terjadi kesalahan saat menghapus akun.';
        }
    }
}

// Ambil data user terbaru
$stmt = $conn->prepare("SELECT nama_penyewa, email_penyewa, no_telp FROM penyewa WHERE email_penyewa = ?");
$stmt->bind_param('s', $emailLogin);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $nama_penyewa = htmlspecialchars($row['nama_penyewa'], ENT_QUOTES, 'UTF-8');
    $email_penyewa = htmlspecialchars($row['email_penyewa'], ENT_QUOTES, 'UTF-8');
    $no_telp = htmlspecialchars($row['no_telp'], ENT_QUOTES, 'UTF-8');
} else {
    session_destroy();
    header('Location: index.php');
    exit();
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profil Saya - Futsal Court</title>
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"/>
  <style>
    body { font-family: 'Inter', sans-serif; }
    .btn-primary {
      @apply bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary/90 transition;
    }
    .btn-danger {
      @apply bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition;
    }
  </style>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const editButton = document.getElementById('edit-btn');
      const saveButton = document.getElementById('save-btn');
      const cancelButton = document.getElementById('cancel-btn');
      const formFields = document.querySelectorAll('.form-field');
      const formReadOnly = document.getElementById('profile-view');
      const formEditable = document.getElementById('profile-edit');

      function toggleEditMode(enable) {
        if (enable) {
          formReadOnly.classList.add('hidden');
          formEditable.classList.remove('hidden');
        } else {
          formReadOnly.classList.remove('hidden');
          formEditable.classList.add('hidden');
        }
      }

      if (editButton) {
        editButton.addEventListener('click', () => {
          toggleEditMode(true);
        });
      }
      if (cancelButton) {
        cancelButton.addEventListener('click', (e) => {
          e.preventDefault();
          toggleEditMode(false);
          // Optionally, reset form fields to original values
          formEditable.querySelector('#nama_penyewa').value = '<?= $nama_penyewa; ?>';
          formEditable.querySelector('#email_penyewa').value = '<?= $email_penyewa; ?>';
          formEditable.querySelector('#no_telp').value = '<?= $no_telp; ?>';
        });
      }
    });
  </script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<main class="flex-grow container mx-auto px-4 max-w-xl mt-10 mb-10">
  <div class="relative bg-white shadow-lg rounded-2xl overflow-hidden">
    <div class="bg-gradient-to-r from-emerald-500 to-green-600 p-6 text-white text-center">
      <img src="https://i.pravatar.cc/100?u=<?= $email_penyewa; ?>" alt="Avatar"
        class="w-24 h-24 rounded-full border-4 border-white mx-auto -mb-12 shadow-md"/>
      <h2 class="mt-16 text-2xl font-bold"><?= $nama_penyewa; ?></h2>
      <p class="text-sm opacity-90"><?= $email_penyewa; ?></p>
    </div>

    <div class="px-6 pt-16 pb-8">
      <?php if($errorMsg): ?>
        <div class="mb-4 text-red-700 bg-red-100 border border-red-400 rounded-md p-3 text-sm">
          <i class="ri-error-warning-line mr-2"></i><?= $errorMsg; ?>
        </div>
      <?php endif; ?>
      <?php if($successMsg): ?>
        <div class="mb-4 text-green-800 bg-green-100 border border-green-400 rounded-md p-3 text-sm">
          <i class="ri-checkbox-circle-line mr-2"></i><?= $successMsg; ?>
        </div>
      <?php endif; ?>

      <!-- View Mode -->
      <div id="profile-view" class="space-y-5">
        <div class="flex items-center justify-between">
          <span class="text-sm text-gray-600">Nomor Telepon</span>
          <span class="text-base text-gray-800 font-medium"><?= $no_telp; ?></span>
        </div>

      <div class="mt-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 h-20">
        <button id="edit-btn" class="flex-1 bg-green-600 text-white px-6 py-2 rounded-full shadow hover:bg-green-700 transition text-center">
          <i class="ri-edit-2-line mr-2"></i>Edit Profil
        </button>
    
        <a href="Dashboard.php" class="flex-1 bg-gray-100 text-gray-700 px-6 py-2 rounded-full border hover:bg-gray-200 transition text-center">
          <i class="ri-home-4-line mr-2"></i>Kembali ke Beranda
        </a>
      </div>

      <form method="POST" class="mt-6">
        <button type="submit" name="delete_account" onclick="return confirm('Yakin ingin menghapus akun?')" class="w-full bg-red-600 text-white px-6 py-3 rounded-full hover:bg-red-700 transition shadow">
          <i class="ri-delete-bin-6-line mr-2"></i>Hapus Akun
        </button>
      </form>
    </div>
  </div>
</main>
<?php include 'include/footer.php';?>
</body>
</html>

