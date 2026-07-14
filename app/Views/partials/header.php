<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= isset($title) ? Security::e($title) . ' - Mimshack' : 'Mimshack - Premium Social Commerce Platform' ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Playfair+Display:ital,wght@0,600;0,700;1,600&amp;display=swap" rel="stylesheet"/>

    <script id="tailwind-config">
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                "primary": "#004D40", // Premium Deep Emerald Green
                "on-primary": "#FFFFFF",
                "primary-container": "#FFE500", // Soft Premium Yellow Highlights
                "on-primary-container": "#004D40",
                "background": "#F5F7F4", // Pale sage cream background
                "surface": "#FFFFFF", // Premium pure white cards
                "surface-container-lowest": "#FFFFFF",
                "surface-container-low": "#F9FAF9",
                "surface-container": "#FFFFFF",
                "surface-container-high": "#EBF0EC",
                "surface-container-highest": "#DEE3DF",
                "on-background": "#0F211C", // Deep emerald-charcoal primary text
                "on-surface": "#0F211C",
                "on-surface-variant": "#536460", // Muted moss gray subtext
                "outline": "#E0E6E2",
                "outline-variant": "#D0D6D2"
              },
              borderRadius: {
                '3xl': '24px',
                '4xl': '32px',
              }
            }
          }
        }
    </script>
    <style>
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body {
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #F5F7F4 0%, #E9EEEA 100%);
        }
        h1, h2, h3, h4, h5, h6, .font-editorial {
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.02em;
        }
        /* Custom elements for premium luxury styling */
        .card-premium {
            background: #FFFFFF;
            border: 1px solid #EAEFEA;
            box-shadow: 0 4px 20px -2px rgba(13, 30, 26, 0.04);
            border-radius: 24px;
        }
        .btn-premium-primary {
            background-color: #004D40;
            color: #FFFFFF;
            font-weight: 600;
            border-radius: 9999px;
            transition: all 0.2s ease-in-out;
        }
        .btn-premium-primary:hover {
            background-color: #00332A;
            transform: translateY(-1px);
        }
        .btn-premium-secondary {
            background-color: #FFE500;
            color: #004D40;
            font-weight: 700;
            border-radius: 9999px;
            transition: all 0.2s ease-in-out;
        }
        .btn-premium-secondary:hover {
            background-color: #E6CE00;
            transform: translateY(-1px);
        }

        /* Smooth Profile Tab Content transitions */
        .tab-content {
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 0.25s ease-out, transform 0.25s ease-out;
        }
        .tab-content:not(.hidden) {
            opacity: 1;
            transform: translateY(0);
        }

        /* Subtle Premium Animations & Liquid Glass effects */
        .liquid-glass {
            background: rgba(255, 255, 255, 0.65) !important;
            backdrop-filter: blur(16px) saturate(120%) !important;
            -webkit-backdrop-filter: blur(16px) saturate(120%) !important;
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
            box-shadow: 0 8px 32px 0 rgba(0, 77, 64, 0.05) !important;
        }

        .skeleton-pulse {
            background: linear-gradient(90deg, #EBF0EC 25%, #F5F7F4 50%, #EBF0EC 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite ease-in-out;
        }

        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Luxurious smooth slide-in for alerts/cards */
        .slide-up {
            animation: slide-up-anim 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slide-up-anim {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-[#F5F7F4] text-on-background min-h-screen pb-24 md:pb-0">

<!-- Mobile Header -->
<?php
$unreadCount = 0;
if ($currentUser) {
    try {
        $db = \App\Core\Database::connect();
        $stmtUnread = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :u AND is_read = 0");
        $stmtUnread->execute(['u' => $currentUser['id']]);
        $unreadCount = (int)$stmtUnread->fetchColumn();
    } catch (\Exception $e) {
        $unreadCount = 0;
    }
}
?>
<header class="fixed top-0 left-0 w-full z-50 flex justify-between items-center px-6 h-16 bg-white/90 backdrop-blur-xl border-b border-[#E0E6E2] md:hidden">
    <a href="/" class="text-2xl font-bold tracking-tight text-[#004D40] flex items-center space-x-2">
        <span class="w-2.5 h-6 bg-[#FFE500] rounded-full inline-block"></span>
        <span>Mimshack</span>
    </a>
    <div class="flex items-center space-x-4">
        <?php if ($currentUser): ?>
            <!-- Top Mobile Notification Bell -->
            <a href="/notifications" class="text-[#004D40] hover:scale-105 transition-transform relative flex items-center">
                <span class="material-symbols-outlined font-bold" style="<?= strpos($currentPath, '/notifications') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">notifications</span>
                <?php if ($unreadCount > 0): ?>
                    <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                <?php endif; ?>
            </a>
            <a href="/profile/<?= Security::e($currentUser['username']) ?>" class="text-[#004D40] hover:scale-105 transition-transform">
                <span class="material-symbols-outlined font-bold">person</span>
            </a>
        <?php else: ?>
            <a href="/auth/login" class="text-xs px-4 py-1.5 bg-[#004D40] text-white rounded-full font-bold">Login</a>
        <?php endif; ?>
    </div>
</header>

<!-- Desktop Sidebar (Pristine layout with refined margins and shadows) -->
<nav class="hidden md:flex fixed left-0 top-0 h-screen w-64 bg-white border-r border-[#E0E6E2] flex-col p-6 z-40">
    <a href="/" class="text-3xl font-extrabold tracking-tight text-[#004D40] mb-10 flex items-center space-x-2">
        <span class="w-3.5 h-8 bg-[#FFE500] rounded-full inline-block"></span>
        <span>Mimshack</span>
    </a>

    <?php
    $cartCount = 0;
    if ($currentUser) {
        try {
            $db = \App\Core\Database::connect();
            $stmtCartCount = $db->prepare("SELECT SUM(quantity) FROM shopping_cart WHERE user_id = :u");
            $stmtCartCount->execute(['u' => $currentUser['id']]);
            $cartCount = (int)$stmtCartCount->fetchColumn();
        } catch (\Exception $e) {
            $cartCount = 0;
        }
    } else {
        $sessionCart = $session->get('cart', []);
        foreach ($sessionCart as $item) {
            $cartCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
        }
    }
    ?>
    <div class="space-y-1.5 flex-1">
        <a class="flex items-center space-x-4 px-4 py-3 rounded-full transition-all <?= $currentPath === '/' ? 'bg-[#004D40] text-white font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-[#F5F7F4] hover:text-[#004D40]' ?>" href="/">
            <span class="material-symbols-outlined" style="<?= $currentPath === '/' ? "font-variation-settings: 'FILL' 1;" : "" ?>">home</span>
            <span>Home Feed</span>
        </a>
        <a class="flex items-center space-x-4 px-4 py-3 rounded-full transition-all <?= strpos($currentPath, '/explore') !== false ? 'bg-[#004D40] text-white font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-[#F5F7F4] hover:text-[#004D40]' ?>" href="/explore">
            <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/explore') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">explore</span>
            <span>Explore</span>
        </a>
        <a class="flex items-center space-x-4 px-4 py-3 rounded-full transition-all <?= strpos($currentPath, '/marketplace') !== false ? 'bg-[#004D40] text-white font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-[#F5F7F4] hover:text-[#004D40]' ?>" href="/marketplace">
            <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/marketplace') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">shopping_bag</span>
            <span>Marketplace</span>
        </a>
        <a class="flex items-center space-x-4 px-4 py-3 rounded-full transition-all <?= strpos($currentPath, '/cart') !== false ? 'bg-[#004D40] text-white font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-[#F5F7F4] hover:text-[#004D40]' ?>" href="/cart">
            <span class="material-symbols-outlined relative" style="<?= strpos($currentPath, '/cart') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">
                shopping_cart
                <?php if ($cartCount > 0): ?>
                    <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-red-500 text-white rounded-full text-[9px] font-extrabold flex items-center justify-center border-2 border-white"><?= $cartCount ?></span>
                <?php endif; ?>
            </span>
            <span>Cart</span>
        </a>

        <?php if ($currentUser): ?>
            <a class="flex items-center space-x-4 px-4 py-3 rounded-full transition-all <?= strpos($currentPath, '/messages') !== false ? 'bg-[#004D40] text-white font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-[#F5F7F4] hover:text-[#004D40]' ?>" href="/messages">
                <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/messages') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">chat</span>
                <span>Messages</span>
            </a>
            <a class="flex items-center space-x-4 px-4 py-3 rounded-full transition-all <?= strpos($currentPath, '/notifications') !== false ? 'bg-[#004D40] text-white font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-[#F5F7F4] hover:text-[#004D40]' ?>" href="/notifications">
                <span class="material-symbols-outlined relative" style="<?= strpos($currentPath, '/notifications') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">
                    notifications
                    <?php if ($unreadCount > 0): ?>
                        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full border border-white"></span>
                    <?php endif; ?>
                </span>
                <span>Notifications</span>
            </a>
            <a class="flex items-center space-x-4 px-4 py-3 rounded-full transition-all <?= strpos($currentPath, '/profile/' . $currentUser['username']) !== false ? 'bg-[#004D40] text-white font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-[#F5F7F4] hover:text-[#004D40]' ?>" href="/profile/<?= Security::e($currentUser['username']) ?>">
                <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/profile/' . $currentUser['username']) !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">person</span>
                <span>Profile</span>
            </a>
            <a class="flex items-center space-x-4 px-4 py-3 rounded-full transition-all <?= strpos($currentPath, '/wallet') !== false ? 'bg-[#004D40] text-white font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-[#F5F7F4] hover:text-[#004D40]' ?>" href="/wallet">
                <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/wallet') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">account_balance_wallet</span>
                <span>My Wallet</span>
            </a>

            <!-- Dynamic Role Based Links -->
            <?php if (in_array($currentUser['role'], ['creator', 'admin'])): ?>
                <a class="flex items-center space-x-4 px-4 py-3 rounded-full transition-all <?= strpos($currentPath, '/creator/dashboard') !== false ? 'bg-[#004D40] text-white font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-[#F5F7F4] hover:text-[#004D40]' ?>" href="/creator/dashboard">
                    <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/creator/dashboard') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">dashboard</span>
                    <span>Creator Panel</span>
                </a>
            <?php endif; ?>

            <?php if ($currentUser['role'] === 'admin'): ?>
                <a class="flex items-center space-x-4 px-4 py-3 rounded-full transition-all <?= strpos($currentPath, '/admin/dashboard') !== false ? 'bg-[#004D40] text-white font-semibold shadow-sm' : 'text-on-surface-variant hover:bg-[#F5F7F4] hover:text-[#004D40]' ?>" href="/admin/dashboard">
                    <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/admin/dashboard') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">admin_panel_settings</span>
                    <span>Admin Panel</span>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a class="flex items-center space-x-4 px-4 py-3 rounded-full text-on-surface-variant hover:bg-[#F5F7F4] hover:text-[#004D40] transition-all" href="/auth/login">
                <span class="material-symbols-outlined">login</span>
                <span>Login / Sign Up</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- User Section Bottom -->
    <div class="mt-auto border-t border-[#E0E6E2] pt-4">
        <?php if ($currentUser): ?>
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-[#004D40] flex items-center justify-center text-white font-bold overflow-hidden border border-[#004D40]/10 shadow-sm">
                    <?php if (!empty($currentUser['avatar_url'])): ?>
                        <img src="<?= Security::e($currentUser['avatar_url']) ?>" class="w-full h-full object-cover"/>
                    <?php else: ?>
                        <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="flex-1 truncate">
                    <p class="text-sm font-bold truncate text-[#004D40]"><?= Security::e($currentUser['full_name']) ?></p>
                    <p class="text-xs text-on-surface-variant truncate">@<?= Security::e($currentUser['username']) ?></p>
                </div>
            </div>
            <a href="/auth/logout" class="block w-full py-2.5 text-center rounded-full bg-[#F5F7F4] text-xs font-bold text-red-600 hover:bg-red-50 transition-colors">
                Logout
            </a>
        <?php else: ?>
            <a href="/auth/register" class="block w-full py-3 text-center rounded-full bg-[#004D40] text-white font-bold hover:opacity-95 transition-all shadow-md">
                Create Account
            </a>
        <?php endif; ?>
    </div>
</nav>

<!-- Main Area -->
<main class="pt-20 md:pt-10 md:pl-72 max-w-4xl xl:max-w-6xl 2xl:max-w-7xl mx-auto px-5 md:px-8">

    <!-- Flash Alerts -->
    <?php if ($session->getFlash('success')): ?>
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center space-x-2 shadow-sm">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            <span class="text-sm font-semibold"><?= $session->getFlash('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if ($session->getFlash('error')): ?>
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center space-x-2 shadow-sm">
            <span class="material-symbols-outlined text-rose-600">error</span>
            <span class="text-sm font-semibold"><?= $session->getFlash('error') ?></span>
        </div>
    <?php endif; ?>

    <?php if ($session->getFlash('errors')): ?>
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex flex-col space-y-1 shadow-sm">
            <div class="flex items-center space-x-2 mb-1">
                <span class="material-symbols-outlined text-rose-600">error</span>
                <span class="text-sm font-bold">Please correct the following errors:</span>
            </div>
            <span class="text-sm pl-7 font-medium"><?= $session->getFlash('errors') ?></span>
        </div>
    <?php endif; ?>
