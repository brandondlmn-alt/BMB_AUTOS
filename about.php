<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BMB Autos | About Us</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <?php include 'nav.php'; ?>
    <main class="mx-auto max-w-4xl px-6 py-12 space-y-8">
        <section class="rounded-3xl bg-white p-10 shadow-sm ring-1 ring-slate-200">
            <span class="text-xs font-semibold uppercase tracking-widest text-blue-600">Our Story</span>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Why We Started BMB Autos</h1>
            <p class="mt-4 text-slate-600 leading-relaxed">
                BMB Autos was founded to eliminate the friction between vehicle maintenance, certified appraisals, and private car sales. We bridge the gap between vehicle owners, expert mechanics, and sales representatives through a unified digital platform.
            </p>
            <div class="mt-8 grid md:grid-cols-3 gap-6">
                <div class="p-4 border rounded-2xl">
                    <h3 class="font-bold text-blue-600">Buy & Sell</h3>
                    <p class="text-sm text-slate-600 mt-2">Transparent consignments backed by verified inspections.</p>
                </div>
                <div class="p-4 border rounded-2xl">
                    <h3 class="font-bold text-blue-600">Certified Mechanics</h3>
                    <p class="text-sm text-slate-600 mt-2">Scheduled servicing, routine diagnostics, and evaluations.</p>
                </div>
                <div class="p-4 border rounded-2xl">
                    <h3 class="font-bold text-blue-600">Customer First</h3>
                    <p class="text-sm text-slate-600 mt-2">Dedicated support and resolution handling for every client.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>