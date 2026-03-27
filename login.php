<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();
if ($currentUser) {
    header('Location: home.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUTCU E-Library | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { heading: ['Montserrat', 'sans-serif'], body: ['Lato', 'sans-serif'] }, colors: { brand: { 900: '#0f172a', 800: '#1e293b', 50: '#f8fafc' }, accent: { 500: '#f97316', 600: '#ea580c' } } } } }
    </script>
</head>
<body class="font-body bg-brand-50 min-h-screen flex flex-col text-slate-800">
    
    <!-- Minimal Header for Auth Pages -->
    <nav class="absolute top-0 w-full z-50 py-6 px-8">
        <a class="flex items-center text-brand-900 hover:text-brand-800 text-xl font-extrabold font-heading tracking-tight no-underline" href="home.php">
            <i class="bi bi-book-half text-accent-500 mr-2 text-2xl"></i> MUTCU Library
        </a>
    </nav>

    <main class="flex-grow flex items-center justify-center p-4 py-20 relative overflow-hidden">
        <!-- Abstract Background Shapes -->
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-accent-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-brand-900/5 rounded-full blur-3xl"></div>

        <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-3xl shadow-2xl shadow-slate-200/50 relative z-10 border border-slate-100">
            
            <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-6 text-sm font-semibold flex items-center">
                <i class="bi bi-exclamation-triangle-fill mr-2"></i> <?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            </div>
            <?php endif; ?>

            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-brand-50 text-brand-900 rounded-full mb-4 border border-slate-100 shadow-inner">
                    <i class="bi bi-person-lock text-4xl"></i>
                </div>
                <h3 class="font-heading font-extrabold text-3xl text-brand-900 mb-2">Welcome Back</h3>
                <p class="text-slate-500">Sign in to access secure educational resources.</p>
            </div>

            <form method="post" action="actions.php">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="return_url" value="home.php">
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>
                    <input type="email" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-accent-500 focus:border-accent-500 outline-none transition-all text-slate-700 font-medium" name="email" placeholder="name@example.com" required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                    <input type="password" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-accent-500 focus:border-accent-500 outline-none transition-all text-slate-700 font-medium" name="password" placeholder="••••••••" required>
                </div>
                
                <div class="flex justify-between items-center mb-8 text-sm font-semibold">
                    <label class="flex items-center text-slate-500 cursor-pointer">
                        <input type="checkbox" class="mr-2 w-4 h-4 rounded border-slate-300 text-accent-500 focus:ring-accent-500 cursor-pointer"> Remember me
                    </label>
                    <a href="#" class="text-accent-500 hover:text-accent-600 transition-colors no-underline">Forgot password?</a>
                </div>
                
                <button type="submit" class="w-full py-3.5 bg-brand-900 hover:bg-brand-800 text-white rounded-xl font-bold text-lg transition-colors shadow-lg shadow-brand-900/20 mb-6">
                    Login to Dashboard
                </button>
                
                <div class="text-center text-sm font-medium text-slate-500">
                    Don't have an account? <a href="register.php" class="text-accent-500 hover:text-accent-600 font-bold no-underline ml-1">Register here</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>