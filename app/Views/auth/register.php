<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Register - Mimshack</title>
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
            <p class="text-xs font-bold text-on-surface-variant mt-2 uppercase tracking-widest">Create your social commerce account</p>
        </div>

        <?php if (isset($_SESSION['flash_messages']['errors']['value'])): ?>
            <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold rounded-2xl flex flex-col space-y-1 shadow-sm">
                <div class="flex items-center space-x-1 font-bold">
                    <span class="material-symbols-outlined text-rose-600 text-sm">error</span>
                    <span>Registration errors:</span>
                </div>
                <span><?= $_SESSION['flash_messages']['errors']['value'] ?></span>
            </div>
            <?php unset($_SESSION['flash_messages']['errors']); ?>
        <?php endif; ?>

        <form action="/auth/register" method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

            <div>
                <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-[#004D40] mb-1.5">Full Name</label>
                <input type="text" id="full_name" name="full_name" required class="w-full px-4 py-2.5 bg-[#F5F7F4] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none transition font-bold shadow-inner"/>
            </div>

            <div>
                <label for="username" class="block text-xs font-bold uppercase tracking-wider text-[#004D40] mb-1.5">Username</label>
                <input type="text" id="username" name="username" placeholder="alphanumeric & underscores" required class="w-full px-4 py-2.5 bg-[#F5F7F4] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none transition text-xs font-bold shadow-inner"/>
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-[#004D40] mb-1.5">Email Address</label>
                <input type="email" id="email" name="email" required class="w-full px-4 py-2.5 bg-[#F5F7F4] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none transition text-xs font-bold shadow-inner"/>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-[#004D40] mb-1.5">Password</label>
                <input type="password" id="password" name="password" required class="w-full px-4 py-2.5 bg-[#F5F7F4] border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none transition font-bold shadow-inner"/>
            </div>

            <button type="submit" class="w-full py-4 bg-[#004D40] text-white font-bold rounded-full hover:bg-[#00332A] transition flex justify-center items-center space-x-2 shadow-md">
                <span>Join Mimshack</span>
                <span class="material-symbols-outlined font-bold">person_add</span>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-[#E0E6E2] text-center">
            <p class="text-xs font-bold text-on-surface-variant">
                Already have an account?
                <a href="/auth/login" class="text-[#004D40] font-extrabold hover:underline">Sign In</a>
            </p>
        </div>
    </div>

</body>
</html>
