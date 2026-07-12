<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Register - Mimshack</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              colors: {
                "primary": "#10B981", // Emerald Green
                "on-primary": "#064E3B",
                "background": "#131315",
                "surface": "#1F1F21",
                "surface-container-low": "#1B1B1D",
                "on-background": "#E4E2E4",
                "on-surface": "#E4E2E4",
                "on-surface-variant": "#C7C4D7",
                "outline": "#908FA0"
              }
            }
          }
        }
    </script>
</head>
<body class="bg-background text-on-background flex flex-col justify-center items-center min-h-screen p-6">

    <div class="w-full max-w-md bg-surface-container-low p-8 rounded-3xl border border-white/5 shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tighter text-primary font-geist">Mimshack</h1>
            <p class="text-sm text-on-surface-variant mt-2 font-geist">Create your social commerce account</p>
        </div>

        <?php if (isset($_SESSION['flash_messages']['errors']['value'])): ?>
            <div class="mb-4 p-4 bg-red-500/10 border border-red-500/20 text-red-400 text-xs rounded-2xl flex flex-col space-y-1">
                <div class="flex items-center space-x-1 font-bold">
                    <span class="material-symbols-outlined text-sm">error</span>
                    <span>Registration errors:</span>
                </div>
                <span><?= $_SESSION['flash_messages']['errors']['value'] ?></span>
            </div>
            <?php unset($_SESSION['flash_messages']['errors']); ?>
        <?php endif; ?>

        <form action="/auth/register" method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

            <div>
                <label for="full_name" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Full Name</label>
                <input type="text" id="full_name" name="full_name" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"/>
            </div>

            <div>
                <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Username</label>
                <input type="text" id="username" name="username" placeholder="only alphanumeric and underscores" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none transition text-sm"/>
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Email Address</label>
                <input type="email" id="email" name="email" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none transition text-sm"/>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Password</label>
                <input type="password" id="password" name="password" required class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"/>
            </div>

            <button type="submit" class="w-full py-4 bg-primary text-on-primary font-bold rounded-full hover:opacity-95 transition flex justify-center items-center space-x-2 shadow-lg shadow-primary/20">
                <span>Join Mimshack</span>
                <span class="material-symbols-outlined">person_add</span>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-white/5 text-center">
            <p class="text-sm text-on-surface-variant">
                Already have an account?
                <a href="/auth/login" class="text-primary font-semibold hover:underline">Sign In</a>
            </p>
        </div>
    </div>

</body>
</html>
