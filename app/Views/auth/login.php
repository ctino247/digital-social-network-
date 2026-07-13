<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login - Mimshack</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                "primary": "#004D40",
                "on-primary": "#FFFFFF",
                "background": "#F5F7F4",
                "surface": "#FFFFFF",
                "on-background": "#0F211C",
                "on-surface": "#0F211C",
                "on-surface-variant": "#536460",
                "outline": "#E0E6E2"
              }
            }
          }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #F5F7F4 0%, #E9EEEA 100%);
        }
    </style>
</head>
<body class="bg-[#F5F7F4] text-[#0F211C] flex flex-col justify-center items-center min-h-screen p-6">

    <div class="w-full max-w-md bg-white p-8 rounded-3xl border border-[#E0E6E2] shadow-xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-[#004D40] flex items-center justify-center space-x-2">
                <span class="w-2.5 h-6 bg-[#FFE500] rounded-full inline-block"></span>
                <span>Mimshack</span>
            </h1>
            <p class="text-xs font-bold text-on-surface-variant mt-2 uppercase tracking-widest">Sign in to your social commerce space</p>
        </div>

        <?php if (isset($_SESSION['flash_messages']['error']['value'])): ?>
            <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold rounded-2xl flex items-center space-x-2 shadow-sm">
                <span class="material-symbols-outlined text-rose-600">error</span>
                <span><?= htmlspecialchars($_SESSION['flash_messages']['error']['value']) ?></span>
            </div>
            <?php unset($_SESSION['flash_messages']['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_messages']['success']['value'])): ?>
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-2xl flex items-center space-x-2 shadow-sm">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span><?= htmlspecialchars($_SESSION['flash_messages']['success']['value']) ?></span>
            </div>
            <?php unset($_SESSION['flash_messages']['success']); ?>
        <?php endif; ?>

        <form action="/auth/login" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

            <div>
                <label for="login" class="block text-xs font-bold uppercase tracking-wider text-[#004D40] mb-2">Username or Email</label>
                <input type="text" id="login" name="login" required class="w-full px-4 py-3 bg-[#F5F7F4] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none transition font-bold shadow-inner"/>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#004D40]">Password</label>
                    <a href="/auth/forgot-password" class="text-xs text-[#004D40] font-extrabold hover:underline">Forgot?</a>
                </div>
                <input type="password" id="password" name="password" required class="w-full px-4 py-3 bg-[#F5F7F4] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none transition font-bold shadow-inner"/>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 bg-white border border-[#E0E6E2] rounded text-[#004D40] focus:ring-[#004D40]"/>
                <label for="remember" class="ml-2.5 text-xs text-on-surface-variant font-bold">Remember me</label>
            </div>

            <button type="submit" class="w-full py-4 bg-[#004D40] text-white font-bold rounded-full hover:bg-[#00332A] transition flex justify-center items-center space-x-2 shadow-md">
                <span>Sign In</span>
                <span class="material-symbols-outlined font-bold">arrow_forward</span>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-[#E0E6E2] text-center">
            <p class="text-xs font-bold text-on-surface-variant">
                New to Mimshack?
                <a href="/auth/register" class="text-[#004D40] font-extrabold hover:underline">Create an account</a>
            </p>
        </div>
    </div>

</body>
</html>
