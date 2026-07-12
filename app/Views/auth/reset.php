<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Reset Password - Mimshack</title>
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
            <p class="text-sm text-on-surface-variant mt-2 font-geist">Set your new account password</p>
        </div>

        <?php if (isset($_SESSION['flash_messages']['error']['value'])): ?>
            <div class="mb-4 p-4 bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-2xl flex items-center space-x-2">
                <span class="material-symbols-outlined">error</span>
                <span><?= htmlspecialchars($_SESSION['flash_messages']['error']['value']) ?></span>
            </div>
            <?php unset($_SESSION['flash_messages']['error']); ?>
        <?php endif; ?>

        <form action="/auth/reset-password" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>"/>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-2">Email Address</label>
                <div class="w-full px-4 py-3 bg-background border border-white/5 rounded-2xl text-on-surface-variant text-sm font-medium">
                    <?= htmlspecialchars($email) ?>
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-2">New Password</label>
                <input type="password" id="password" name="password" required class="w-full px-4 py-3 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"/>
            </div>

            <button type="submit" class="w-full py-4 bg-primary text-on-primary font-bold rounded-full hover:opacity-95 transition flex justify-center items-center space-x-2 shadow-lg shadow-primary/20">
                <span>Save New Password</span>
                <span class="material-symbols-outlined">save</span>
            </button>
        </form>
    </div>

</body>
</html>
