<?php
$currentUser = $_SESSION['user'] ?? null;
$role = $currentUser['role'] ?? 'guest';
?>
<header class="bg-slate-900 text-white shadow-md sticky top-0 z-40">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
        <a href="index.php" class="flex items-center gap-2">
            <div class="h-10 w-44">
                <img src="images/logo.svg" alt="BMB Autos" class="h-full w-auto object-contain" onerror="this.outerHTML='<span class=\'font-bold text-xl text-blue-500\'>BMB AUTOS</span>'">
            </div>
        </a>
        <nav class="hidden items-center gap-6 md:flex">
            <a href="index.php" class="hover:text-blue-400 font-medium text-sm transition">Home</a>
            <a href="cars.php" class="hover:text-blue-400 font-medium text-sm transition">Cars</a>
            <a href="servicing.php" class="hover:text-blue-400 font-medium text-sm transition">Servicing & Evaluation</a>
            <a href="about.php" class="hover:text-blue-400 font-medium text-sm transition">About Us</a>
            <a href="support.php" class="hover:text-blue-400 font-medium text-sm transition">Support</a>

            <?php if ($role === 'admin'): ?>
                <a href="admin.php" class="rounded-full bg-indigo-600 px-4 py-2 text-xs font-bold text-white uppercase tracking-wider hover:bg-indigo-500">Admin Panel</a>
            <?php elseif ($role === 'mechanic'): ?>
                <a href="mechanic.php" class="rounded-full bg-amber-600 px-4 py-2 text-xs font-bold text-white uppercase tracking-wider hover:bg-amber-500">Mechanic Deck</a>
            <?php elseif ($role === 'sales_rep'): ?>
                <a href="sales.php" class="rounded-full bg-emerald-600 px-4 py-2 text-xs font-bold text-white uppercase tracking-wider hover:bg-emerald-500">Sales Portal</a>
            <?php endif; ?>

            <?php if ($currentUser): ?>
                <a href="profile.php" class="hover:text-blue-400 text-xs font-semibold text-slate-300">Profile</a>
                <a href="logout.php" class="rounded-full border border-red-500/70 px-4 py-1.5 text-xs font-semibold text-red-300 hover:bg-red-600 hover:text-white transition">Logout (<?= htmlspecialchars($currentUser['name']) ?>)</a>
            <?php else: ?>
                <a href="login.php" class="rounded-full bg-blue-600 px-5 py-2 text-xs font-bold text-white hover:bg-blue-500 transition shadow">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>