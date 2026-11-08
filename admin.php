<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$staff = $pdo->query("SELECT * FROM users WHERE role IN ('mechanic', 'sales_rep') ORDER BY role ASC")->fetchAll();
$allBookings = $pdo->query("SELECT b.*, u.name as customer_name, m.name as mechanic_name FROM bookings b JOIN users u ON b.customer_id = u.id LEFT JOIN users m ON b.mechanic_id = m.id ORDER BY b.booking_date DESC")->fetchAll();
$complaints = $pdo->query("SELECT c.*, u.name as customer_name FROM complaints c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.id DESC")->fetchAll();
$inventory = $pdo->query("SELECT * FROM vehicles ORDER BY status DESC, make ASC, model ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMB Autos | Master Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <?php include 'nav.php'; ?>

    <main class="mx-auto max-w-7xl px-4 sm:px-6 py-6 sm:py-10 space-y-6 sm:space-y-10">
        <!-- Inventory Management Section -->
        <section class="rounded-3xl bg-white p-4 sm:p-8 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-col gap-4">
                <div>
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold uppercase text-indigo-800">Admin Control</span>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Inventory Stock Management</h2>
                    <p class="text-sm text-slate-500">Adjust stock levels for each vehicle. Setting stock to 0 automatically marks the vehicle as Sold.</p>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-950 text-white">
                        <tr>
                            <th class="px-3 py-3 sm:px-6">Stock #</th>
                            <th class="px-3 py-3 sm:px-6">Vehicle</th>
                            <th class="px-3 py-3 sm:px-6">Color</th>
                            <th class="px-3 py-3 sm:px-6">Status</th>
                            <th class="px-3 py-3 sm:px-6">Current Stock</th>
                            <th class="px-3 py-3 sm:px-6">Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($inventory as $vehicle): ?>
                        <tr>
                            <td class="px-3 py-4 sm:px-6 font-semibold"><?= htmlspecialchars($vehicle['stock_number']) ?></td>
                            <td class="px-3 py-4 sm:px-6"><?= htmlspecialchars($vehicle['year']) ?> <?= htmlspecialchars($vehicle['make']) ?> <?= htmlspecialchars($vehicle['model']) ?></td>
                            <td class="px-3 py-4 sm:px-6"><?= htmlspecialchars($vehicle['color'] ?? 'White') ?></td>
                            <td class="px-3 py-4 sm:px-6">
                                <span class="rounded-full px-3 py-1 text-xs font-bold <?= $vehicle['status'] === 'Sold' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                    <?= htmlspecialchars($vehicle['status']) ?>
                                </span>
                            </td>
                            <td class="px-3 py-4 sm:px-6 font-bold text-slate-800"><?= (int)$vehicle['stock_quantity'] ?></td>
                            <td class="px-3 py-4 sm:px-6">
                                <!-- Form now has a specific class for JS targeting -->
                                <form method="POST" action="process.php" class="update-stock-form flex flex-row items-center gap-2">
                                    <input type="hidden" name="action" value="update_stock">
                                    <input type="hidden" name="vehicle_id" value="<?= (int)$vehicle['id'] ?>">
                                    <input type="number" min="0" name="stock_quantity" value="<?= (int)$vehicle['stock_quantity'] ?>" class="w-20 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-500">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Staff Management Section (CRUD) -->
        <section class="rounded-3xl bg-white p-4 sm:p-8 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                <div>
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold uppercase text-indigo-800">Admin Control</span>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Mechanic & Sales Rep Management</h2>
                    <p class="text-sm text-slate-500">Create, view, and delete operational staff accounts.</p>
                </div>
                <button onclick="document.getElementById('staffModal').classList.remove('hidden')" class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-500 shadow">+ Add New Staff</button>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-950 text-white">
                        <tr>
                            <th class="px-3 py-3 sm:px-6">ID</th>
                            <th class="px-3 py-3 sm:px-6">Name</th>
                            <th class="px-3 py-3 sm:px-6">Email</th>
                            <th class="px-3 py-3 sm:px-6">Role</th>
                            <th class="px-3 py-3 sm:px-6">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($staff as $s): ?>
                        <tr>
                            <td class="px-3 py-4 sm:px-6">#<?= $s['id'] ?></td>
                            <td class="px-3 py-4 sm:px-6 font-semibold"><?= htmlspecialchars($s['name']) ?></td>
                            <td class="px-3 py-4 sm:px-6 text-slate-600"><?= htmlspecialchars($s['email']) ?></td>
                            <td class="px-3 py-4 sm:px-6">
                                <span class="rounded-full px-3 py-1 text-xs font-bold uppercase <?= $s['role'] === 'mechanic' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
                                    <?= htmlspecialchars($s['role']) ?>
                                </span>
                            </td>
                            <td class="px-3 py-4 sm:px-6">
                                <button onclick="deleteStaff(<?= $s['id'] ?>)" class="font-semibold text-red-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Master Mechanics Booking Schedule -->
        <section class="rounded-3xl bg-white p-4 sm:p-8 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-2xl font-bold text-slate-900">Master Service & Evaluation Schedule</h2>
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-3 py-3 sm:px-6">Booking ID</th>
                            <th class="px-3 py-3 sm:px-6">Customer</th>
                            <th class="px-3 py-3 sm:px-6">Vehicle</th>
                            <th class="px-3 py-3 sm:px-6">Service Type</th>
                            <th class="px-3 py-3 sm:px-6">Assigned Mechanic</th>
                            <th class="px-3 py-3 sm:px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($allBookings as $b): ?>
                        <tr>
                            <td class="px-3 py-4 sm:px-6 font-semibold">#<?= $b['id'] ?></td>
                            <td class="px-3 py-4 sm:px-6"><?= htmlspecialchars($b['customer_name']) ?></td>
                            <td class="px-3 py-4 sm:px-6"><?= htmlspecialchars($b['vehicle_info']) ?></td>
                            <td class="px-3 py-4 sm:px-6"><?= htmlspecialchars($b['service_type']) ?></td>
                            <td class="px-3 py-4 sm:px-6 font-bold text-blue-600"><?= htmlspecialchars($b['mechanic_name'] ?? 'Unassigned') ?></td>
                            <td class="px-3 py-4 sm:px-6">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold"><?= htmlspecialchars($b['status']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Customer Complaints -->
        <section class="rounded-3xl bg-white p-4 sm:p-8 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-2xl font-bold text-slate-900">Customer Support Tickets</h2>
            <div class="mt-6 space-y-4">
                <?php foreach ($complaints as $c): ?>
                <div class="rounded-2xl border p-4 bg-slate-50 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-800"><?= htmlspecialchars($c['category']) ?></span>
                        <p class="mt-2 text-sm text-slate-800 font-medium">"<?= htmlspecialchars($c['message']) ?>"</p>
                        <p class="mt-1 text-xs text-slate-500">Submitted by: <?= htmlspecialchars($c['customer_name'] ?? 'Anonymous') ?> on <?= $c['created_at'] ?></p>
                    </div>
                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold"><?= htmlspecialchars($c['status']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Staff Modal -->
    <div id="staffModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4">
        <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
            <h3 class="text-xl font-bold">Add Staff Account</h3>
            <p class="text-xs text-slate-500 mt-1">Assign credentials for mechanics or sales representatives.</p>
            <form id="staffForm" class="mt-4 space-y-4">
                <input type="hidden" name="action" value="admin_manage_staff">
                <input type="hidden" name="sub_action" value="create">
                <div>
                    <label class="text-xs font-semibold">Full Name</label>
                    <input name="name" required placeholder="Staff member name" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold">Email Address</label>
                    <input name="email" type="email" required placeholder="staff@bmbautos.co.za" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold">Password</label>
                    <input name="password" type="password" required placeholder="Set login password" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold">Assign Role</label>
                    <select name="role" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm">
                        <option value="mechanic">Mechanic</option>
                        <option value="sales_rep">Sales Representative</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('staffModal').classList.add('hidden')" class="rounded-xl px-4 py-2 bg-slate-100 text-sm font-semibold">Cancel</button>
                    <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-bold text-white shadow hover:bg-blue-500">Create Staff</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="pointer-events-none fixed right-6 top-6 hidden w-80 rounded-3xl bg-slate-900 p-4 text-white shadow-xl z-50"></div>
    <script src="script.js"></script>
    <script>
        handleFormSubmit('staffForm', () => {
            document.getElementById('staffModal').classList.add('hidden');
            setTimeout(() => window.location.reload(), 800);
        });

        // FIXED: Selects the correct class and handles the AJAX update
        document.querySelectorAll('.update-stock-form').forEach(form => {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                const formData = new FormData(form);
                const response = await fetch('process.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                // Uses the showToast function from script.js
                if (typeof showToast === 'function') {
                    showToast(result.message);
                } else {
                    alert(result.message);
                }
                
                if (result.status === 'success') {
                    setTimeout(() => window.location.reload(), 800);
                }
            });
        });
    </script>
</body>
</html>