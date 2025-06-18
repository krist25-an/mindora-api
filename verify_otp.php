<?php
header('Content-Type: application/json');
require_once 'config/db.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$otp   = $data['otp'] ?? '';

if (!$email || !$otp) {
  echo json_encode(['success' => false, 'message' => 'Email dan kode OTP wajib diisi.']);
  exit;
}

// Ambil user_id dari email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$userResult = $stmt->get_result();

if ($userResult->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'User tidak ditemukan.']);
  exit;
}

$user = $userResult->fetch_assoc();
$user_id = $user['id'];

// Cek OTP dari tabel otp_verifications
$stmt = $conn->prepare("SELECT * FROM otp_verifications WHERE user_id = ? AND otp_code = ? AND otp_expires_at > NOW()");
$stmt->bind_param("is", $user_id, $otp);
$stmt->execute();
$otpResult = $stmt->get_result();

if ($otpResult->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'Kode OTP salah atau sudah kedaluwarsa.']);
  exit;
}

// Update kolom is_otp_verified menjadi 1
$stmt = $conn->prepare("UPDATE users SET is_otp_verified = 1 WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

// OTP valid, hapus kode OTP setelah verifikasi
$del = $conn->prepare("DELETE FROM otp_verifications WHERE user_id = ?");
$del->bind_param("i", $user_id);
$del->execute();

// Verifikasi sukses
echo json_encode(['success' => true, 'message' => 'OTP berhasil diverifikasi.']);
