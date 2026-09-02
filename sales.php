<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'sales_rep') {
    header('Location: login.php');
    exit;
}

$vehicles = $pdo->query("SELECT v.*, u.name as seller_name FROM vehicles v LEFT JOIN users u ON v.seller_id = u.id ORDER BY v.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BMB Autos | Sales Representative Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <?php include 'nav.php'; ?>

    <main class="mx-auto max-w-7xl px-6 py-10 space-y-8">
        <div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase text-emerald-800">Sales Representative</span>
            <h1 class="mt-2 text-3xl font-bold">Consignment Pipeline & Inventory Control</h1>
            <p class="text-sm text-slate-600 mt-1">Review cars evaluated by mechanics, set showroom pricing, and publish vehicles to customers.</p>

            <div class="mt-8 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-950 text-white">
                        <tr>
                            <th class="px-6 py-4">Vehicle</th>
                            <th class="px-6 py-4">Seller</th>
                            <th class="px-6 py-4">Mechanic Evaluation</th>
                            <th class="px-6 py-4">Listing Price</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($vehicles as $v): ?>
                        <tr>
                            <td class="px-6 py-4 font-bold"><?= "{$v['year']} {$v['make']} {$v['model']}" ?></td>
                            <td class="px-6 py-4 text-slate-600"><?= htmlspecialchars($v['seller_name'] ?? 'Store Direct') ?></td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800"><?= htmlspecialchars($v['evaluation_grade']) ?></span>
                            </td>
                            <td class="px-6 py-4 font-semibold"><?= htmlspecialchars($v['price']) ?></td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-bold <?= $v['status'] === 'Available' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                    <?= htmlspecialchars($v['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <form class="salesPublishForm flex items-center gap-2">
                                    <input type="hidden" name="action" value="sales_publish_car">
                                    <input type="hidden" name="vehicle_id" value="<?= $v['id'] ?>">
                                    <input name="final_price" value="<?= htmlspecialchars($v['price']) ?>" class="w-28 rounded-lg border p-1.5 text-xs font-medium">
                                    <select name="status" class="rounded-lg border p-1.5 text-xs">
                                        <option value="Available" <?= $v['status'] === 'Available' ? 'selected' : '' ?>>Publish (Available)</option>
                                        <option value="Pending Evaluation" <?= $v['status'] === 'Pending Evaluation' ? 'selected' : '' ?>>Under Evaluation</option>
                                        <option value="Sold" <?= $v['status'] === 'Sold' ? 'selected' : '' ?>>Mark Sold</option>
                                    </select>
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-500">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="toast" class="pointer-events-none fixed right-6 top-6 hidden w-80 rounded-3xl bg-slate-900 p-4 text-white shadow-xl z-50"></div>
    <script src="script.js"></script>
    <script>
        document.querySelectorAll('.salesPublishForm').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const res = await fetch('process.php', { method: 'POST', body: new FormData(form) });
                const data = await res.json();
                showToast(data.message);
                setTimeout(() => window.location.reload(), 800);
            });
        });
    </script>
</body>
</html>