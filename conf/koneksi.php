<?php
    $conn = mysqli_connect("localhost", "root", "", "lapangan");
    if(!$conn) {
        die("Connection Failed: ".mysqli_connect_error());
    }
?>