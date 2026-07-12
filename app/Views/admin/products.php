<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6">
    <!-- Back Link -->
    <a href="/admin/dashboard" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span>Back to Admin</span>
    </a>

    <!-- Products list -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Digital Products Moderation</h3>

        <?php if (empty($products)): ?>
            <p class="text-xs text-on-surface-variant py-4 text-center">No products found in catalog.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 text-on-surface-variant font-semibold uppercase">
                            <th class="py-3">Product Name</th>
                            <th class="py-3">Creator</th>
                            <th class="py-3">Category</th>
                            <th class="py-3">Asset Type</th>
                            <th class="py-3">Price</th>
                            <th class="py-3">Listing Status</th>
                            <th class="py-3 text-right">Moderation Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 font-semibold text-on-background font-mono">
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td class="py-3.5">
                                    <p class="text-xs font-bold font-geist truncate max-w-[150px]"><?= Security::e($prod['name']) ?></p>
                                    <span class="text-[9px] text-on-surface-variant"><?= Security::e($prod['file_name']) ?></span>
                                </td>
                                <td class="py-3.5">@<?= Security::e($prod['creator_username']) ?></td>
                                <td class="py-3.5 text-on-surface-variant"><?= Security::e($prod['category_name']) ?></td>
                                <td class="py-3.5 text-primary uppercase text-[10px]"><?= Security::e($prod['type']) ?></td>
                                <td class="py-3.5 text-primary font-geist">$<?= number_format($prod['price'], 2) ?></td>
                                <td class="py-3.5">
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase <?= $prod['status'] === 'active' ? 'bg-primary/10 text-primary' : ($prod['status'] === 'pending' ? 'bg-yellow-500/10 text-yellow-400' : 'bg-red-500/10 text-red-400') ?>">
                                        <?= Security::e($prod['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 text-right space-x-2">
                                    <?php if ($prod['status'] !== 'active'): ?>
                                        <form action="/admin/products/<?= (int)$prod['id'] ?>/approve" method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                                            <button type="submit" class="px-3.5 py-1 bg-primary text-on-primary hover:opacity-95 rounded-full text-[10px] font-bold uppercase transition-all shadow-sm">Approve</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($prod['status'] !== 'rejected'): ?>
                                        <form action="/admin/products/<?= (int)$prod['id'] ?>/reject" method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                                            <button type="submit" class="px-3.5 py-1 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white rounded-full text-[10px] font-bold uppercase transition-all">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
