<?php
// api/update_profile.php
require_once 'config.php';
require_once 'auth_check.php';

$user = authenticate($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = getJsonInput();
$name = trim($data['name'] ?? $user['name']);
$email = strtolower(trim($data['email'] ?? $user['email']));
$notifyEmail = isset($data['notify_email']) ? (int)$data['notify_email'] : 1;
$notifyPush = isset($data['notify_push']) ? (int)$data['notify_push'] : 1;

try {
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, notify_email = ?, notify_push = ? WHERE id = ?");
    $stmt->execute([$name, $email, $notifyEmail, $notifyPush, $user['id']]);
    
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully.']);
} catch (PDOException $e) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Email already in use.']);
}
?>