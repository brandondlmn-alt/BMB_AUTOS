<?php
require_once 'config.php';
require_once 'auth_check.php';

$user = authenticate($pdo);
$data = getJsonInput();
$carId = (int)($data['car_id'] ?? 0);
$amount = trim($data['amount'] ?? '');

if ($carId <= 0 || empty($amount)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid car data']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Record transaction
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, vehicle_id, amount) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $carId, $amount]);

    // Mark car as sold
    $update = $pdo->prepare("UPDATE vehicles SET status = 'Sold' WHERE id = ?");
    $update->execute([$carId]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Car purchased successfully!']);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    // This will send the exact MySQL error back to the app!
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>