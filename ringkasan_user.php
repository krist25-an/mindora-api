<?php
require_once "config/db.php";

$user_id = intval($_GET['user_id'] ?? 0);
if ($user_id === 0) {
  echo json_encode(["error" => "User ID tidak valid"]);
  exit;
}

$stmt = $conn->prepare("SELECT kategori FROM pertanyaan_ai WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$kategori = [];
while ($row = $result->fetch_assoc()) {
  $total++;
  if (!in_array($row['kategori'], $kategori)) {
    $kategori[] = $row['kategori'];
  }
}

echo json_encode([
  "total_pertanyaan" => $total,
  "kategori" => $kategori
]);
?>
