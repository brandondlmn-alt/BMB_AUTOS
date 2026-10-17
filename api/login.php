<?php
// api/login.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = getJsonInput();
$email = strtolower(trim($data['email'] ?? ''));
$password = trim($data['password'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Generate secure API token
    $token = bin2hex(random_bytes(32));
    
    $update = $pdo->prepare("UPDATE users SET api_token = ? WHERE id = ?");
    $update->execute([$token, $user['id']]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
}
?>