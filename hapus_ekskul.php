<?php
include "config.php";

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Cek apakah ekskul sedang digunakan
    $cek = $conn->query("SELECT * FROM pendaftaran WHERE id_ekskul = $id");
    if ($cek->num_rows > 0) {
        echo json_encode(["status" => "error", "pesan" => "Ekskul tidak bisa dihapus karena masih digunakan."]);
        exit;
    }

    // Hapus jika tidak digunakan
    $hapus = $conn->query("DELETE FROM ekskul WHERE id_ekskul = $id");

    if ($hapus) {
        echo json_encode(["status" => "success", "pesan" => "Data berhasil dihapus."]);
    } else {
        echo json_encode(["status" => "error", "pesan" => "Gagal menghapus: " . $conn->error]);
    }
}
?>
