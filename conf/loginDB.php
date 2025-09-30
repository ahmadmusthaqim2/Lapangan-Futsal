<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'koneksi.php';
session_start();

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Admin check
    if ($email === 'admin@admin' && $pass === 'admin123') {
        $_SESSION['is_admin'] = true;
        $_SESSION['email_admin'] = $email;
        header("Location: ../admin.php");
        exit();
    }

    // User check
    $query = "SELECT * FROM penyewa WHERE email_penyewa=? AND password_penyewa=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $email, $pass);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $_SESSION['id_penyewa'] = $data['id_penyewa'];
        $_SESSION['email_penyewa'] = $data['email_penyewa'];
        
        // Redirect logic
        $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '../Dashboard.php';
        unset($_SESSION['redirect_url']);
        header("Location: $redirect_url");
        exit();
    } else {
        // Simpan status error di session
        $_SESSION['login_error'] = true;
        header("Location: ../index.php");
        exit();
    }
}
?>