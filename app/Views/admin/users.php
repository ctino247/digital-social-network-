<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6">
    <!-- Back link -->
    <a href="/admin/dashboard" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span>Back to Admin</span>
    </a>

    <!-- Users table -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Registered Accounts Directory</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-on-surface-variant font-semibold uppercase">
                        <th class="py-3">Name</th>
                        <th class="py-3">Role</th>
                        <th class="py-3">Affiliate Partner</th>
                        <th class="py-3">Verified Status</th>
                        <th class="py-3">Registered Date</th>
                        <th class="py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 font-semibold text-on-background font-mono">
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="py-3.5">
                                <div class="flex items-center space-x-2 font-geist">
                                    <div class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center text-primary text-[10px] font-bold overflow-hidden shrink-0">
                                        <?php if (!empty($u['avatar_url'])): ?>
                                            <img src="<?= Security::e($u['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                        <?php endif; ?>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-on-background text-xs truncate">@<?= Security::e($u['username']) ?></p>
                                        <p class="text-[9px] text-on-surface-variant truncate"><?= Security::e($u['full_name']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded text-[9px] uppercase font-bold <?= $u['role'] === 'admin' ? 'bg-red-500/10 text-red-400' : ($u['role'] === 'creator' ? 'bg-primary/10 text-primary' : 'bg-white/5 text-on-surface-variant') ?>">
                                    <?= Security::e($u['role']) ?>
                                </span>
                            </td>
                            <td class="py-3.5">
                                <span class="text-xs <?= $u['is_sales_partner'] ? 'text-primary' : 'text-on-surface-variant' ?>">
                                    <?= $u['is_sales_partner'] ? 'Unlocked' : 'Locked' ?>
                                </span>
                            </td>
                            <td class="py-3.5">
                                <span class="text-xs <?= $u['is_verified'] ? 'text-primary' : 'text-yellow-500' ?>">
                                    <?= $u['is_verified'] ? 'Verified' : 'Unverified' ?>
                                </span>
                            </td>
                            <td class="py-3.5 text-[10px] text-on-surface-variant"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td class="py-3.5 text-right">
                                <?php if (!$u['is_verified']): ?>
                                    <form action="/admin/users/<?= (int)$u['id'] ?>/verify" method="POST" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                                        <button type="submit" class="px-3 py-1 bg-primary text-on-primary hover:opacity-90 rounded-full text-[9px] font-bold uppercase transition-opacity">Verify</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-[10px] text-primary font-bold">✓ Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
