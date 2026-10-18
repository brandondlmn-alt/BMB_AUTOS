<?php
require_once 'config.php';
require_once 'auth_check.php';

$user = authenticate($pdo);

try {
    // Join transactions and vehicles to find cars bought by this user
    $stmt = $pdo->prepare("
        SELECT v.* 
        FROM transactions t 
        JOIN vehicles v ON t.vehicle_id = v.id 
        WHERE t.user_id = ? 
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $cars = $stmt->fetchAll();
    
    echo json_encode([
        'status' => 'success',
        'data' => $cars
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>