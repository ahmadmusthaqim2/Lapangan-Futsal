<?php    
    $isLoggedIn = isset($_SESSION['email_penyewa']) && $_SESSION['email_penyewa'] !== null;
    $nama_penyewa = '';
    $email_penyewa = '';
    $tlp_penyewa = '';

    if ($isLoggedIn) {
        $stmt = $conn->prepare("SELECT * FROM penyewa WHERE email_penyewa = ?");
        $stmt->bind_param('s', $_SESSION['email_penyewa']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $nama_penyewa = htmlspecialchars($row['nama_penyewa'], ENT_QUOTES, 'UTF-8');
            $email_penyewa = htmlspecialchars($row['email_penyewa'], ENT_QUOTES, 'UTF-8');
            $tlp_penyewa = htmlspecialchars($row['no_telp'], ENT_QUOTES, 'UTF-8');
        }
        $stmt->close();
    }
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Futsal Court Booking</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css"/>
    <link rel="stylesheet" href="css/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="sweetalert2.min.css">
    <script src="js/script.js"></script>
  </head>
  <body>
    <div id="app" class="min-h-screen">
      <!-- User Data & Date Selection Page -->
      <div id="user-date-page" class="block min-h-screen">
        <header class="bg-white shadow">
          <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center">
              <h1 class="font-['Pacifico'] text-2xl text-primary">Futsal Court</h1>
            </div>
            <div class="flex items-center space-x-4">
              <div class="relative">
                <button id="profile-button" class="flex items-center space-x-2 text-gray-700 hover:text-primary">
                  <div class="w-8 h-8 flex items-center justify-center bg-primary/10 rounded-full">
                    <i class="ri-user-line text-primary"></i>
                  </div>
                  <span class="font-medium"><?= $nama_penyewa; ?></span>
                  <i class="ri-arrow-down-s-line"></i>
                </button>
                <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                  <a href="profil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
                  <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Riwayat Booking</a>
                  <div class="border-t border-gray-200"></div>
                  <a href="logout.php" class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100">Keluar</a>
                </div>
              </div>
            </div>
          </div>
        </header>