<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BMB Autos | My Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <?php include 'nav.php'; ?>
    <main class="mx-auto max-w-2xl px-6 py-10">
        <div class="rounded-3xl bg-white p-8 shadow-xl ring-1 ring-slate-200">
            <h1 class="text-2xl font-bold">Account Management</h1>
            <p class="text-sm text-slate-600 mt-1">Manage your personal credentials and booking history.</p>

            <form id="profileForm" class="mt-6 space-y-4">
                <input type="hidden" name="action" value="update_profile">
                <div>
                    <label class="text-sm font-medium">Full Name</label>
                    <input name="name" value="<?= htmlspecialchars($user['name']) ?>" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3">
                </div>
                <div>
                    <label class="text-sm font-medium">Email Address</label>
                    <input name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3">
                </div>
                <button type="submit" class="w-full rounded-2xl bg-blue-600 py-3 font-semibold text-white hover:bg-blue-500">Update Account</button>
            </form>

            <hr class="my-8 border-slate-200">

            <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
                <h3 class="font-semibold text-red-900">Danger Zone</h3>
                <p class="text-xs text-red-700 mt-1">Permanently remove your profile and clear current bookings.</p>
                <button onclick="deleteAccount()" class="mt-4 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Delete My Account</button>
            </div>
        </div>
    </main>
    <div id="toast" class="pointer-events-none fixed right-6 top-6 hidden w-80 rounded-3xl bg-slate-900 p-4 text-white"></div>
    <script src="script.js"></script>
    <script>
        function deleteAccount() {
            if (!confirm('Are you sure you want to permanently delete your account?')) return;
            const fd = new FormData();
            fd.append('action', 'delete_account');
            fetch('process.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    showToast(d.message);
                    setTimeout(() => window.location.href = 'logout.php', 1000);
                });
        }
    </script>
</body>
</html>