<?php
// api/sso_login.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = getJsonInput();
$email = strtolower(trim($data['email'] ?? ''));
$name = trim($data['name'] ?? 'Google User');
$googleId = trim($data['google_id'] ?? '');

if (empty($email) || empty($googleId)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing Google details']);
    exit;
}

// Check if user exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    // Register new user
    $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $insert = $pdo->prepare("INSERT INTO users (name, email, password, role, google_id) VALUES (?, ?, ?, 'customer', ?)");
    $insert->execute([$name, $email, $randomPassword, $googleId]);
    
    // Fetch the newly created user
    $stmt->execute([$email]);
    $user = $stmt->fetch();
}

// Generate standard API token
$token = bin2hex(random_bytes(32));
$update = $pdo->prepare("UPDATE users SET api_token = ?, google_id = ? WHERE id = ?");
$update->execute([$token, $googleId, $user['id']]);

echo json_encode([
    'status' => 'success',
    'message' => 'SSO Login successful',
    'token' => $token,
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    ]
]);
?>