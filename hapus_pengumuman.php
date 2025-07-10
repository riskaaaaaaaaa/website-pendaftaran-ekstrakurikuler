<?php
include "config.php";

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $query = "DELETE FROM pengumuman WHERE id = $id";
    if ($conn->query($query) === TRUE) {
        header("Location: kelola_pengumuman.php?hapus=1");
    } else {
        echo "Gagal menghapus: " . $conn->error;
    }
} else {
    echo "ID tidak ditemukan.";
}
