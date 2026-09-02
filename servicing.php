<?php
session_start();
require_once 'db.php';

$currentUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMB Autos | Book Servicing & Evaluation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <?php include 'nav.php'; ?>

    <main class="mx-auto max-w-4xl px-6 py-10">
        <div class="rounded-3xl bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold uppercase text-blue-800">Workshop & Inspections</span>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Schedule Service or Evaluation</h1>
            <p class="mt-2 text-sm text-slate-600">Select a service package or choose pre-sale condition grading to sell your vehicle through our sales team.</p>

            <form id="serviceForm" class="mt-8 space-y-6">
                <input type="hidden" name="action" value="create_booking">
                
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700">Select Service Category</label>
                    <select name="service_type" class="mt-2 w-full rounded-2xl border bg-slate-50 p-3.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Pre-Sale Vehicle Condition Evaluation">Pre-Sale Vehicle Condition Evaluation (Mandatory for Resale)</option>
                        <option value="Oil & Filter Minor Service">Oil & Filter Minor Service</option>
                        <option value="Brake Inspection & Pad Replacement">Brake Inspection & Pad Replacement</option>
                        <option value="Major Diagnostic & Full Service">Major Diagnostic & Full Service</option>
                        <option value="Suspension & Safety Check">Suspension & Safety Check</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700">Vehicle Details (Year Make Model)</label>
                        <input name="vehicle" placeholder="e.g. 2021 Volkswagen Polo" required class="mt-2 w-full rounded-2xl border bg-slate-50 p-3.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-700">Preferred Date</label>
                        <input name="date" type="date" required min="<?= date('Y-m-d') ?>" class="mt-2 w-full rounded-2xl border bg-slate-50 p-3.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-700">Preferred Time Slot</label>
                    <select name="time" class="mt-2 w-full rounded-2xl border bg-slate-50 p-3.5 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="08:30:00">08:30 AM</option>
                        <option value="10:00:00">10:00 AM</option>
                        <option value="11:30:00">11:30 AM</option>
                        <option value="14:00:00">02:00 PM</option>
                        <option value="15:30:00">03:30 PM</option>
                    </select>
                </div>

                <button type="submit" class="w-full rounded-2xl bg-blue-600 py-4 font-bold text-white shadow-lg hover:bg-blue-500 transition">Confirm Booking</button>
            </form>
        </div>
    </main>

    <div id="toast" class="pointer-events-none fixed right-6 top-6 hidden w-80 rounded-3xl bg-slate-900 p-4 text-white shadow-xl z-50"></div>
    <script src="script.js"></script>
</body>
</html>