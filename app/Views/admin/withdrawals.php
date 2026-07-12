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

    <!-- Withdrawals list -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Financial Payouts Moderation</h3>

        <?php if (empty($withdrawals)): ?>
            <p class="text-xs text-on-surface-variant py-4 text-center">No withdrawal requests found.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 text-on-surface-variant font-semibold uppercase">
                            <th class="py-3">Member</th>
                            <th class="py-3">Amount Requested</th>
                            <th class="py-3">Payout Destination</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Submission Date</th>
                            <th class="py-3 text-right">Moderation Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 font-semibold text-on-background font-mono">
                        <?php foreach ($withdrawals as $w): ?>
                            <tr>
                                <td class="py-3.5">
                                    <p class="text-xs font-bold font-geist">@<?= Security::e($w['username']) ?></p>
                                    <p class="text-[10px] text-on-surface-variant truncate"><?= Security::e($w['full_name']) ?></p>
                                </td>
                                <td class="py-3.5 text-sm font-bold font-geist text-primary">$<?= number_format($w['amount'], 2) ?></td>
                                <td class="py-3.5 text-xs text-on-surface-variant max-w-[150px] truncate"><?= Security::e($w['destination']) ?></td>
                                <td class="py-3.5">
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase <?= $w['status'] === 'approved' ? 'bg-primary/10 text-primary' : ($w['status'] === 'pending' ? 'bg-yellow-500/10 text-yellow-400' : 'bg-red-500/10 text-red-400') ?>">
                                        <?= Security::e($w['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 text-[10px] text-on-surface-variant"><?= date('M d, Y H:i', strtotime($w['created_at'])) ?></td>
                                <td class="py-3.5 text-right space-x-1">
                                    <?php if ($w['status'] === 'pending'): ?>
                                        <form action="/admin/withdrawals/<?= (int)$w['id'] ?>/approve" method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                                            <button type="submit" class="px-3.5 py-1 bg-primary text-on-primary hover:opacity-95 rounded-full text-[10px] font-bold uppercase transition-all shadow-sm">Approve</button>
                                        </form>
                                        <form action="/admin/withdrawals/<?= (int)$w['id'] ?>/reject" method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                                            <button type="submit" class="px-3.5 py-1 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white rounded-full text-[10px] font-bold uppercase transition-all">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-[10px] text-on-surface-variant uppercase">Completed</span>
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
