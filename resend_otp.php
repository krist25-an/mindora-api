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
        $mail->Subject = 'Kode OTP Baru Anda';
        $mail->Body    = "Kode OTP baru Anda adalah <b>$kodeOtp</b>. Kode ini berlaku selama 60 menit.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email tidak boleh kosong.']);
    exit;
}

// Ambil user_id dari email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Email tidak ditemukan.']);
    exit;
}
$user = $result->fetch_assoc();
$user_id = $user['id'];

// Hapus OTP sebelumnya jika ada (opsional tapi disarankan)
$stmt = $conn->prepare("DELETE FROM otp_verifications WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Buat OTP baru
$otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
$expires_at = date('Y-m-d H:i:s', strtotime('+60 minutes'));

// Simpan OTP baru
$stmt = $conn->prepare("INSERT INTO otp_verifications (user_id, otp_code, otp_expires_at) VALUES (?, ?, ?)");
$stmt->bind_param('iss', $user_id, $otp, $expires_at);
$stmt->execute();

// Kirim ulang OTP via email
$mail_sent = kirimOtpEmail($email, $otp);

if (!$mail_sent) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengirim ulang OTP ke email.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Kode OTP berhasil dikirim ulang ke email Anda.']);
