<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = trim($_POST['password'] ?? '');

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            $redirects = [
                'admin' => 'admin.php',
                'mechanic' => 'mechanic.php',
                'sales_rep' => 'sales.php',
                'customer' => 'index.php'
            ];

            echo json_encode([
                'status' => 'success',
                'message' => "Welcome back, {$user['name']}!",
                'redirect' => $redirects[$user['role']] ?? 'index.php'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
        }
        break;

    case 'register_customer':
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, $password]);
            echo json_encode(['status' => 'success', 'message' => 'Account registered! Please log in.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Email is already registered.']);
        }
        break;

    case 'create_booking':
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Please log in to make a booking.']);
            exit;
        }

        $serviceType = htmlspecialchars($_POST['service_type'] ?? 'General Service');
        $vehicle = htmlspecialchars($_POST['vehicle'] ?? 'Customer Car');
        $date = htmlspecialchars($_POST['date'] ?? date('Y-m-d'));
        $time = htmlspecialchars($_POST['time'] ?? '09:00');
        $customerId = $_SESSION['user']['id'];

        $mechStmt = $pdo->query("SELECT id FROM users WHERE role = 'mechanic' LIMIT 1");
        $mechanicId = $mechStmt->fetchColumn() ?: null;

        $stmt = $pdo->prepare("INSERT INTO bookings (customer_id, mechanic_id, vehicle_info, service_type, booking_date, booking_time, condition_notes, status) VALUES (?, ?, ?, ?, ?, ?, 'Initial diagnostic required.', 'Scheduled')");
        $stmt->execute([$customerId, $mechanicId, $vehicle, $serviceType, $date, $time]);

        echo json_encode(['status' => 'success', 'message' => "Booking confirmed for $serviceType on $date."]);
        break;

    case 'list_car_for_sale':
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'Please sign in to list your car.']);
            exit;
        }

        $make = htmlspecialchars($_POST['make'] ?? '');
        $model = htmlspecialchars($_POST['model'] ?? '');
        $year = (int)($_POST['year'] ?? 2021);
        $price = htmlspecialchars($_POST['price'] ?? 'R 0.00');
        $type = htmlspecialchars($_POST['type'] ?? 'Sedan');
        $sellerId = $_SESSION['user']['id'];

        $stmt = $pdo->prepare("INSERT INTO vehicles (seller_id, make, model, year, price, type, status, evaluation_grade, image) VALUES (?, ?, ?, ?, ?, ?, 'Pending Evaluation', 'Pending Inspection', 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80')");
        $stmt->execute([$sellerId, $make, $model, $year, $price, $type]);

        // Auto-create an evaluation booking for the mechanic
        $mechStmt = $pdo->query("SELECT id FROM users WHERE role = 'mechanic' LIMIT 1");
        $mechanicId = $mechStmt->fetchColumn() ?: null;

        $bStmt = $pdo->prepare("INSERT INTO bookings (customer_id, mechanic_id, vehicle_info, service_type, booking_date, booking_time, condition_notes, status) VALUES (?, ?, ?, 'Pre-Sale Vehicle Evaluation', CURDATE(), '10:00:00', 'Evaluate car condition for sales showroom listing.', 'Scheduled')");
        $bStmt->execute([$sellerId, $mechanicId, "$year $make $model"]);

        echo json_encode(['status' => 'success', 'message' => 'Car submitted! A mechanic evaluation booking has been scheduled.']);
        break;

    case 'update_mechanic_job':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mechanic') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
            exit;
        }
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $status = htmlspecialchars($_POST['status'] ?? 'Completed');
        $notes = htmlspecialchars($_POST['notes'] ?? '');

        $stmt = $pdo->prepare("UPDATE bookings SET status = ?, condition_notes = ? WHERE id = ?");
        $stmt->execute([$status, $notes, $bookingId]);

        echo json_encode(['status' => 'success', 'message' => "Job #$bookingId updated to $status."]);
        break;

    case 'sales_publish_car':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'sales_rep') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
            exit;
        }
        $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
        $finalPrice = htmlspecialchars($_POST['final_price'] ?? '');
        $status = htmlspecialchars($_POST['status'] ?? 'Available');

        $stmt = $pdo->prepare("UPDATE vehicles SET price = ?, status = ? WHERE id = ?");
        $stmt->execute([$finalPrice, $status, $vehicleId]);

        echo json_encode(['status' => 'success', 'message' => "Vehicle #$vehicleId published to store inventory."]);
        break;

    case 'admin_manage_staff':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
            exit;
        }

        $subAction = $_POST['sub_action'] ?? '';
        if ($subAction === 'create') {
            $name = htmlspecialchars($_POST['name'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $role = htmlspecialchars($_POST['role'] ?? 'mechanic');
            $password = password_hash($_POST['password'] ?? 'bmb123', PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $role]);
            echo json_encode(['status' => 'success', 'message' => "New $role account created for $name."]);
        } elseif ($subAction === 'delete') {
            $staffId = (int)($_POST['staff_id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role IN ('mechanic', 'sales_rep')");
            $stmt->execute([$staffId]);
            echo json_encode(['status' => 'success', 'message' => 'Staff member deleted.']);
        }
        break;

    case 'file_complaint':
        $category = htmlspecialchars($_POST['category'] ?? 'General');
        $message = htmlspecialchars($_POST['message'] ?? '');
        $userId = $_SESSION['user']['id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO complaints (user_id, category, message, status) VALUES (?, ?, ?, 'Open')");
        $stmt->execute([$userId, $category, $message]);

        echo json_encode(['status' => 'success', 'message' => 'Complaint ticket submitted. Support will follow up soon.']);
        break;

    case 'update_profile':
        if (!isset($_SESSION['user'])) { exit; }
        $name = htmlspecialchars($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $_SESSION['user']['id']]);
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully.']);
        break;

    case 'delete_account':
        if (!isset($_SESSION['user'])) { exit; }
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => 'Account deleted.']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
        break;
}