<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
?>

</main>

<!-- Floating Action Button for Creating Posts (Mobile Only) -->
<?php if ($currentUser): ?>
    <a href="/post/create" class="md:hidden fixed bottom-28 right-6 w-14 h-14 bg-[#004D40] text-[#FFE500] rounded-full shadow-lg flex items-center justify-center hover:scale-105 active:scale-95 transition-all z-40 border border-[#004D40]/20">
        <span class="material-symbols-outlined font-bold" style="font-size: 28px;">add</span>
    </a>
<?php endif; ?>

<!-- Bottom Navigation Bar (Mobile Only - styled exactly like the attached design mockup) -->
<nav class="md:hidden fixed bottom-4 left-4 right-4 z-50 h-20 bg-[#004D40] rounded-3xl shadow-xl flex justify-around items-center px-4 border border-[#004D40]/30">
    <!-- Home Feed Tab -->
    <a class="flex items-center justify-center transition-all duration-300 <?= $currentPath === '/' ? 'bg-[#FFE500] text-[#004D40] px-5 py-2.5 rounded-full font-bold shadow-md scale-105' : 'text-white/70 hover:text-white' ?>" href="/">
        <span class="material-symbols-outlined" style="<?= $currentPath === '/' ? "font-variation-settings: 'FILL' 1;" : "" ?>">home</span>
        <?php if ($currentPath === '/'): ?>
            <span class="text-xs ml-2 font-bold">Home</span>
        <?php endif; ?>
    </a>

    <!-- Explore Tab -->
    <a class="flex items-center justify-center transition-all duration-300 <?= strpos($currentPath, '/explore') !== false ? 'bg-[#FFE500] text-[#004D40] px-5 py-2.5 rounded-full font-bold shadow-md scale-105' : 'text-white/70 hover:text-white' ?>" href="/explore">
        <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/explore') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">explore</span>
        <?php if (strpos($currentPath, '/explore') !== false): ?>
            <span class="text-xs ml-2 font-bold">Explore</span>
        <?php endif; ?>
    </a>

    <!-- Marketplace Tab -->
    <a class="flex items-center justify-center transition-all duration-300 <?= strpos($currentPath, '/marketplace') !== false ? 'bg-[#FFE500] text-[#004D40] px-5 py-2.5 rounded-full font-bold shadow-md scale-105' : 'text-white/70 hover:text-white' ?>" href="/marketplace">
        <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/marketplace') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">shopping_bag</span>
        <?php if (strpos($currentPath, '/marketplace') !== false): ?>
            <span class="text-xs ml-2 font-bold">Shop</span>
        <?php endif; ?>
    </a>

    <!-- Messages Tab -->
    <a class="flex items-center justify-center transition-all duration-300 <?= strpos($currentPath, '/messages') !== false ? 'bg-[#FFE500] text-[#004D40] px-5 py-2.5 rounded-full font-bold shadow-md scale-105' : 'text-white/70 hover:text-white' ?>" href="/messages">
        <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/messages') !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">chat</span>
        <?php if (strpos($currentPath, '/messages') !== false): ?>
            <span class="text-xs ml-2 font-bold">Chat</span>
        <?php endif; ?>
    </a>

    <!-- Profile/Login Tab -->
    <?php if ($currentUser): ?>
        <a class="flex items-center justify-center transition-all duration-300 <?= strpos($currentPath, '/profile/' . $currentUser['username']) !== false ? 'bg-[#FFE500] text-[#004D40] px-5 py-2.5 rounded-full font-bold shadow-md scale-105' : 'text-white/70 hover:text-white' ?>" href="/profile/<?= Security::e($currentUser['username']) ?>">
            <span class="material-symbols-outlined" style="<?= strpos($currentPath, '/profile/' . $currentUser['username']) !== false ? "font-variation-settings: 'FILL' 1;" : "" ?>">person</span>
            <?php if (strpos($currentPath, '/profile/' . $currentUser['username']) !== false): ?>
                <span class="text-xs ml-2 font-bold">Me</span>
            <?php endif; ?>
        </a>
    <?php else: ?>
        <a class="flex items-center justify-center transition-all duration-300 <?= strpos($currentPath, '/auth/login') !== false ? 'bg-[#FFE500] text-[#004D40] px-5 py-2.5 rounded-full font-bold shadow-md scale-105' : 'text-white/70 hover:text-white' ?>" href="/auth/login">
            <span class="material-symbols-outlined">login</span>
            <?php if (strpos($currentPath, '/auth/login') !== false): ?>
                <span class="text-xs ml-2 font-bold">Login</span>
            <?php endif; ?>
        </a>
    <?php endif; ?>
</nav>

</body>
</html>
