<?php
require_once "config/db.php";

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
  http_response_code(400);
  echo json_encode([]);
  exit;
}

$stmt = $conn->prepare("SELECT id, konten_id, pertanyaan, jawaban, kategori FROM pertanyaan_ai WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);
