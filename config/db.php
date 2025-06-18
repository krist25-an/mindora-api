<?php

$conn = new mysqli('localhost', 'root', '', 'mindora');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal']);
    exit;
}