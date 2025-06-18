<?php
require_once "config/db.php";

$id = intval($_GET['id'] ?? 0);
if ($id === 0) {
  echo json_encode(["error" => "ID tidak valid"]);
  exit;
}

$stmt = $conn->prepare("SELECT id, name, email, categories FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  // Pastikan categories selalu array
  $row['categories'] = json_decode($row['categories'] ?? '[]');
  echo json_encode($row);
} else {
  echo json_encode(["error" => "User tidak ditemukan"]);
}
?>
