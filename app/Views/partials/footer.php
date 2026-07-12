<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
?>

</main>

<!-- Floating Action Button for Creating Posts (Mobile Only) -->
<?php if ($currentUser): ?>
    <a href="/post/create" class="md:hidden fixed bottom-24 right-4 w-14 h-14 bg-primary text-on-primary rounded-full shadow-lg flex items-center justify-center hover:scale-105 active:scale-95 transition-all z-40">
        <span class="material-symbols-outlined" style="font-size: 28px;">add</span>
    </a>
<?php endif; ?>

<!-- Bottom Navigation Bar (Mobile Only) -->
<nav class="md:hidden fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-6 pb-safe h-20 bg-surface-container-low/95 backdrop-blur-2xl border-t border-white/5 shadow-lg">
    <a class="flex flex-col items-center justify-center <?= $currentPath === '/' ? 'text-primary bg-primary/10 rounded-full p-2 scale-95' : 'text-on-surface-variant/60 hover:text-primary' ?> transition-all duration-300" href="/">
        <span class="material-symbols-outlined" style="<?= $currentPath === '/' ? "font-variation-settings: 'FILL' 1;" : "" ?>">home</span>
        <span class="font-label-md text-label-md sr-only">Home</span>
    </a>
    <a class="flex flex-col items-center justify-center <?= strpos($currentPath, '/explore') !== false ? 'text-primary bg-primary/10 rounded-full p-2 scale-95' : 'text-on-surface-variant/60 hover:text-primary' ?> transition-colors" href="/explore">
        <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/explore') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">explore</span>
        <span class="font-label-md text-label-md sr-only">Explore</span>
    </a>
    <a class="flex flex-col items-center justify-center <?= strpos($currentPath, '/marketplace') !== false ? 'text-primary bg-primary/10 rounded-full p-2 scale-95' : 'text-on-surface-variant/60 hover:text-primary' ?> transition-colors" href="/marketplace">
        <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/marketplace') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">shopping_bag</span>
        <span class="font-label-md text-label-md sr-only">Shop</span>
    </a>
    <a class="flex flex-col items-center justify-center <?= strpos($currentPath, '/notifications') !== false ? 'text-primary bg-primary/10 rounded-full p-2 scale-95' : 'text-on-surface-variant/60 hover:text-primary' ?> transition-colors" href="/notifications">
        <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/notifications') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">notifications</span>
        <span class="font-label-md text-label-md sr-only">Notifications</span>
    </a>
    <?php if ($currentUser): ?>
        <a class="flex flex-col items-center justify-center <?= strpos($currentPath, '/profile/' . $currentUser['username']) !== false ? 'text-primary bg-primary/10 rounded-full p-2 scale-95' : 'text-on-surface-variant/60 hover:text-primary' ?> transition-colors" href="/profile/<?= Security::e($currentUser['username']) ?>">
            <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/profile/' . $currentUser['username']) !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">person</span>
            <span class="font-label-md text-label-md sr-only">Profile</span>
        </a>
    <?php else: ?>
        <a class="flex flex-col items-center justify-center text-on-surface-variant/60 hover:text-primary transition-colors" href="/auth/login">
            <span class="material-symbols-outlined">login</span>
            <span class="font-label-md text-label-md sr-only">Login</span>
        </a>
    <?php endif; ?>
</nav>

</body>
</html>
