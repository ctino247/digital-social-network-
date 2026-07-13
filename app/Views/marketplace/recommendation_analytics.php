<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6">
    <!-- Back link -->
    <a href="/profile/<?= Security::e($currentUser['username']) ?>" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span>Back to My Profile</span>
    </a>

    <!-- Top Card Header -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 relative overflow-hidden">
        <div class="absolute right-0 bottom-0 w-48 h-48 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
        <h1 class="text-2xl font-bold font-geist text-on-background leading-tight mb-2">Recommendation Analytics</h1>
        <p class="text-xs text-on-surface-variant">Real-time conversions tracking and financial performance of your shared recommendation link.</p>

        <div class="mt-4 pt-4 border-t border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Product Item</p>
                <h4 class="text-sm font-bold text-white mt-1"><?= Security::e($refLink['product_name']) ?></h4>
            </div>
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Referral Code Key</p>
                <span class="px-3 py-1.5 bg-background border border-white/10 rounded-xl text-xs font-mono text-primary font-bold inline-block mt-1"><?= Security::e($refLink['code']) ?></span>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Clicks -->
        <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 flex flex-col justify-between shadow-sm">
            <span class="material-symbols-outlined text-primary text-xl">ads_click</span>
            <div class="mt-4">
                <p class="text-[9px] font-bold text-gray-400 uppercase">Link Clicks</p>
                <h3 class="text-2xl font-bold text-white mt-1 font-geist"><?= $clicks ?></h3>
            </div>
        </div>
        <!-- Views -->
        <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 flex flex-col justify-between shadow-sm">
            <span class="material-symbols-outlined text-blue-400 text-xl">visibility</span>
            <div class="mt-4">
                <p class="text-[9px] font-bold text-gray-400 uppercase">Product Views</p>
                <h3 class="text-2xl font-bold text-white mt-1 font-geist"><?= $views ?></h3>
            </div>
        </div>
        <!-- Checkout Starts -->
        <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 flex flex-col justify-between shadow-sm">
            <span class="material-symbols-outlined text-yellow-500 text-xl">shopping_cart_checkout</span>
            <div class="mt-4">
                <p class="text-[9px] font-bold text-gray-400 uppercase">Checkout Starts</p>
                <h3 class="text-2xl font-bold text-white mt-1 font-geist"><?= $checkouts ?></h3>
            </div>
        </div>
        <!-- Successful Purchases -->
        <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 flex flex-col justify-between shadow-sm">
            <span class="material-symbols-outlined text-green-400 text-xl">task_alt</span>
            <div class="mt-4">
                <p class="text-[9px] font-bold text-gray-400 uppercase">Purchases</p>
                <h3 class="text-2xl font-bold text-white mt-1 font-geist"><?= $purchases ?></h3>
            </div>
        </div>
    </div>

    <!-- Conversion & Earnings Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Conversion Rate -->
        <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-on-background font-geist">Conversion Performance</h3>
            <div class="flex items-center space-x-4">
                <div class="w-16 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold font-geist text-lg border border-primary/20">
                    <?= $conversionRate ?>%
                </div>
                <div class="flex-1">
                    <p class="text-xs text-on-surface-variant leading-relaxed">Percentage of link clicks that converted successfully into product purchases.</p>
                </div>
            </div>
            <!-- Conversion Line visualization -->
            <div class="w-full bg-background rounded-full h-2 overflow-hidden border border-white/5">
                <div class="bg-primary h-full rounded-full" style="width: <?= min(100, $conversionRate) ?>%"></div>
            </div>
        </div>

        <!-- Earnings Card -->
        <div class="bg-primary/5 p-6 rounded-3xl border border-primary/20 flex items-center justify-between">
            <div class="space-y-1">
                <p class="text-xs text-primary font-bold uppercase tracking-wider">Total Commission Earned</p>
                <h2 class="text-3xl font-bold font-geist text-primary">$<?= number_format($earnings, 2) ?></h2>
                <p class="text-[10px] text-gray-400 mt-1">Earned via direct recommendation sales splits.</p>
            </div>
            <span class="material-symbols-outlined text-5xl text-primary/45">payments</span>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
