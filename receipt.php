<?php
session_start();
require_once 'db.php';

$transactionId = isset($_GET['transaction_id']) ? (int)$_GET['transaction_id'] : 0;
$receipt = null;

if ($transactionId > 0) {
    $stmt = $pdo->prepare(
        "SELECT t.id, t.amount, t.created_at, t.payment_method, t.status,
                v.stock_number, v.make, v.model, v.year, v.color, v.price,
                u.name AS customer_name, u.email AS customer_email
         FROM transactions t
         JOIN vehicles v ON v.id = t.vehicle_id
         JOIN users u ON u.id = t.user_id
         WHERE t.id = ?"
    );
    $stmt->execute([$transactionId]);
    $receipt = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMB Autos | Receipt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 p-4 text-slate-900">
    <div class="mx-auto max-w-3xl rounded-3xl bg-white shadow-xl ring-1 ring-slate-200">
        <?php if (!$receipt): ?>
            <div class="p-10 text-center">
                <h1 class="text-2xl font-bold text-slate-900">Receipt not found</h1>
                <p class="mt-3 text-slate-600">The transaction could not be located.</p>
                <a href="index.php" class="mt-6 inline-block rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white">Return Home</a>
            </div>
        <?php else: ?>
            <div class="p-8 md:p-10">
                <div class="flex flex-col gap-3 border-b border-slate-200 pb-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">BMB Autos</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight">Purchase Receipt</h1>
                    </div>
                    <div class="text-left md:text-right">
                        <p class="text-sm text-slate-500">Receipt #<?= htmlspecialchars($receipt['id']) ?></p>
                        <p class="text-sm text-slate-500"><?= htmlspecialchars(date('d M Y', strtotime($receipt['created_at']))) ?></p>
                    </div>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Customer</p>
                        <p class="mt-3 text-lg font-bold"><?= htmlspecialchars($receipt['customer_name']) ?></p>
                        <p class="text-sm text-slate-600"><?= htmlspecialchars($receipt['customer_email']) ?></p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Payment</p>
                        <p class="mt-3 text-lg font-bold"><?= htmlspecialchars($receipt['payment_method']) ?></p>
                        <p class="text-sm text-slate-600">Status: <?= htmlspecialchars($receipt['status']) ?></p>
                    </div>
                </div>

                <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-900 text-white">
                            <tr>
                                <th class="px-5 py-3">Vehicle</th>
                                <th class="px-5 py-3">Stock #</th>
                                <th class="px-5 py-3">Color</th>
                                <th class="px-5 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white">
                                <td class="px-5 py-4 font-semibold"><?= htmlspecialchars($receipt['year']) . ' ' . htmlspecialchars($receipt['make']) . ' ' . htmlspecialchars($receipt['model']) ?></td>
                                <td class="px-5 py-4"><?= htmlspecialchars($receipt['stock_number']) ?></td>
                                <td class="px-5 py-4"><?= htmlspecialchars($receipt['color']) ?></td>
                                <td class="px-5 py-4 text-right font-black text-blue-600"><?= htmlspecialchars($receipt['amount']) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 flex flex-col items-end gap-3 text-sm">
                    <div class="flex w-full max-w-xs justify-between border-t border-slate-200 pt-4">
                        <span class="text-slate-600">Total Paid</span>
                        <span class="text-xl font-black text-slate-900"><?= htmlspecialchars($receipt['amount']) ?></span>
                    </div>
                </div>

                <div class="no-print mt-8 flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <a href="index.php" class="rounded-xl bg-slate-100 px-5 py-3 text-center font-semibold text-slate-800">Back to Home</a>
                    <button type="button" onclick="window.print()" class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white shadow hover:bg-blue-500">Print Receipt</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
