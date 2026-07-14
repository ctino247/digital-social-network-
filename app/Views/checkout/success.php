<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="max-w-2xl mx-auto space-y-8 py-6">

    <!-- Success Badge & Heading -->
    <div class="bg-white p-8 rounded-3xl border border-[#E0E6E2] text-center space-y-4 shadow-sm relative overflow-hidden">
        <div class="absolute right-0 top-0 w-48 h-48 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="w-16 h-16 bg-emerald-100 text-emerald-800 rounded-full flex items-center justify-center mx-auto shadow-sm">
            <span class="material-symbols-outlined text-4xl font-bold">check_circle</span>
        </div>

        <h1 class="text-3xl font-extrabold text-[#004D40] tracking-tight">Payment Verified!</h1>
        <p class="text-xs font-semibold text-on-surface-variant max-w-md mx-auto leading-relaxed">Thank you for your purchase. Flutterwave has securely verified your transaction. Your digital assets are unlocked and ready to download!</p>

        <div class="inline-block px-4 py-2 bg-[#FBFBF9] border border-[#E0E6E2] rounded-full text-xs font-mono text-on-surface-variant shadow-inner">
            TX REF: <span class="font-bold text-[#004D40]"><?= Security::e($txRef) ?></span>
        </div>
    </div>

    <!-- Unlocked Products List -->
    <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
        <h3 class="text-xs font-extrabold uppercase tracking-widest text-[#004D40] opacity-75 border-b border-[#E0E6E2] pb-2">Your Unlocked Downloads</h3>

        <div class="divide-y divide-[#E0E6E2]/60">
            <?php foreach ($payments as $pay): ?>
                <div class="py-4 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-[#004D40]/10 flex items-center justify-center text-[#004D40] shrink-0 border border-[#004D40]/10">
                            <span class="material-symbols-outlined text-xl">cloud_download</span>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-xs text-[#004D40]"><?= Security::e($pay['product_name']) ?></h4>
                            <p class="text-[9px] font-extrabold text-on-surface-variant uppercase tracking-wider mt-1"><?= Security::e($pay['category_name']) ?></p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <a href="/product/<?= (int)$pay['product_id'] ?>/download" class="px-5 py-2 bg-[#004D40] text-white hover:bg-[#00332A] text-xs font-bold rounded-full transition-all flex items-center space-x-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-sm">download</span>
                            <span>Download Files</span>
                        </a>
                        <a href="/product/<?= Security::e($pay['product_slug']) ?>" class="px-4 py-2 border border-[#E0E6E2] hover:bg-[#F5F7F4] text-[#004D40] text-xs font-bold rounded-full transition-all">
                            View Page
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Payout Promotion / Next Step -->
    <div class="bg-[#FBFBF9] p-6 rounded-3xl border border-[#E0E6E2] flex flex-col sm:flex-row items-center justify-between gap-6 shadow-sm">
        <div class="space-y-1 text-center sm:text-left">
            <h4 class="text-xs font-extrabold text-[#004D40] uppercase tracking-wider">Become a Sales Partner!</h4>
            <p class="text-[10px] text-on-surface-variant font-bold leading-relaxed max-w-sm">Since you have successfully purchased a digital product, you are now an official Mimshack Sales Partner! Generate unique recommendation links to earn passive commissions.</p>
        </div>
        <a href="/profile/<?= Security::e($currentUser['username']) ?>?tab=recommendations" class="px-6 py-2.5 bg-[#FFE500] text-[#004D40] hover:bg-[#E6CE00] text-xs font-bold rounded-full transition-all shadow-sm shrink-0">
            Open Affiliate Space
        </a>
    </div>

</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
