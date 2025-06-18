<?php
header('Content-Type: application/json');
require_once 'config/db.php';

$id = $_GET['id'] ?? '';

if (!$id) {
  echo json_encode(['error' => 'Forum ID tidak ditemukan']);
  exit;
}

// Ambil data forum
$stmt = $conn->prepare("SELECT * FROM forums WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$forumResult = $stmt->get_result()->fetch_assoc();

// Ambil komentar terkait forum ini
$komenStmt = $conn->prepare("SELECT * FROM diskusi WHERE forum_id = ? ORDER BY created_at ASC");
$komenStmt->bind_param("s", $id);
$komenStmt->execute();
$komenResult = $komenStmt->get_result();

$komentar = [];
while ($row = $komenResult->fetch_assoc()) {
  $komentar[] = $row;
}

// Gabungkan hasil
echo json_encode([
  'forum' => $forumResult,
  'komentar' => $komentar
]);
