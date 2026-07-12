<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= isset($title) ? Security::e($title) . ' - Mimshack' : 'Mimshack - Social Commerce Platform' ?></title>
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
                "primary-container": "#D1FAE5",
                "on-primary-container": "#065F46",
                "background": "#131315",
                "surface": "#1F1F21",
                "surface-container-lowest": "#0E0E10",
                "surface-container-low": "#1B1B1D",
                "surface-container": "#1F1F21",
                "surface-container-high": "#2A2A2C",
                "surface-container-highest": "#353437",
                "on-background": "#E4E2E4",
                "on-surface": "#E4E2E4",
                "on-surface-variant": "#C7C4D7",
                "outline": "#908FA0",
                "outline-variant": "#464554"
              }
            }
          }
        }
    </script>
    <style>
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body { min-height: 100vh; font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-geist { font-family: 'Geist', sans-serif; }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen pb-24 md:pb-0">

<!-- Mobile Header -->
<header class="fixed top-0 left-0 w-full z-50 flex justify-between items-center px-5 h-16 bg-surface/80 backdrop-blur-xl border-b border-white/5 md:hidden">
    <a href="/" class="text-2xl font-bold tracking-tighter text-primary">Mimshack</a>
    <div class="flex items-center space-x-4">
        <?php if ($currentUser): ?>
            <a href="/profile/<?= Security::e($currentUser['username']) ?>" class="text-on-surface-variant hover:text-primary">
                <span class="material-symbols-outlined">person</span>
            </a>
        <?php else: ?>
            <a href="/auth/login" class="text-xs px-3 py-1 bg-primary text-on-primary rounded-full font-semibold">Login</a>
        <?php endif; ?>
    </div>
</header>

<!-- Desktop Sidebar (Hidden on Mobile) -->
<nav class="hidden md:flex fixed left-0 top-0 h-screen w-64 bg-surface-container-lowest border-r border-white/5 flex-col p-6 z-40">
    <a href="/" class="text-3xl font-bold tracking-tighter text-primary mb-10 block">Mimshack</a>

    <div class="space-y-2 flex-1">
        <a class="flex items-center space-x-4 p-3 rounded-full transition-all <?= $currentPath === '/' ? 'bg-primary-container/10 text-primary' : 'text-on-surface-variant/75 hover:text-primary' ?>" href="/">
            <span class="material-symbols-outlined" style="<?= $currentPath === '/' ? "font-variation-settings: 'FILL' 1;" : "" ?>">home</span>
            <span class="font-semibold">Home Feed</span>
        </a>
        <a class="flex items-center space-x-4 p-3 rounded-full transition-all <?= strpos($currentPath, '/explore') !== false ? 'bg-primary-container/10 text-primary' : 'text-on-surface-variant/75 hover:text-primary' ?>" href="/explore">
            <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/explore') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">explore</span>
            <span class="font-semibold">Explore</span>
        </a>
        <a class="flex items-center space-x-4 p-3 rounded-full transition-all <?= strpos($currentPath, '/marketplace') !== false ? 'bg-primary-container/10 text-primary' : 'text-on-surface-variant/75 hover:text-primary' ?>" href="/marketplace">
            <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/marketplace') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">shopping_bag</span>
            <span class="font-semibold">Marketplace</span>
        </a>

        <?php if ($currentUser): ?>
            <a class="flex items-center space-x-4 p-3 rounded-full transition-all <?= strpos($currentPath, '/messages') !== false ? 'bg-primary-container/10 text-primary' : 'text-on-surface-variant/75 hover:text-primary' ?>" href="/messages">
                <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/messages') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">chat</span>
                <span class="font-semibold">Messages</span>
            </a>
            <a class="flex items-center space-x-4 p-3 rounded-full transition-all <?= strpos($currentPath, '/notifications') !== false ? 'bg-primary-container/10 text-primary' : 'text-on-surface-variant/75 hover:text-primary' ?>" href="/notifications">
                <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/notifications') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">notifications</span>
                <span class="font-semibold">Notifications</span>
            </a>
            <a class="flex items-center space-x-4 p-3 rounded-full transition-all <?= strpos($currentPath, '/profile/' . $currentUser['username']) !== false ? 'bg-primary-container/10 text-primary' : 'text-on-surface-variant/75 hover:text-primary' ?>" href="/profile/<?= Security::e($currentUser['username']) ?>">
                <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/profile/' . $currentUser['username']) !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">person</span>
                <span class="font-semibold">Profile</span>
            </a>

            <!-- Dynamic Role Based Links -->
            <?php if (in_array($currentUser['role'], ['creator', 'admin'])): ?>
                <a class="flex items-center space-x-4 p-3 rounded-full transition-all <?= strpos($currentPath, '/creator/dashboard') !== false ? 'bg-primary-container/10 text-primary' : 'text-on-surface-variant/75 hover:text-primary' ?>" href="/creator/dashboard">
                    <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/creator/dashboard') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">dashboard</span>
                    <span class="font-semibold">Creator Panel</span>
                </a>
            <?php endif; ?>

            <?php if ($currentUser['role'] === 'admin'): ?>
                <a class="flex items-center space-x-4 p-3 rounded-full transition-all <?= strpos($currentPath, '/admin/dashboard') !== false ? 'bg-primary-container/10 text-primary' : 'text-on-surface-variant/75 hover:text-primary' ?>" href="/admin/dashboard">
                    <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/admin/dashboard') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">admin_panel_settings</span>
                    <span class="font-semibold">Admin Panel</span>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a class="flex items-center space-x-4 p-3 rounded-full text-on-surface-variant/75 hover:text-primary transition-all" href="/auth/login">
                <span class="material-symbols-outlined">login</span>
                <span class="font-semibold">Login / Sign Up</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- User Section Bottom -->
    <div class="mt-auto border-t border-white/5 pt-4">
        <?php if ($currentUser): ?>
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-primary-container/20 flex items-center justify-center text-primary font-bold overflow-hidden">
                    <?php if (!empty($currentUser['avatar_url'])): ?>
                        <img src="<?= Security::e($currentUser['avatar_url']) ?>" class="w-full h-full object-cover"/>
                    <?php else: ?>
                        <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="flex-1 truncate">
                    <p class="text-sm font-semibold truncate"><?= Security::e($currentUser['full_name']) ?></p>
                    <p class="text-xs text-on-surface-variant truncate">@<?= Security::e($currentUser['username']) ?></p>
                </div>
            </div>
            <a href="/auth/logout" class="block w-full py-2.5 text-center rounded-full bg-surface-container-high text-xs font-semibold text-red-400 hover:bg-red-500/10 transition-colors">
                Logout
            </a>
        <?php else: ?>
            <a href="/auth/register" class="block w-full py-3 text-center rounded-full bg-primary text-on-primary font-semibold hover:opacity-90 transition-opacity">
                Create Account
            </a>
        <?php endif; ?>
    </div>
</nav>

<!-- Main Area -->
<main class="pt-20 md:pt-10 md:pl-72 max-w-4xl mx-auto px-5 md:px-8">

    <!-- Flash Alerts -->
    <?php if ($session->getFlash('success')): ?>
        <div class="mb-6 p-4 bg-primary/10 border border-primary/20 text-primary rounded-2xl flex items-center space-x-2">
            <span class="material-symbols-outlined">check_circle</span>
            <span class="text-sm font-medium"><?= $session->getFlash('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if ($session->getFlash('error')): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-2xl flex items-center space-x-2">
            <span class="material-symbols-outlined">error</span>
            <span class="text-sm font-medium"><?= $session->getFlash('error') ?></span>
        </div>
    <?php endif; ?>

    <?php if ($session->getFlash('errors')): ?>
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-2xl flex flex-col space-y-1">
            <div class="flex items-center space-x-2 mb-1">
                <span class="material-symbols-outlined">error</span>
                <span class="text-sm font-bold">Please correct the following errors:</span>
            </div>
            <span class="text-sm pl-7"><?= $session->getFlash('errors') ?></span>
        </div>
    <?php endif; ?>
