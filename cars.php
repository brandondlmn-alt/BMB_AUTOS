<?php
session_start();
require_once 'db.php';

$search = trim($_GET['search'] ?? '');
$make = trim($_GET['make'] ?? '');

$query = "SELECT * FROM vehicles WHERE status = 'Available' AND stock_quantity > 0";
$params = [];

if (!empty($search)) {
    $query .= " AND (make LIKE ? OR model LIKE ? OR color LIKE ? OR stock_number LIKE ?)";
    $term = "%{$search}%";
    $params = [$term, $term, $term, $term];
}

if (!empty($make)) {
    $query .= " AND make = ?";
    $params[] = $make;
}

$query .= " ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$cars = $stmt->fetchAll();

$makeStmt = $pdo->query("SELECT DISTINCT make FROM vehicles WHERE status = 'Available' AND stock_quantity > 0 ORDER BY make");
$makes = $makeStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMB Autos | Vehicle Showroom & Consignment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .car-card:hover .preview-overlay {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <?php include 'nav.php'; ?>

    <main class="mx-auto max-w-7xl px-6 py-10 space-y-12">
        <!-- Sell Your Vehicle CTA Section -->
        <section class="rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-800 p-8 md:p-10 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="max-w-2xl space-y-2">
                <span class="rounded-full bg-blue-400/20 px-3.5 py-1 text-xs font-bold uppercase tracking-widest text-blue-200">Customer Consignment</span>
                <h2 class="text-3xl font-extrabold tracking-tight">Looking to Sell Your Vehicle?</h2>
                <p class="text-blue-100 text-sm leading-relaxed">
                    Submit your car for inspection. Our certified mechanics assess condition grading, and our dedicated sales representatives handle selling your car directly to buyers or in our store showroom.
                </p>
            </div>
            <button onclick="document.getElementById('sellModal').classList.remove('hidden')" class="whitespace-nowrap rounded-2xl bg-white px-7 py-4 font-bold text-blue-700 shadow-lg hover:bg-slate-100 transition duration-200">
                Sell My Car
            </button>
        </section>

        <!-- Search and Filter Section -->
        <section class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <form method="GET" class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="flex-1">
                    <label for="search" class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Search Inventory</label>
                    <input id="search" name="search" type="text" value="<?= htmlspecialchars($search) ?>" placeholder="Search by make, model, color, or stock number" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="lg:w-64">
                    <label for="make" class="mb-1 block text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Make</label>
                    <select id="make" name="make" class="w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Makes</option>
                        <?php foreach ($makes as $makeOption): ?>
                            <option value="<?= htmlspecialchars($makeOption) ?>" <?= $make === $makeOption ? 'selected' : '' ?>><?= htmlspecialchars($makeOption) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="lg:self-end">
                    <button type="submit" class="w-full rounded-2xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow hover:bg-blue-500">Search</button>
                </div>
            </form>
        </section>

        <!-- Main Vehicle Inventory Section -->
        <section>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-slate-900">Vehicle Showroom</h2>
                    <p class="text-sm text-slate-500 mt-1">Hover over any vehicle to preview, or click to enlarge photos.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full bg-slate-200 px-4 py-1.5 text-xs font-bold text-slate-700">
                        <?= count($cars) ?> Vehicles Available
                    </span>
                </div>
            </div>

            <!-- Vehicle Card Grid -->
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($cars as $car): ?>
                <div class="car-card group flex flex-col justify-between overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200 transition-all duration-300 hover:shadow-xl hover:-translate-y-1.5">
                    
                    <!-- Hover Zoom Image Container -->
                    <div 
                        class="relative h-56 w-full overflow-hidden bg-slate-100 cursor-pointer"
                        onclick="openImageLightbox('<?= htmlspecialchars($car['image']) ?>', '<?= htmlspecialchars("{$car['year']} {$car['make']} {$car['model']}") ?>', '<?= htmlspecialchars($car['price']) ?>', '<?= htmlspecialchars($car['evaluation_grade']) ?>')"
                    >
                        <img 
                            src="<?= htmlspecialchars($car['image']) ?>" 
                            alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>" 
                            class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
                            onerror="this.src='https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?auto=format&fit=crop&w=800&q=80'"
                        >
                        <!-- Category Badge -->
                        <span class="absolute top-4 left-4 rounded-full bg-slate-950/80 backdrop-blur-md px-3 py-1 text-xs font-semibold text-white uppercase tracking-wider">
                            <?= htmlspecialchars($car['type'] ?? 'Sedan') ?>
                        </span>
                        <!-- Evaluation Grade Badge -->
                        <span class="absolute top-4 right-4 rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white shadow">
                            <?= htmlspecialchars($car['evaluation_grade'] ?? 'Certified') ?>
                        </span>
                        <!-- Hover Preview Indicator -->
                        <div class="preview-overlay pointer-events-none absolute inset-0 flex items-center justify-center bg-slate-950/40 opacity-0 transition-all duration-300 transform translate-y-2">
                            <span class="rounded-xl bg-white/95 px-4 py-2 text-xs font-bold text-slate-900 shadow-lg">
                                View Full Image 🔍
                            </span>
                        </div>
                    </div>

                    <!-- Vehicle Specification Body -->
                    <div class="p-6 flex flex-col flex-grow justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-xl font-bold text-slate-900"><?= htmlspecialchars($car['year']) ?> <?= htmlspecialchars($car['make']) ?> <?= htmlspecialchars($car['model']) ?></h3>
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold uppercase text-slate-600">Stock <?= (int)$car['stock_quantity'] ?></span>
                            </div>
                            <p class="mt-2 text-2xl font-extrabold text-blue-600"><?= htmlspecialchars($car['price']) ?></p>

                            <div class="mt-5 grid grid-cols-2 gap-2.5 border-t border-b border-slate-100 py-3.5 text-xs text-slate-600">
                                <div><strong>Color:</strong> <?= htmlspecialchars($car['color'] ?? 'White') ?></div>
                                <div><strong>Mileage:</strong> <?= htmlspecialchars($car['mileage'] ?? 'Low km') ?></div>
                                <div><strong>Transmission:</strong> <?= htmlspecialchars($car['transmission'] ?? 'Automatic') ?></div>
                                <div><strong>Fuel:</strong> <?= htmlspecialchars($car['fuel_type'] ?? 'Petrol') ?></div>
                            </div>
                        </div>

                        <button 
                            type="button"
                            class="purchase-trigger mt-6 w-full rounded-2xl bg-slate-900 py-3.5 text-sm font-bold text-white transition duration-200 hover:bg-slate-800"
                            data-car-id="<?= (int)$car['id'] ?>"
                            data-vehicle="<?= htmlspecialchars("{$car['year']} {$car['make']} {$car['model']}") ?>"
                            data-price="<?= htmlspecialchars($car['price']) ?>"
                        >
                            Purchase Now
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Modal 1: Consignment Sell Vehicle Submission -->
    <div id="sellModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
        <div class="w-full max-w-lg rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-200">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">List Car for Consignment</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Schedules a mechanic evaluation & assigns a sales representative.</p>
                </div>
                <button type="button" onclick="document.getElementById('sellModal').classList.add('hidden')" class="rounded-full bg-slate-100 p-2 text-slate-500 hover:bg-slate-200 text-sm">✕</button>
            </div>

            <form id="sellForm" class="mt-6 space-y-4">
                <input type="hidden" name="action" value="list_car_for_sale">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-700 uppercase">Make</label>
                        <input name="make" placeholder="e.g. BMW" required class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 uppercase">Model</label>
                        <input name="model" placeholder="e.g. 320i" required class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-700 uppercase">Year</label>
                        <input name="year" type="number" min="2000" max="2026" value="2022" required class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-700 uppercase">Asking Price</label>
                        <input name="price" placeholder="e.g. R 350,000" required class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 uppercase">Vehicle Body Type</label>
                    <select name="type" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Sedan">Sedan</option>
                        <option value="SUV">SUV</option>
                        <option value="Hatchback">Hatchback</option>
                        <option value="Bakkie / Single Cab">Bakkie / Single Cab</option>
                        <option value="Bakkie / Double Cab">Bakkie / Double Cab</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('sellModal').classList.add('hidden')" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow hover:bg-blue-500 transition">
                        Submit Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Full-Size Image Preview Lightbox -->
    <div id="imageLightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/85 backdrop-blur-sm p-4" onclick="closeImageLightbox()">
        <div class="relative max-w-4xl w-full rounded-3xl bg-slate-900 overflow-hidden shadow-2xl p-4 text-white ring-1 ring-slate-800" onclick="event.stopPropagation()">
            <button onclick="closeImageLightbox()" class="absolute top-6 right-6 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-slate-800/80 text-white hover:bg-slate-700 transition">
                ✕
            </button>
            <div class="h-96 md:h-[28rem] w-full overflow-hidden rounded-2xl bg-slate-950">
                <img id="lightboxImage" src="" alt="Car Fullscreen Preview" class="h-full w-full object-cover">
            </div>
            <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between px-2 gap-2">
                <div>
                    <h3 id="lightboxTitle" class="text-2xl font-bold"></h3>
                    <p id="lightboxGrade" class="text-sm font-semibold text-emerald-400"></p>
                </div>
                <span id="lightboxPrice" class="text-3xl font-black text-blue-400"></span>
            </div>
        </div>
    </div>

    <!-- Toast Notification Banner -->
    <div id="toast" class="pointer-events-none fixed right-6 top-6 hidden w-80 rounded-3xl bg-slate-900 p-4 text-white shadow-2xl z-50 border border-slate-800 transition duration-300"></div>

    <script src="script.js"></script>
    <script>
        // Lightbox Functions
        function openImageLightbox(imgUrl, title, price, grade) {
            document.getElementById('lightboxImage').src = imgUrl;
            document.getElementById('lightboxTitle').textContent = title;
            document.getElementById('lightboxPrice').textContent = price;
            document.getElementById('lightboxGrade').textContent = 'Verification: ' + grade;
            
            const lightbox = document.getElementById('imageLightbox');
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
        }

        function closeImageLightbox() {
            const lightbox = document.getElementById('imageLightbox');
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
        }

        // Form submission handling for Sell Vehicle Consignment
        handleFormSubmit('sellForm', () => {
            document.getElementById('sellModal').classList.add('hidden');
            setTimeout(() => window.location.reload(), 1200);
        });

        // Purchase inquiry triggers
        document.querySelectorAll('.purchase-trigger').forEach(btn => {
            btn.addEventListener('click', async () => {
                const carId = btn.dataset.carId;
                const vehicle = btn.dataset.vehicle;
                const price = btn.dataset.price;

                if (!confirm(`Purchase ${vehicle} for ${price}?`)) {
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'purchase_vehicle');
                formData.append('car_id', carId);
                formData.append('amount', price);
                formData.append('payment_method', 'Card');

                try {
                    const response = await fetch('process.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    showToast(result.message || 'Purchase processed.');

                    if (result.status === 'success' && result.redirect) {
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 800);
                    }
                } catch (error) {
                    showToast('Unable to process purchase.');
                }
            });
        });
    </script>
</body>
</html>