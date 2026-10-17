<?php
// api/cars.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Basic filtering support (optional)
$make = $_GET['make'] ?? '';
$query = "SELECT * FROM vehicles WHERE status = 'Available'";
$params = [];

if (!empty($make)) {
    $query .= " AND make LIKE ?";
    $params[] = "%$make%";
}

$query .= " ORDER BY id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$cars = $stmt->fetchAll();

echo json_encode([
    'status' => 'success',
    'count' => count($cars),
    'data' => $cars
]);
?>