<?php
// api/car_details.php
require_once 'config.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Valid Car ID required']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch();

if ($car) {
    echo json_encode(['status' => 'success', 'data' => $car]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Car not found']);
}
?>