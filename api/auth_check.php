<?php
// api/auth_check.php
require_once 'config.php';

function authenticate($pdo) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (empty($authHeader)) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'No token provided']);
        exit;
    }

    $token = str_replace('Bearer ', '', $authHeader);
    
    $stmt = $pdo->prepare("SELECT id, name, email, role, notify_email, notify_push FROM users WHERE api_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token']);
        exit;
    }
    
    return $user;
}
?>