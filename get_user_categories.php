<?php
header('Content-Type: application/json');
require_once 'config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID tidak ditemukan']);
    exit;
}

$stmt = $conn->prepare("SELECT categories FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
    exit;
}

$kategori = json_decode($user['categories'], true);

echo json_encode([
    'success' => true,
    'kategori' => $kategori
]);
