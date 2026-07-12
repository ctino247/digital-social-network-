<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-8">
    <!-- Admin Hero card -->
    <div class="bg-red-500/5 p-8 rounded-3xl border border-red-500/10 relative overflow-hidden">
        <div class="absolute right-0 bottom-0 w-64 h-64 bg-red-500/5 rounded-full blur-3xl -mr-20 -mb-20 pointer-events-none"></div>
        <h1 class="text-3xl font-bold font-geist text-on-background mb-2">Platform Administration</h1>
        <p class="text-sm text-on-surface-variant max-w-lg leading-relaxed">System dashboard for overall platform statistics, user directories, catalog moderation, financial payouts review, and system configuration toggles.</p>
    </div>

    <!-- Stats row grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5">
            <span class="material-symbols-outlined text-red-400 text-2xl mb-1">payments</span>
            <p class="text-[9px] font-bold text-on-surface-variant uppercase">Platform Revenue</p>
            <h3 class="text-xl font-bold text-on-background mt-1 font-geist">$<?= number_format($totalRevenue, 2) ?></h3>
        </div>
        <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5">
            <span class="material-symbols-outlined text-primary text-2xl mb-1">paid</span>
            <p class="text-[9px] font-bold text-on-surface-variant uppercase">Fees Earned</p>
            <h3 class="text-xl font-bold text-primary mt-1 font-geist">$<?= number_format($platformFeesEarned, 2) ?></h3>
        </div>
        <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5">
            <span class="material-symbols-outlined text-blue-400 text-2xl mb-1">group</span>
            <p class="text-[9px] font-bold text-on-surface-variant uppercase">Total Users</p>
            <h3 class="text-xl font-bold text-on-background mt-1 font-geist"><?= $usersCount ?></h3>
        </div>
        <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5">
            <span class="material-symbols-outlined text-yellow-500 text-2xl mb-1">deployed_code</span>
            <p class="text-[9px] font-bold text-on-surface-variant uppercase">Listed Products</p>
            <h3 class="text-xl font-bold text-on-background mt-1 font-geist"><?= $productsCount ?></h3>
        </div>
    </div>

    <!-- Admin Links Shortcut Row -->
    <div class="flex flex-wrap gap-3">
        <a href="/admin/users" class="px-5 py-2.5 bg-surface-container-high hover:bg-white/5 border border-white/5 text-xs font-bold rounded-full transition-all">Manage Users</a>
        <a href="/admin/creator-applications" class="px-5 py-2.5 bg-surface-container-high hover:bg-white/5 border border-white/5 text-xs font-bold rounded-full transition-all flex items-center space-x-1.5">
            <span>Creators Applications</span>
            <?php if ($pendingCreators > 0): ?>
                <span class="px-2 py-0.5 bg-red-500 text-white rounded-full text-[9px] font-bold"><?= $pendingCreators ?></span>
            <?php endif; ?>
        </a>
        <a href="/admin/products" class="px-5 py-2.5 bg-surface-container-high hover:bg-white/5 border border-white/5 text-xs font-bold rounded-full transition-all">Products Moderation</a>
        <a href="/admin/withdrawals" class="px-5 py-2.5 bg-surface-container-high hover:bg-white/5 border border-white/5 text-xs font-bold rounded-full transition-all flex items-center space-x-1.5">
            <span>Payout Requests</span>
            <?php if ($pendingWithdrawals > 0): ?>
                <span class="px-2 py-0.5 bg-red-500 text-white rounded-full text-[9px] font-bold"><?= $pendingWithdrawals ?></span>
            <?php endif; ?>
        </a>
        <a href="/admin/settings" class="px-5 py-2.5 bg-red-500/10 hover:bg-red-500 hover:text-white border border-red-500/20 text-red-400 text-xs font-bold rounded-full transition-all">System Settings</a>
    </div>

    <!-- Recent orders audit -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Recent Completed Purchases</h3>

        <?php if (empty($recentOrders)): ?>
            <p class="text-xs text-on-surface-variant">No orders completed yet.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 text-on-surface-variant font-semibold uppercase">
                            <th class="py-3">Order ID</th>
                            <th class="py-3">Buyer</th>
                            <th class="py-3">Product Name</th>
                            <th class="py-3">Amount</th>
                            <th class="py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 font-semibold text-on-background font-mono">
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td class="py-3 text-on-surface-variant">#<?= (int)$order['id'] ?></td>
                                <td class="py-3">@<?= Security::e($order['buyer_username']) ?></td>
                                <td class="py-3 text-on-surface-variant truncate font-geist"><?= Security::e($order['product_name']) ?></td>
                                <td class="py-3 text-primary font-geist">$<?= number_format($order['final_amount'], 2) ?></td>
                                <td class="py-3 text-on-surface-variant text-[10px]"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
