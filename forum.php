<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

require_once 'config/db.php';

$sql = "SELECT * FROM forums ORDER BY id DESC";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    // Decode sumber kalau berupa string JSON
    if (isset($row['sumber']) && is_string($row['sumber']) && $row['sumber'][0] == '[') {
        $row['sumber'] = json_decode($row['sumber'], true);
    }
    $data[] = $row;
}

echo json_encode($data);
?>
