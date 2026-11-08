<?php
require_once 'config.php';
require_once 'auth_check.php';

$user = authenticate($pdo);
$data = getJsonInput();
$carId = (int)($data['car_id'] ?? 0);
$amount = trim($data['amount'] ?? '');
$paymentMethod = trim($data['payment_method'] ?? 'Card');

if ($carId <= 0 || empty($amount)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid car data']);
    exit;
}

try {
    $pdo->beginTransaction();

    $vehicleStmt = $pdo->prepare("SELECT id, stock_number, make, model, color, stock_quantity, status FROM vehicles WHERE id = ? FOR UPDATE");
    $vehicleStmt->execute([$carId]);
    $vehicle = $vehicleStmt->fetch();

    if (!$vehicle) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Vehicle not found.']);
        exit;
    }

    if ((int)$vehicle['stock_quantity'] <= 0 || $vehicle['status'] === 'Sold') {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'This vehicle is no longer available.']);
        exit;
    }

    $newStock = max((int)$vehicle['stock_quantity'] - 1, 0);
    $newStatus = $newStock > 0 ? 'Available' : 'Sold';

    $updateVehicle = $pdo->prepare("UPDATE vehicles SET stock_quantity = ?, status = ? WHERE id = ?");
    $updateVehicle->execute([$newStock, $newStatus, $carId]);

    $txnStmt = $pdo->prepare("INSERT INTO transactions (user_id, vehicle_id, amount, payment_method, status) VALUES (?, ?, ?, ?, 'Completed')");
    $txnStmt->execute([$user['id'], $carId, $amount, $paymentMethod]);
    $transactionId = $pdo->lastInsertId();

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Car purchased successfully!',
        'transaction_id' => (int)$transactionId,
        'receipt_url' => 'receipt.php?transaction_id=' . (int)$transactionId,
        'stock_quantity' => $newStock,
        'status' => $newStatus
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>