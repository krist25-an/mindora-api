<?php
header('Content-Type: application/json');

require_once 'config/db.php'; 

// CORS dan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diperbolehkan']);
    exit;
}

// Ambil input JSON
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

// Validasi
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email dan password wajib diisi']);
    exit;
}

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Akun tidak ditemukan']);
    exit;
}

// Cek password
if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Password salah']);
    exit;
}

// Cek apakah sudah verifikasi OTP
if ((int)$user['is_otp_verified'] !== 1) {
    echo json_encode(['success' => false, 'message' => 'Akun belum diverifikasi. Silakan cek email Anda untuk verifikasi OTP.']);
    exit;
}

// Login berhasil
echo json_encode([
    'success' => true,
    'message' => 'Login berhasil',
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email']
    ]
]);
