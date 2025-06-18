<?php
require_once "config/db.php";

// Ambil data dari fetch
$data = json_decode(file_get_contents("php://input"), true);
$pertanyaan = trim($data['pertanyaan'] ?? '');
$user_id = intval($data['user_id'] ?? 0);
$konten_id = trim($data['konten_id'] ?? '');

if ($pertanyaan === '' || $user_id === 0 || $konten_id === '') {
  http_response_code(400);
  echo json_encode(["error" => "Data tidak lengkap (pertanyaan, user_id, atau konten_id)"]);
  exit;
}

// Kirim ke OpenRouter
$payload = [
  "model" => "deepseek/deepseek-r1:free",
  "messages" => [[
    "role" => "user",
    "content" => "Jawablah pertanyaan berikut secara singkat dan padat untuk publik umum.

Setelah itu, tentukan 1 kategori yang menurutmu paling relevan (buat sendiri kategorinya sesuai isi pertanyaan).

Format jawaban:
jawaban: [isi jawaban]
kategori: [nama kategori]

Pertanyaan: \"$pertanyaan\""
  ]]
];

$ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer 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",
  "Content-Type: application/json",
  "HTTP-Referer: yourdomain.com",
  "X-Title: Artikel AI Generator"
]);

$response = curl_exec($ch);
curl_close($ch);

if (!$response) {
  http_response_code(500);
  echo json_encode(["error" => "Gagal menghubungi AI"]);
  exit;
}

$resData = json_decode($response, true);
$content = $resData['choices'][0]['message']['content'] ?? '';

// Ekstrak jawaban & kategori
preg_match('/jawaban:\s*(.+?)\s*kategori:/is', $content, $matchJawaban);
preg_match('/kategori:\s*(.+)/i', $content, $matchKategori);

$jawaban = trim($matchJawaban[1] ?? '');
$kategori = trim($matchKategori[1] ?? 'Umum');

// Simpan pertanyaan (dengan konten_id)
$stmt = $conn->prepare("INSERT INTO pertanyaan_ai (user_id, pertanyaan, jawaban, kategori, konten_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $user_id, $pertanyaan, $jawaban, $kategori, $konten_id);
$stmt->execute();
$stmt->close();

// Update kategori di tabel users
$cek = $conn->prepare("SELECT categories FROM users WHERE id = ?");
$cek->bind_param("i", $user_id);
$cek->execute();
$res = $cek->get_result();
$row = $res->fetch_assoc();
$cek->close();

$kategori_lama = $row['categories'] ?? '[]';
$kategori_array = json_decode($kategori_lama, true);
if (!is_array($kategori_array)) $kategori_array = [];

if (!in_array($kategori, $kategori_array)) {
  $kategori_array[] = $kategori;
  $kategori_json = json_encode($kategori_array);

  $update = $conn->prepare("UPDATE users SET categories = ? WHERE id = ?");
  $update->bind_param("si", $kategori_json, $user_id);
  $update->execute();
  $update->close();
}

// Kirim respons ke frontend
echo json_encode([
  "pertanyaan" => $pertanyaan,
  "jawaban" => $jawaban,
  "kategori" => $kategori,
  "konten_id" => $konten_id
]);
?>
