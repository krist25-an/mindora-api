<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$forum_id = $data['forum_id'] ?? null;
$user_id = $data['user_id'] ?? null;
$nama = $data['nama'] ?? '';
$komentar = $data['komentar'] ?? '';

// Validasi sederhana
if (!$forum_id || !$user_id || !$nama || !$komentar) {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap.'
    ]);
    exit;
}

// Simpan komentar ke database
$stmt = $conn->prepare("INSERT INTO diskusi (forum_id, user_id, nama, komentar) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $forum_id, $user_id, $nama, $komentar);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Komentar berhasil dikirim!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan komentar.']);
}
