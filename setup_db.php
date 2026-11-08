<?php
$host = 'localhost';
$user = 'root';
$pass = '';

$statusMessages = [];

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS bmb_autos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE bmb_autos");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS transactions, complaints, bookings, vehicles, users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // 1. Users Table
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'mechanic', 'sales_rep', 'customer') DEFAULT 'customer',
        phone VARCHAR(20) DEFAULT '+27 11 000 0000',
        api_token VARCHAR(255) NULL,
        google_id VARCHAR(255) NULL,
        fcm_token VARCHAR(255) NULL,
        notify_email BOOLEAN DEFAULT TRUE,
        notify_push BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Vehicles Table (Updated with Stock & Color)
    $pdo->exec("CREATE TABLE vehicles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        stock_number VARCHAR(50) NOT NULL UNIQUE,
        seller_id INT NULL,
        make VARCHAR(50) NOT NULL,
        model VARCHAR(50) NOT NULL,
        year INT NOT NULL,
        price VARCHAR(50) NOT NULL,
        mileage VARCHAR(50) DEFAULT '0 km',
        type VARCHAR(50) DEFAULT 'Sedan',
        transmission VARCHAR(20) DEFAULT 'Automatic',
        fuel_type VARCHAR(20) DEFAULT 'Petrol',
        color VARCHAR(50) DEFAULT 'White',
        stock_quantity INT DEFAULT 1,
        image VARCHAR(255) DEFAULT 'images/car-placeholder.jpg',
        evaluation_grade VARCHAR(50) DEFAULT 'Pending Inspection',
        status ENUM('Available', 'Pending Evaluation', 'Listed', 'Sold') DEFAULT 'Available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 3. Bookings Table
    $pdo->exec("CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        mechanic_id INT NULL,
        vehicle_info VARCHAR(150) NOT NULL,
        service_type VARCHAR(100) NOT NULL,
        booking_date DATE NOT NULL,
        booking_time TIME NOT NULL,
        condition_notes TEXT NULL,
        status ENUM('Scheduled', 'In Progress', 'Evaluation Passed (Grade A)', 'Evaluation Passed (Grade B)', 'Evaluation Failed', 'Completed') DEFAULT 'Scheduled',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (mechanic_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 4. Complaints Table
    $pdo->exec("CREATE TABLE complaints (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        category VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('Open', 'In Review', 'Resolved') DEFAULT 'Open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 5. Transactions Table
    $pdo->exec("CREATE TABLE transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        vehicle_id INT NULL,
        amount VARCHAR(50) NOT NULL,
        payment_method VARCHAR(50) DEFAULT 'Card',
        status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Completed',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
    )");

    // --- SEED USERS ---
    $userStmt = $pdo->prepare("INSERT INTO users (id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $users = [
        [1, 'System Administrator', 'admin@bmbautos.co.za', password_hash('admin123', PASSWORD_DEFAULT), 'admin'],
        [2, 'Sipho Mechanic', 'mechanic@bmbautos.co.za', password_hash('mech123', PASSWORD_DEFAULT), 'mechanic'],
        [3, 'Lerato Sales', 'sales@bmbautos.co.za', password_hash('sales123', PASSWORD_DEFAULT), 'sales_rep'],
        [4, 'Thabo Mokoena', 'thabo@bmbautos.co.za', password_hash('welcome123', PASSWORD_DEFAULT), 'customer'],
        [5, 'Ayesha Khan', 'ayesha@gmail.com', password_hash('welcome123', PASSWORD_DEFAULT), 'customer'],
        [6, 'David Smith', 'david@gmail.com', password_hash('welcome123', PASSWORD_DEFAULT), 'customer']
    ];
    foreach ($users as $u) { $userStmt->execute($u); }

    // --- SEED 50 VEHICLES ---
    $carStmt = $pdo->prepare("INSERT INTO vehicles (stock_number, seller_id, make, model, year, price, mileage, type, transmission, fuel_type, color, stock_quantity, image, evaluation_grade, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $cars = [
        ['STK-1001', null, 'Toyota', 'Hilux 2.8 GD-6 4x4', 2023, 'R 689,900', '19,500 km', 'Bakkie', 'Automatic', 'Diesel', 'Glacier White', 2, 'https://images.unsplash.com/photo-1559416523-140ddc3d238c?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1002', null, 'Toyota', 'Hilux 2.8 GD-6 4x4', 2023, 'R 689,900', '21,000 km', 'Bakkie', 'Automatic', 'Diesel', 'Graphite Grey', 1, 'https://images.unsplash.com/photo-1559416523-140ddc3d238c?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1003', null, 'Toyota', 'Hilux 2.8 GD-6 4x4', 2022, 'R 650,000', '35,000 km', 'Bakkie', 'Manual', 'Diesel', 'Attitude Black', 1, 'https://images.unsplash.com/photo-1559416523-140ddc3d238c?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1004', null, 'Volkswagen', 'Polo GTI 2.0 TSI', 2022, 'R 429,000', '22,000 km', 'Hatchback', 'Automatic', 'Petrol', 'Pure White', 3, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1005', null, 'Volkswagen', 'Polo GTI 2.0 TSI', 2021, 'R 399,000', '45,000 km', 'Hatchback', 'Automatic', 'Petrol', 'Flash Red', 1, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1006', null, 'Volkswagen', 'Polo GTI 2.0 TSI', 2023, 'R 459,000', '10,000 km', 'Hatchback', 'Automatic', 'Petrol', 'Deep Black Pearl', 2, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1007', null, 'Mercedes-Benz', 'C200 AMG Line', 2021, 'R 579,000', '35,000 km', 'Sedan', 'Automatic', 'Petrol', 'Polar White', 1, 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1008', null, 'Mercedes-Benz', 'C200 AMG Line', 2020, 'R 520,000', '55,000 km', 'Sedan', 'Automatic', 'Petrol', 'Obsidian Black', 1, 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1009', null, 'Ford', 'Ranger 2.0 Wildtrak', 2022, 'R 639,500', '28,000 km', 'Bakkie', 'Automatic', 'Diesel', 'Cyber Orange', 1, 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1010', null, 'Ford', 'Ranger 2.0 Wildtrak', 2022, 'R 639,500', '29,500 km', 'Bakkie', 'Automatic', 'Diesel', 'Meteor Grey', 2, 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1011', null, 'Audi', 'RS3 Sportback', 2023, 'R 1,150,000', '8,000 km', 'Hatchback', 'Automatic', 'Petrol', 'Kyalami Green', 1, 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1012', null, 'Audi', 'RS3 Sportback', 2022, 'R 1,050,000', '18,000 km', 'Hatchback', 'Automatic', 'Petrol', 'Mythos Black', 1, 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1013', null, 'Land Rover', 'Defender 110', 2022, 'R 1,450,000', '24,000 km', 'SUV', 'Automatic', 'Diesel', 'Pangea Green', 1, 'https://images.unsplash.com/photo-1594502184342-2e12f877aa73?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1014', null, 'Land Rover', 'Defender 110', 2021, 'R 1,350,000', '44,000 km', 'SUV', 'Automatic', 'Diesel', 'Fuji White', 1, 'https://images.unsplash.com/photo-1594502184342-2e12f877aa73?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1015', null, 'Kia', 'Sportage GT-Line', 2023, 'R 720,000', '12,500 km', 'SUV', 'Automatic', 'Petrol', 'Lunar Silver', 2, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1016', null, 'Kia', 'Sportage GT-Line', 2023, 'R 720,000', '14,000 km', 'SUV', 'Automatic', 'Petrol', 'Infra Red', 1, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1017', null, 'Volkswagen', 'Golf 8 R', 2022, 'R 850,000', '15,000 km', 'Hatchback', 'Automatic', 'Petrol', 'Lapiz Blue', 1, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1018', null, 'Volkswagen', 'Golf 8 R', 2022, 'R 845,000', '16,500 km', 'Hatchback', 'Automatic', 'Petrol', 'Pure White', 2, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1019', null, 'Isuzu', 'D-Max 3.0 LSE', 2023, 'R 760,000', '10,000 km', 'Bakkie', 'Automatic', 'Diesel', 'Desert Orange', 1, 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1020', null, 'Isuzu', 'D-Max 3.0 LSE', 2022, 'R 710,000', '30,000 km', 'Bakkie', 'Automatic', 'Diesel', 'Splash White', 2, 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1021', null, 'Mazda', 'CX-5 2.0 Dynamic', 2022, 'R 480,000', '18,000 km', 'SUV', 'Automatic', 'Petrol', 'Soul Red Crystal', 1, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1022', null, 'Mazda', 'CX-5 2.0 Dynamic', 2022, 'R 475,000', '22,000 km', 'SUV', 'Automatic', 'Petrol', 'Machine Grey', 1, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1023', null, 'Honda', 'Civic 1.5T Sport', 2021, 'R 450,000', '30,000 km', 'Sedan', 'Automatic', 'Petrol', 'Platinum White', 2, 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1024', null, 'Honda', 'Civic 1.5T Sport', 2021, 'R 440,000', '35,000 km', 'Sedan', 'Automatic', 'Petrol', 'Crystal Black', 1, 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1025', null, 'Porsche', '911 Carrera S', 2020, 'R 2,450,000', '12,000 km', 'Coupe', 'Automatic', 'Petrol', 'Guards Red', 1, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1026', null, 'Porsche', '911 Carrera S', 2019, 'R 2,150,000', '28,000 km', 'Coupe', 'Automatic', 'Petrol', 'GT Silver', 1, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1027', null, 'Renault', 'Duster 1.5 dCi', 2021, 'R 320,000', '45,000 km', 'SUV', 'Manual', 'Diesel', 'Iron Blue', 3, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1028', null, 'Renault', 'Duster 1.5 dCi', 2022, 'R 350,000', '20,000 km', 'SUV', 'Manual', 'Diesel', 'Highland Grey', 2, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1029', null, 'Nissan', 'NP200 1.6', 2020, 'R 185,000', '65,000 km', 'Bakkie', 'Manual', 'Petrol', 'White', 5, 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1030', null, 'Nissan', 'NP200 1.6', 2021, 'R 205,000', '40,000 km', 'Bakkie', 'Manual', 'Petrol', 'Silver', 2, 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1031', null, 'BMW', 'X5 xDrive30d', 2022, 'R 1,250,000', '25,000 km', 'SUV', 'Automatic', 'Diesel', 'Alpine White', 1, 'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1032', null, 'BMW', 'X5 xDrive30d', 2021, 'R 1,100,000', '45,000 km', 'SUV', 'Automatic', 'Diesel', 'Carbon Black', 1, 'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1033', null, 'Hyundai', 'Tucson 2.0 Elite', 2023, 'R 680,000', '15,000 km', 'SUV', 'Automatic', 'Petrol', 'Phantom Black', 2, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1034', null, 'Hyundai', 'Tucson 2.0 Elite', 2022, 'R 620,000', '32,000 km', 'SUV', 'Automatic', 'Petrol', 'White Cream', 1, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1035', null, 'Toyota', 'Starlet 1.5 Xr', 2023, 'R 310,000', '10,000 km', 'Hatchback', 'Automatic', 'Petrol', 'Shadow Grey', 4, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1036', null, 'Toyota', 'Starlet 1.5 Xr', 2022, 'R 280,000', '25,000 km', 'Hatchback', 'Manual', 'Petrol', 'Mystic Pearl White', 3, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1037', null, 'Suzuki', 'Swift 1.2 GLX', 2023, 'R 230,000', '5,000 km', 'Hatchback', 'Manual', 'Petrol', 'Speedy Blue', 2, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1038', null, 'Suzuki', 'Swift 1.2 GLX', 2021, 'R 190,000', '42,000 km', 'Hatchback', 'Automatic', 'Petrol', 'Arctic White', 2, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1039', null, 'Haval', 'Jolion 1.5T Super Luxury', 2023, 'R 420,000', '12,000 km', 'SUV', 'Automatic', 'Petrol', 'Vivid Green', 1, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1040', null, 'Haval', 'Jolion 1.5T Super Luxury', 2022, 'R 380,000', '28,000 km', 'SUV', 'Automatic', 'Petrol', 'Hamilton White', 2, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1041', null, 'Chery', 'Tiggo 4 Pro', 2023, 'R 340,000', '9,000 km', 'SUV', 'Automatic', 'Petrol', 'Blood Red', 1, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1042', null, 'Chery', 'Tiggo 4 Pro', 2022, 'R 310,000', '22,000 km', 'SUV', 'Automatic', 'Petrol', 'Silver', 1, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1043', null, 'Jeep', 'Wrangler Rubicon', 2021, 'R 950,000', '35,000 km', 'SUV', 'Automatic', 'Petrol', 'Firecracker Red', 1, 'https://images.unsplash.com/photo-1594502184342-2e12f877aa73?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1044', null, 'Jeep', 'Wrangler Rubicon', 2020, 'R 880,000', '50,000 km', 'SUV', 'Automatic', 'Petrol', 'Sting-Gray', 1, 'https://images.unsplash.com/photo-1594502184342-2e12f877aa73?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1045', null, 'Volvo', 'XC60 B5 Inscription', 2022, 'R 890,000', '18,000 km', 'SUV', 'Automatic', 'Petrol', 'Crystal White', 1, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1046', null, 'Volvo', 'XC60 B5 Inscription', 2021, 'R 820,000', '38,000 km', 'SUV', 'Automatic', 'Petrol', 'Onyx Black', 1, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1047', null, 'Mini', 'Cooper S', 2022, 'R 550,000', '20,000 km', 'Hatchback', 'Automatic', 'Petrol', 'British Racing Green', 1, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1048', null, 'Mini', 'Cooper S', 2020, 'R 450,000', '45,000 km', 'Hatchback', 'Automatic', 'Petrol', 'Chili Red', 1, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-1049', null, 'Subaru', 'Outback 2.5i Field', 2023, 'R 790,000', '10,000 km', 'SUV', 'Automatic', 'Petrol', 'Autumn Green', 1, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade A Certified', 'Available'],
        ['STK-1050', null, 'Subaru', 'Outback 2.5i Field', 2021, 'R 650,000', '40,000 km', 'SUV', 'Automatic', 'Petrol', 'Crystal Black', 1, 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available'],
        ['STK-2001', 4, 'Volkswagen', 'Golf 8 GTI', 2021, 'R 580,000', '31,000 km', 'Hatchback', 'Automatic', 'Petrol', 'Moonstone Grey', 1, 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80', 'Pending Inspection', 'Pending Evaluation'],
        ['STK-3001', 5, 'BMW', '320i M Sport', 2019, 'R 415,000', '48,500 km', 'Sedan', 'Automatic', 'Petrol', 'Portimao Blue', 1, 'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=800&q=80', 'Grade B Certified', 'Available']
    ];

    foreach ($cars as $car) { $carStmt->execute($car); }

    // --- SEED BOOKINGS ---
    $bStmt = $pdo->prepare("INSERT INTO bookings (customer_id, mechanic_id, vehicle_info, service_type, booking_date, booking_time, condition_notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $bookings = [
        [4, 2, '2021 Volkswagen Golf 8 GTI', 'Vehicle Evaluation', '2026-10-25', '10:00:00', 'Full diagnostics required for trade-in.', 'Scheduled'],
        [4, 2, '2022 Toyota Corolla', 'Oil & Filter Change', '2026-10-28', '14:30:00', 'Routine minor service.', 'Scheduled'],
        [5, 2, '2019 BMW 320i M Sport', 'Major Diagnostic', '2026-10-26', '09:00:00', 'Engine light is on.', 'In Progress'],
        [6, 2, '2020 Toyota Fortuner', 'Brake Inspection', '2026-11-02', '11:00:00', 'Squeaking noise when braking.', 'Scheduled']
    ];
    foreach ($bookings as $b) { $bStmt->execute($b); }

    // --- SEED COMPLAINTS ---
    $cStmt = $pdo->prepare("INSERT INTO complaints (user_id, category, message, status) VALUES (?, ?, ?, ?)");
    $complaints = [
        [4, 'Mechanic Service', 'Service took 2 hours longer than scheduled, but work was completed properly.', 'In Review'],
        [5, 'Sales Department', 'I am still waiting for the vehicle registration papers.', 'Open']
    ];
    foreach ($complaints as $cp) { $cStmt->execute($cp); }

    $statusMessages = ["Database 'bmb_autos' successfully rebuilt with multiple users, rich data, extra vehicles, and stock numbers!"];
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BMB Autos | DB Auto-Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-900 px-4 text-slate-100">
    <div class="w-full max-w-lg rounded-3xl bg-slate-800 p-8 shadow-2xl ring-1 ring-slate-700">
        <h1 class="text-2xl font-bold text-white">Database Initialized</h1>
        <div class="mt-6 space-y-3">
            <?php if (isset($error)): ?>
                <div class="rounded-2xl bg-red-950 p-4 text-red-300 text-sm">Error: <?= htmlspecialchars($error) ?></div>
            <?php else: ?>
                <?php foreach ($statusMessages as $msg): ?>
                    <p class="text-emerald-400 text-sm">✓ <?= htmlspecialchars($msg) ?></p>
                <?php endforeach; ?>
                <a href="index.php" class="mt-6 block w-full text-center rounded-2xl bg-blue-600 py-3 font-semibold text-white hover:bg-blue-500">Go to Homepage</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>