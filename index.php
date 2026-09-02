<?php
session_start();
require_once 'db.php';

$currentUser = $_SESSION['user'] ?? null;
$customerName = $currentUser ? htmlspecialchars($currentUser['name']) : 'Guest';

// Fetch available vehicles with images from MySQL
$carStmt = $pdo->query("SELECT * FROM vehicles WHERE status = 'Available' ORDER BY id DESC LIMIT 6");
$cars = $carStmt->fetchAll();

// Fetch customer bookings if logged in
$myBookings = [];
if ($currentUser) {
    $bStmt = $pdo->prepare("SELECT * FROM bookings WHERE customer_id = ? ORDER BY booking_date DESC");
    $bStmt->execute([$currentUser['id']]);
    $myBookings = $bStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMB Autos | Home & Showroom</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .car-image-container:hover .preview-badge {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <?php include 'nav.php'; ?>

    <main class="mx-auto max-w-7xl px-6 py-10 space-y-12">
        <!-- Hero Section -->
        <section class="rounded-3xl bg-slate-900 p-8 md:p-12 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="max-w-2xl space-y-4">
                <span class="rounded-full bg-blue-500/20 px-3.5 py-1 text-xs font-bold uppercase tracking-widest text-blue-400">Premium Automotive Marketplace</span>
                <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl">Drive Certified Quality.</h1>
                <p class="text-slate-300 text-base leading-relaxed">
                    Buy inspected vehicles, schedule certified mechanic servicing, or consign your car for sale through our professional team.
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="cars.php" class="rounded-2xl bg-blue-600 px-6 py-3.5 font-bold text-white shadow-lg hover:bg-blue-500 transition">Explore All Cars</a>
                    <a href="servicing.php" class="rounded-2xl bg-slate-800 px-6 py-3.5 font-bold text-slate-200 ring-1 ring-slate-700 hover:bg-slate-700 transition">Book Maintenance</a>
                </div>
            </div>
            <div class="w-full md:w-96 rounded-2xl overflow-hidden shadow-2xl ring-1 ring-slate-700 relative group">
                <img src="https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=800&q=80" alt="Featured BMW" class="h-56 w-full object-cover transition-transform duration-500 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent flex items-end p-4">
                    <span class="text-xs font-semibold text-white">BMW 320i M Sport • Certified Grade A</span>
                </div>
            </div>
        </section>

        <!-- Customer Bookings Overview -->
        <?php if ($currentUser && count($myBookings) > 0): ?>
        <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-2xl font-bold text-slate-900">Your Active Service & Evaluation Bookings</h2>
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-6 py-3">Booking ID</th>
                            <th class="px-6 py-3">Vehicle</th>
                            <th class="px-6 py-3">Service Type</th>
                            <th class="px-6 py-3">Date & Time</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($myBookings as $booking): ?>
                        <tr>
                            <td class="px-6 py-4 font-semibold">#<?= $booking['id'] ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($booking['vehicle_info']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($booking['service_type']) ?></td>
                            <td class="px-6 py-4"><?= $booking['booking_date'] ?> @ <?= substr($booking['booking_time'], 0, 5) ?></td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"><?= htmlspecialchars($booking['status']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

        <!-- Featured Showroom Vehicles with Hover Viewer -->
        <section>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Featured Showroom Vehicles</h2>
                    <p class="text-sm text-slate-500">Hover over any car image to zoom and preview details.</p>
                </div>
                <a href="cars.php" class="text-sm font-bold text-blue-600 hover:text-blue-500">View All (<?= count($cars) ?>) →</a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($cars as $car): ?>
                <div class="group flex flex-col justify-between overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    
                    <!-- Hover Zoom Image Container -->
                    <div class="car-image-container relative h-52 w-full overflow-hidden bg-slate-100 cursor-pointer" onclick="openImageModal('<?= htmlspecialchars($car['image']) ?>', '<?= htmlspecialchars("{$car['year']} {$car['make']} {$car['model']}") ?>', '<?= htmlspecialchars($car['price']) ?>')">
                        <img 
                            src="<?= htmlspecialchars($car['image']) ?>" 
                            alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>" 
                            class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110"
                            onerror="this.src='https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80'"
                        >
                        <!-- Category Badge -->
                        <span class="absolute top-4 left-4 rounded-full bg-slate-900/80 backdrop-blur px-3 py-1 text-xs font-semibold text-white uppercase tracking-wider">
                            <?= htmlspecialchars($car['type']) ?>
                        </span>
                        <!-- Evaluation Grade Badge -->
                        <span class="absolute top-4 right-4 rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white shadow">
                            <?= htmlspecialchars($car['evaluation_grade']) ?>
                        </span>
                        <!-- Hover Preview Indicator -->
                        <div class="preview-badge pointer-events-none absolute inset-0 flex items-center justify-center bg-slate-900/40 opacity-0 transition-all duration-300 transform translate-y-2">
                            <span class="rounded-xl bg-white/95 px-4 py-2 text-xs font-bold text-slate-900 shadow-md">Click to Enlarge 🔍</span>
                        </div>
                    </div>

                    <!-- Details Area -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-slate-900"><?= "{$car['year']} {$car['make']} {$car['model']}" ?></h3>
                        <p class="mt-2 text-2xl font-extrabold text-blue-600"><?= htmlspecialchars($car['price']) ?></p>

                        <div class="mt-4 grid grid-cols-2 gap-2 border-t border-b border-slate-100 py-3 text-xs text-slate-600">
                            <div><strong>Mileage:</strong> <?= htmlspecialchars($car['mileage']) ?></div>
                            <div><strong>Fuel:</strong> <?= htmlspecialchars($car['fuel_type']) ?></div>
                            <div><strong>Transmission:</strong> <?= htmlspecialchars($car['transmission']) ?></div>
                            <div><strong>Status:</strong> <span class="text-emerald-600 font-semibold"><?= htmlspecialchars($car['status']) ?></span></div>
                        </div>

                        <button 
                            class="mt-5 w-full rounded-2xl bg-slate-900 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                            onclick="showToast('Inquiry logged for <?= htmlspecialchars("{$car['year']} {$car['make']} {$car['model']}") ?>. A representative will contact you.')"
                        >
                            Inquire / Purchase
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Full-Screen Hover / Click Image Lightbox Modal -->
    <div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4" onclick="closeImageModal()">
        <div class="relative max-w-3xl w-full rounded-3xl bg-slate-900 overflow-hidden shadow-2xl p-4 text-white" onclick="event.stopPropagation()">
            <button onclick="closeImageModal()" class="absolute top-6 right-6 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-slate-800/80 text-white hover:bg-slate-700">✕</button>
            <img id="modalImage" src="" alt="Car Preview" class="h-96 w-full object-cover rounded-2xl">
            <div class="mt-4 flex items-center justify-between px-2">
                <div>
                    <h3 id="modalTitle" class="text-2xl font-bold"></h3>
                    <p class="text-sm text-slate-400">Certified by BMB Autos Technical Team</p>
                </div>
                <span id="modalPrice" class="text-2xl font-black text-blue-400"></span>
            </div>
        </div>
    </div>

    <div id="toast" class="pointer-events-none fixed right-6 top-6 hidden w-80 rounded-3xl bg-slate-900 p-4 text-white shadow-xl z-50"></div>
    <script src="script.js"></script>
    <script>
        function openImageModal(imgSrc, title, price) {
            document.getElementById('modalImage').src = imgSrc;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalPrice').textContent = price;
            document.getElementById('imageModal').classList.remove('hidden');
            document.getElementById('imageModal').classList.add('flex');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.getElementById('imageModal').classList.remove('flex');
        }
    </script>
</body>
</html>