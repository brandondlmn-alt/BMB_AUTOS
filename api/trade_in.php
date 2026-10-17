<?php
// api/trade_in.php
require_once 'config.php';
require_once 'auth_check.php';

$user = authenticate($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = getJsonInput();
$make = trim($data['make'] ?? '');
$model = trim($data['model'] ?? '');
$year = (int)($data['year'] ?? 2021);
$price = trim($data['price'] ?? 'R 0.00');
$type = trim($data['type'] ?? 'Sedan');

if (empty($make) || empty($model)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Make and Model are required']);
    exit;
}

try {
    // Insert into vehicles
    $stmt = $pdo->prepare("INSERT INTO vehicles (seller_id, make, model, year, price, type, status, evaluation_grade, image) VALUES (?, ?, ?, ?, ?, ?, 'Pending Evaluation', 'Pending Inspection', 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80')");
    $stmt->execute([$user['id'], $make, $model, $year, $price, $type]);

    // Auto-create an evaluation booking for a mechanic
    $mechStmt = $pdo->query("SELECT id FROM users WHERE role = 'mechanic' LIMIT 1");
    $mechanicId = $mechStmt->fetchColumn() ?: null;

    $bStmt = $pdo->prepare("INSERT INTO bookings (customer_id, mechanic_id, vehicle_info, service_type, booking_date, booking_time, condition_notes, status) VALUES (?, ?, ?, 'Pre-Sale Vehicle Evaluation', CURDATE(), '10:00:00', 'Evaluate car condition for sales showroom listing.', 'Scheduled')");
    $bStmt->execute([$user['id'], $mechanicId, "$year $make $model"]);

    echo json_encode(['status' => 'success', 'message' => 'Trade-in submitted successfully. Evaluation booked.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit trade-in.']);
}
?>