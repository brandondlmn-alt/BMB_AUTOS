<?php
// api/register.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$data = getJsonInput();
$name = trim($data['name'] ?? '');
$email = strtolower(trim($data['email'] ?? ''));
$password = trim($data['password'] ?? '');

if (empty($name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    echo json_encode(['status' => 'success', 'message' => 'Account registered! Please log in.']);
} catch (PDOException $e) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Email is already registered.']);
}
?>