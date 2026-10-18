<?php
require_once 'config.php';
require_once 'auth_check.php';

$user = authenticate($pdo);

try {
    $stmt = $pdo->prepare("
        SELECT t.id as transaction_id, t.amount, t.created_at, 
               v.stock_number, v.make, v.model, v.year, v.image 
        FROM transactions t 
        JOIN vehicles v ON t.vehicle_id = v.id 
        WHERE t.user_id = ? 
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $data = $stmt->fetchAll();
    
    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>