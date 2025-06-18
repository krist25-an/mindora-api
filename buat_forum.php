<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once 'config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$konten_id = $data['konten_id'] ?? null;
$judul = $data['judul'] ?? '';
$subjudul = $data['subjudul'] ?? '';
$kategori = $data['kategori'] ?? '';
$isi_konten = $data['isi_konten'] ?? '';
$sumber = $data['sumber'] ?? null;
if (is_array($sumber)) {
    $sumber = json_encode($sumber);
}
$thumbnail = $data['thumbnail'] ?? '';
$dibuat_oleh = $data['dibuat_oleh'] ?? '';
$type = $data['type'] ?? '';

// Cek apakah konten_id sudah ada
$cekStmt = $conn->prepare("SELECT id FROM forums WHERE konten_id = ?");
$cekStmt->bind_param("s", $konten_id);
$cekStmt->execute();
$cekStmt->store_result();

if ($cekStmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Forum ini telah dibuat sebelumnya.']);
    exit;
}

$cekStmt->close();

// Jika belum ada, lanjut insert
$stmt = $conn->prepare("INSERT INTO forums (konten_id, judul, subjudul, kategori, isi_konten, sumber, thumbnail, dibuat_oleh, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $konten_id, $judul, $subjudul, $kategori, $isi_konten, $sumber, $thumbnail, $dibuat_oleh, $type);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Forum berhasil dibuat!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan!']);
}

$stmt->close();
$conn->close();
