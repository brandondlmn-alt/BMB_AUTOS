<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMB Autos | Sign In & Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 flex items-center justify-center p-6">
    <div class="w-full max-w-md rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-2xl">
        <div class="text-center mb-6">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 font-bold text-white text-xl">B</div>
            <h1 class="text-2xl font-bold text-slate-900">BMB Autos Portal</h1>
            <p class="text-sm text-slate-500 mt-1">Sign in or register your customer account</p>
        </div>

        <!-- Tab Controls -->
        <div class="flex rounded-2xl bg-slate-100 p-1 mb-6 text-sm font-medium">
            <button id="tabLogin" onclick="switchAuthTab('login')" class="w-1/2 rounded-xl bg-white py-2 shadow-sm text-slate-900">Sign In</button>
            <button id="tabRegister" onclick="switchAuthTab('register')" class="w-1/2 rounded-xl py-2 text-slate-500 hover:text-slate-900">Register</button>
        </div>

        <!-- Login Form -->
        <form id="loginForm" class="space-y-4">
            <input type="hidden" name="action" value="login">
            <div>
                <label class="text-xs font-semibold text-slate-700">Email Address</label>
                <input name="email" type="email" required placeholder="name@domain.co.za" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-700">Password</label>
                <input name="password" type="password" required placeholder="Enter password" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full rounded-2xl bg-blue-600 py-3 font-semibold text-white shadow hover:bg-blue-500">Sign In</button>
        </form>

        <!-- Register Form -->
        <form id="registerForm" class="space-y-4 hidden">
            <input type="hidden" name="action" value="register_customer">
            <div>
                <label class="text-xs font-semibold text-slate-700">Full Name</label>
                <input name="name" type="text" required placeholder="e.g. John Doe" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-700">Email Address</label>
                <input name="email" type="email" required placeholder="john@domain.co.za" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-700">Password</label>
                <input name="password" type="password" required placeholder="Create password" class="mt-1 w-full rounded-2xl border bg-slate-50 p-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full rounded-2xl bg-emerald-600 py-3 font-semibold text-white shadow hover:bg-emerald-500">Create Account</button>
        </form>

        <!-- Role Credentials Reference -->
        <div class="mt-8 rounded-2xl bg-slate-50 p-4 border border-slate-200 text-xs text-slate-600 space-y-1">
            <p class="font-bold text-slate-800 uppercase tracking-wider">Demo Credentials:</p>
            <p><strong>Admin:</strong> admin@bmbautos.co.za / admin123</p>
            <p><strong>Mechanic:</strong> mechanic@bmbautos.co.za / mech123</p>
            <p><strong>Sales Rep:</strong> sales@bmbautos.co.za / sales123</p>
            <p><strong>Customer:</strong> thabo@bmbautos.co.za / welcome123</p>
        </div>
    </div>

    <div id="toast" class="pointer-events-none fixed right-6 top-6 hidden w-80 rounded-3xl bg-slate-900 p-4 text-white shadow-xl"></div>
    <script src="script.js"></script>
    <script>
        function switchAuthTab(tab) {
            const loginForm = document.getElementById('loginForm');
            const regForm = document.getElementById('registerForm');
            const tabLogin = document.getElementById('tabLogin');
            const tabRegister = document.getElementById('tabRegister');

            if (tab === 'login') {
                loginForm.classList.remove('hidden');
                regForm.classList.add('hidden');
                tabLogin.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
                tabLogin.classList.remove('text-slate-500');
                tabRegister.classList.remove('bg-white', 'text-slate-900', 'shadow-sm');
                tabRegister.classList.add('text-slate-500');
            } else {
                loginForm.classList.add('hidden');
                regForm.classList.remove('hidden');
                tabRegister.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
                tabRegister.classList.remove('text-slate-500');
                tabLogin.classList.remove('bg-white', 'text-slate-900', 'shadow-sm');
                tabLogin.classList.add('text-slate-500');
            }
        }
        handleFormSubmit('registerForm', () => switchAuthTab('login'));
    </script>
</body>
</html>