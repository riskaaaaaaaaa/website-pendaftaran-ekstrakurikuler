<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $conn->real_escape_string($_POST['judul']);
    $isi   = $conn->real_escape_string($_POST['isi']);
    $tanggal = date("Y-m-d");

    $query = "INSERT INTO pengumuman (judul, isi, tanggal) VALUES ('$judul', '$isi', '$tanggal')";

    if ($conn->query($query) === TRUE) {
        header("Location: kelola_pengumuman.php?success=1");
    } else {
        header("Location: kelola_pengumuman.php?error=1");
    }
}
?>
