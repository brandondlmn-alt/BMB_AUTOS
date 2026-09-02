<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'mechanic') {
    header('Location: login.php');
    exit;
}

$mechanicId = $_SESSION['user']['id'];
$bookings = $pdo->prepare("SELECT b.*, u.name as customer_name, u.phone as customer_phone FROM bookings b JOIN users u ON b.customer_id = u.id WHERE b.mechanic_id = ? ORDER BY b.booking_date ASC");
$bookings->execute([$mechanicId]);
$jobs = $bookings->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BMB Autos | Mechanic Workbench</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <?php include 'nav.php'; ?>

    <main class="mx-auto max-w-7xl px-6 py-10 space-y-8">
        <div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase text-amber-800">Mechanic Operations</span>
            <h1 class="mt-2 text-3xl font-bold">Assigned Servicing & Pre-Sale Evaluations</h1>
            <p class="text-sm text-slate-600 mt-1">Perform vehicle safety inspections, oil/brake servicing, and assign condition grades for sales listings.</p>

            <div class="mt-8 space-y-6">
                <?php foreach ($jobs as $job): ?>
                <div class="rounded-2xl border border-slate-200 p-6 bg-slate-50 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-blue-600 px-3 py-0.5 text-xs font-bold text-white"><?= htmlspecialchars($job['service_type']) ?></span>
                            <span class="text-xs text-slate-500">Booking #<?= $job['id'] ?> | Scheduled: <?= $job['booking_date'] ?> @ <?= substr($job['booking_time'], 0, 5) ?></span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900"><?= htmlspecialchars($job['vehicle_info']) ?></h3>
                        <p class="text-sm text-slate-600">Customer: <strong><?= htmlspecialchars($job['customer_name']) ?></strong> (<?= htmlspecialchars($job['customer_phone'] ?? 'N/A') ?>)</p>
                        <p class="text-xs text-slate-500 bg-white p-2 rounded-xl border">Diagnostic Notes: <?= htmlspecialchars($job['condition_notes']) ?></p>
                    </div>

                    <form class="mechanicUpdateForm flex flex-col sm:flex-row gap-3 items-end">
                        <input type="hidden" name="action" value="update_mechanic_job">
                        <input type="hidden" name="booking_id" value="<?= $job['id'] ?>">
                        <div>
                            <label class="text-xs font-semibold text-slate-600">Condition Grade / Status</label>
                            <select name="status" class="mt-1 block rounded-xl border bg-white p-2.5 text-sm">
                                <option value="Scheduled" <?= $job['status'] == 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                <option value="In Progress" <?= $job['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Evaluation Passed (Grade A)" <?= $job['status'] == 'Evaluation Passed (Grade A)' ? 'selected' : '' ?>>Passed (Grade A - Showroom Ready)</option>
                                <option value="Evaluation Passed (Grade B)" <?= $job['status'] == 'Evaluation Passed (Grade B)' ? 'selected' : '' ?>>Passed (Grade B - Minor Wear)</option>
                                <option value="Evaluation Failed" <?= $job['status'] == 'Evaluation Failed' ? 'selected' : '' ?>>Failed Inspection</option>
                                <option value="Completed" <?= $job['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-48">
                            <label class="text-xs font-semibold text-slate-600">Inspection Notes</label>
                            <input name="notes" placeholder="e.g. Engine 100%, brakes good" value="<?= htmlspecialchars($job['condition_notes']) ?>" class="mt-1 w-full rounded-xl border bg-white p-2.5 text-sm">
                        </div>
                        <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Save</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <div id="toast" class="pointer-events-none fixed right-6 top-6 hidden w-80 rounded-3xl bg-slate-900 p-4 text-white shadow-xl z-50"></div>
    <script src="script.js"></script>
    <script>
        document.querySelectorAll('.mechanicUpdateForm').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const res = await fetch('process.php', { method: 'POST', body: new FormData(form) });
                const data = await res.json();
                showToast(data.message);
            });
        });
    </script>
</body>
</html>