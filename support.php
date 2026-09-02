<?php
session_start();
require_once 'db.php';

$currentUser = $_SESSION['user'] ?? null;
$pastComplaints = [];

if ($currentUser) {
    $stmt = $pdo->prepare("SELECT * FROM complaints WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$currentUser['id']]);
    $pastComplaints = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMB Autos | Customer Support & Complaints</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <?php include 'nav.php'; ?>

    <main class="mx-auto max-w-6xl px-6 py-10 space-y-10">
        <div class="grid md:grid-cols-2 gap-8 items-start">
            <div class="space-y-6">
                <div>
                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold uppercase text-red-800">Support Desk</span>
                    <h1 class="mt-2 text-3xl font-bold">Customer Help & Dispute Resolution</h1>
                    <p class="mt-2 text-slate-600 text-sm">If you experienced an issue with workshop servicing, mechanic condition evaluation, or vehicle sales, lodge a formal ticket below.</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm space-y-3 text-sm text-slate-700">
                    <p><strong>Emergency Hotline:</strong> +27 (0) 11 555 0199</p>
                    <p><strong>WhatsApp Support:</strong> +27 (0) 82 000 0123</p>
                    <p><strong>Support Email:</strong> complaints@bmbautos.co.za</p>
                    <p><strong>Location:</strong> Braamfontein, Johannesburg, 2001</p>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-8 shadow-xl ring-1 ring-slate-200">
                <h2 class="text-xl font-bold">Lodge a Complaint</h2>
                <form id="complaintForm" class="mt-6 space-y-4">
                    <input type="hidden" name="action" value="file_complaint">
                    <div>
                        <label class="text-xs font-semibold text-slate-700">Complaint Category</label>
                        <select name="category" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm">
                            <option value="Mechanic Service Issue">Mechanic Servicing / Repair Quality</option>
                            <option value="Vehicle Evaluation Dispute">Vehicle Condition Evaluation Dispute</option>
                            <option value="Sales Delay">Sales Rep / Consignment Delay</option>
                            <option value="Billing & Charges">Billing & Invoice Inquiry</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700">Explain the Issue</label>
                        <textarea name="message" rows="4" placeholder="Provide full details regarding your vehicle or booking..." required class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm"></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-red-600 py-3 text-sm font-bold text-white hover:bg-red-500 shadow">Submit Ticket</button>
                </form>
            </div>
        </div>

        <?php if ($currentUser && count($pastComplaints) > 0): ?>
        <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-xl font-bold">Your Submitted Tickets</h2>
            <div class="mt-4 space-y-3">
                <?php foreach ($pastComplaints as $c): ?>
                <div class="rounded-2xl border p-4 bg-slate-50 flex justify-between items-start text-sm">
                    <div>
                        <span class="font-bold text-slate-900"><?= htmlspecialchars($c['category']) ?></span>
                        <p class="text-slate-600 mt-1"><?= htmlspecialchars($c['message']) ?></p>
                        <span class="text-xs text-slate-400">Filed on <?= $c['created_at'] ?></span>
                    </div>
                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold"><?= htmlspecialchars($c['status']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <div id="toast" class="pointer-events-none fixed right-6 top-6 hidden w-80 rounded-3xl bg-slate-900 p-4 text-white shadow-xl z-50"></div>
    <script src="script.js"></script>
    <script>
        handleFormSubmit('complaintForm', () => {
            setTimeout(() => window.location.reload(), 1000);
        });
    </script>
</body>
</html>