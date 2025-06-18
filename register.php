<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'vendor/autoload.php'; 
require_once 'config/db.php'; 

date_default_timezone_set('Asia/Jakarta');

function kirimOtpEmail($email, $kodeOtp) {
    $mail = new PHPMailer(true);
    try {
        // Server email
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'floss.stephen@gmail.com';
        $mail->Password   = 'bvlmrjcefyoxpdfq';        
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Penerima
        $mail->setFrom('mindora@gmail.com', 'Mindora');
        $mail->addAddress($email);

        // Konten email
        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP Verifikasi Anda';
        $mail->Body    = "Kode OTP Anda adalah <b>$kodeOtp</b>. Jangan berikan kode ini kepada siapa pun.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

$input = json_decode(file_get_contents('php://input'), true);
$nama = trim($input['nama'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

// Validasi sederhana
if (!$nama || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi.']);
    exit;
}

// Cek jika email sudah terdaftar
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
    exit;
}

// Simpan user
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $nama, $email, $hashedPassword);
$stmt->execute();
$user_id = $stmt->insert_id;

// Generate OTP
$otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
$expires_at = date('Y-m-d H:i:s', strtotime('+60 minutes'));

// Simpan ke tabel OTP
$stmt = $conn->prepare("INSERT INTO otp_verifications (user_id, otp_code, otp_expires_at) VALUES (?, ?, ?)");
$stmt->bind_param('iss', $user_id, $otp, $expires_at);
$stmt->execute();

// Pastikan ini bekerja di server dengan mail() atau gunakan SMTP nanti
$mail_sent = kirimOtpEmail($email, $otp);

if (!$mail_sent) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengirim email OTP.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Registrasi berhasil. Silakan cek email untuk OTP.']);