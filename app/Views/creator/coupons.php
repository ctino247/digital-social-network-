<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6">
    <!-- Back Link -->
    <a href="/creator/dashboard" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span>Back to Dashboard</span>
    </a>

    <!-- Top card layout split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left: Coupon Generator form -->
        <div class="space-y-4">
            <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-primary mb-4 font-geist">Create Coupon Key</h3>

                <form action="/creator/coupons/new" method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

                    <div>
                        <label for="code" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Coupon Code Code</label>
                        <input type="text" id="code" name="code" required placeholder="e.g. SAVE20" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-bold font-mono uppercase"/>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="discount_percent" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Discount (%)</label>
                            <input type="number" min="1" max="100" id="discount_percent" name="discount_percent" placeholder="20" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-medium"/>
                        </div>
                        <div>
                            <label for="discount_amount" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Flat Cash ($)</label>
                            <input type="number" step="0.01" min="0.01" id="discount_amount" name="discount_amount" placeholder="5.00" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-medium"/>
                        </div>
                    </div>

                    <div>
                        <label for="expires_at" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Expiration Date</label>
                        <input type="date" id="expires_at" name="expires_at" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary outline-none text-xs font-medium"/>
                    </div>

                    <button type="submit" class="w-full py-3 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 text-xs shadow-md">
                        Generate Coupon
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Active Coupons List table -->
        <div class="lg:col-span-2 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">My Coupon list</h3>

            <?php if (empty($coupons)): ?>
                <div class="text-center py-12 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl mb-2 opacity-55">percent</span>
                    <p class="text-sm font-medium">You haven\'t created any coupon codes yet.</p>
                </div>
            <?php else: ?>
                <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 text-on-surface-variant font-semibold uppercase">
                                <th class="py-3">Code</th>
                                <th class="py-3">Discount Split</th>
                                <th class="py-3">Expiration Date</th>
                                <th class="py-3">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 font-semibold text-on-background font-mono">
                            <?php foreach ($coupons as $coup): ?>
                                <tr>
                                    <td class="py-3 text-primary text-sm font-bold"><?= Security::e($coup['code']) ?></td>
                                    <td class="py-3">
                                        <?php if ($coup['discount_percent']): ?>
                                            <?= (int)$coup['discount_percent'] ?>% Off
                                        <?php else: ?>
                                            $<?= number_format($coup['discount_amount'], 2) ?> Off
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 text-on-surface-variant text-xs">
                                        <?= $coup['expires_at'] ? date('M d, Y', strtotime($coup['expires_at'])) : 'Never Expires' ?>
                                    </td>
                                    <td class="py-3 text-on-surface-variant text-[10px]"><?= date('M d, Y', strtotime($coup['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
