<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include 'koneksi.php';

if (isset($_POST['signup'])) {
    $nama_penyewa = $_POST['nama_penyewa'];
    $email_penyewa = $_POST['email_penyewa'];
    $password_penyewa = $_POST['password_penyewa'];
    $confirm_pass = $_POST['confirm_password'];
    $no_telp = $_POST['no_telp'];

    // Validasi nomor telepon
    if (!preg_match('/^[0-9]{10,15}$/', $no_telp)) {
        $_SESSION['error'] = "Nomor telepon harus 10-15 digit angka!";
        header("Location: ../regis.php");
        exit;
    }

    // Cek konfirmasi password
    if ($password_penyewa !== $confirm_pass) {
        echo "Password dan konfirmasi tidak sama!";
        header("Location: ../regis.php");
        exit;
    }

    // Cek apakah email sudah terdaftar
    $cek_email = $conn->prepare("SELECT id_penyewa FROM penyewa WHERE email_penyewa = ?");
    $cek_email->bind_param("s", $email_penyewa);
    $cek_email->execute();
    $cek_email->store_result();

    if ($cek_email->num_rows > 0) {
        echo "Email sudah digunakan bro!";
        header("Location: ../regis.php");
        exit;
    }

    // Hash password
    // $hashed_password = password_hash($password_penyewa, PASSWORD_DEFAULT);
    $hashed_password = $password_penyewa;

    // Masukkan data ke DB
    $stmt = $conn->prepare("INSERT INTO penyewa (nama_penyewa, email_penyewa, password_penyewa, no_telp) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama_penyewa, $email_penyewa, $hashed_password, $no_telp);

    if ($stmt->execute()) {
        header("Location: ../index.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal daftar: " . $stmt->error;
        header("Location: ../regis.php");
        exit;
    }
}
?>
