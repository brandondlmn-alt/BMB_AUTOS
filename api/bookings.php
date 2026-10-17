<?php
// api/bookings.php
require_once 'config.php';
require_once 'auth_check.php';

$user = authenticate($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch bookings for the logged-in user
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE customer_id = ? ORDER BY booking_date DESC");
    $stmt->execute([$user['id']]);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create a new booking
    $data = getJsonInput();
    $service = trim($data['service_type'] ?? 'General Service');
    $vehicle = trim($data['vehicle'] ?? '');
    $date = trim($data['date'] ?? '');
    $time = trim($data['time'] ?? '');

    if (empty($vehicle) || empty($date) || empty($time)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing booking details']);
        exit;
    }

    // Find a mechanic (simplified for demo)
    $mechStmt = $pdo->query("SELECT id FROM users WHERE role = 'mechanic' LIMIT 1");
    $mechanicId = $mechStmt->fetchColumn() ?: null;

    $stmt = $pdo->prepare("INSERT INTO bookings (customer_id, mechanic_id, vehicle_info, service_type, booking_date, booking_time, condition_notes, status) VALUES (?, ?, ?, ?, ?, ?, 'API Booking', 'Scheduled')");
    $stmt->execute([$user['id'], $mechanicId, $vehicle, $service, $date, $time]);

    echo json_encode(['status' => 'success', 'message' => 'Booking confirmed successfully.']);
}
?>