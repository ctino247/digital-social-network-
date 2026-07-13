<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-8">

    <!-- Top Dashboard Header Card -->
    <div class="bg-primary/5 p-8 rounded-3xl border border-primary/20 relative overflow-hidden">
        <div class="absolute right-0 bottom-0 w-64 h-64 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
        <h1 class="text-3xl font-bold font-geist text-on-background mb-2">My Secured Wallet</h1>
        <p class="text-xs text-on-surface-variant max-w-lg leading-relaxed">Check available balances, monitor commission splits across 5 level lines, and request payout withdrawals instantly.</p>
    </div>

    <!-- Metrics Card row grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Available balance -->
        <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 flex items-center justify-between shadow-md">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Withdrawable Balance</p>
                <h2 class="text-2xl font-bold font-geist text-primary mt-1">$<?= number_format($wallet['balance'] ?? 0.00, 2) ?></h2>
                <p class="text-[8px] text-gray-500 mt-1 uppercase font-semibold">Available for transfer</p>
            </div>
            <span class="material-symbols-outlined text-3xl text-primary/45">account_balance_wallet</span>
        </div>
        <!-- Pending balance -->
        <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 flex items-center justify-between shadow-md">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Pending Balance</p>
                <h2 class="text-2xl font-bold font-geist text-yellow-500 mt-1">$<?= number_format($wallet['pending_balance'] ?? 0.00, 2) ?></h2>
                <p class="text-[8px] text-gray-500 mt-1 uppercase font-semibold">Attribution Escrow window</p>
            </div>
            <span class="material-symbols-outlined text-3xl text-yellow-500/45">hourglass_empty</span>
        </div>
        <!-- Total earnings -->
        <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 flex items-center justify-between shadow-md">
            <div>
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Total Earnings</p>
                <h2 class="text-2xl font-bold font-geist text-white mt-1">$<?= number_format($totalEarnings, 2) ?></h2>
                <p class="text-[8px] text-gray-500 mt-1 uppercase font-semibold">Accumulated historical balance</p>
            </div>
            <span class="material-symbols-outlined text-3xl text-gray-400/45">equalizer</span>
        </div>
    </div>

    <!-- Earnings Breakdown grid -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4 shadow-md">
        <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Revenue Streams Breakdown</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-semibold">
            <div class="p-4 bg-background/50 rounded-2xl border border-white/5">
                <p class="text-gray-400">Creator Royalties</p>
                <p class="text-base font-bold text-white mt-1 font-geist">$<?= number_format($creatorEarnings, 2) ?></p>
            </div>
            <div class="p-4 bg-background/50 rounded-2xl border border-white/5">
                <p class="text-gray-400">Indirect Referral Earnings</p>
                <p class="text-base font-bold text-white mt-1 font-geist">$<?= number_format($referralEarnings, 2) ?></p>
            </div>
            <div class="p-4 bg-background/50 rounded-2xl border border-white/5">
                <p class="text-gray-400">Direct Recommendation Commissions</p>
                <p class="text-base font-bold text-white mt-1 font-geist">$<?= number_format($recommendationEarnings, 2) ?></p>
            </div>
        </div>
    </div>

    <!-- Two-column: Withdrawal Trigger form & History -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Withdrawal Form -->
        <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4 shadow-md">
            <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Withdraw Earnings</h3>

            <?php if (!$withdrawalsEnabled): ?>
                <div class="p-4 bg-red-500/10 border border-red-500/20 text-red-400 text-xs rounded-2xl">
                    Payout withdrawals are temporarily disabled by the system administrator.
                </div>
            <?php else: ?>
                <div class="text-[10px] text-gray-400 space-y-1 bg-background/50 p-3 rounded-2xl border border-white/5 font-medium uppercase leading-relaxed">
                    <p>Minimum payout limit: <span class="text-white font-bold font-geist">$<?= number_format($minWithdrawal, 2) ?></span></p>
                    <p>Maximum payout limit: <span class="text-white font-bold font-geist">$<?= number_format($maxWithdrawal, 2) ?></span></p>
                    <p>Processing fee: <span class="text-red-400 font-bold font-geist">$<?= number_format($withdrawalFee, 2) ?></span></p>
                </div>

                <form action="/profile/withdraw" method="POST" class="space-y-4" id="withdrawal-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="amount" class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Amount ($)</label>
                            <input type="number" step="0.01" min="<?= $minWithdrawal ?>" max="<?= min($maxWithdrawal, $wallet['balance']) ?>" name="amount" id="amount" required placeholder="50.00" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-xs text-white focus:border-primary outline-none font-semibold"/>
                        </div>
                        <div>
                            <label for="payout_destination" class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Flutterwave Bank/Account Details</label>
                            <input type="text" name="destination" id="payout_destination" required placeholder="BankCode:AccountNumber" class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-xs text-white focus:border-primary outline-none font-semibold"/>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition-opacity text-xs flex justify-center items-center space-x-1.5 shadow-md shadow-primary/20">
                        <span class="material-symbols-outlined text-sm">local_atm</span>
                        <span>Submit Withdrawal Request</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Withdrawal History list -->
        <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4 shadow-md">
            <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Withdrawal History</h3>

            <?php if (empty($withdrawalsHistory)): ?>
                <p class="text-xs text-gray-500 py-4 text-center">No withdrawal request history.</p>
            <?php else: ?>
                <div class="space-y-3 max-h-[250px] overflow-y-auto pr-1">
                    <?php foreach ($withdrawalsHistory as $wh): ?>
                        <div class="p-3 bg-background/50 rounded-2xl border border-white/5 flex justify-between items-center text-xs">
                            <div class="space-y-1">
                                <p class="font-bold text-white font-geist">$<?= number_format($wh['amount'], 2) ?></p>
                                <p class="text-[9px] text-gray-400 truncate max-w-[150px]"><?= Security::e($wh['destination']) ?></p>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-0.5 rounded text-[8px] font-bold uppercase <?= $wh['status'] === 'approved' ? 'bg-primary/10 text-primary' : ($wh['status'] === 'pending' ? 'bg-yellow-500/10 text-yellow-400' : 'bg-red-500/10 text-red-400') ?>">
                                    <?= Security::e($wh['status']) ?>
                                </span>
                                <p class="text-[8px] text-gray-500 mt-1"><?= date('M d, Y', strtotime($wh['created_at'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Transaction Ledger -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4 shadow-md">
        <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Transactions History ledger</h3>

        <?php if (empty($transactions)): ?>
            <p class="text-xs text-gray-500 py-4 text-center font-medium">No ledger records found.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-white/5 text-on-surface-variant font-semibold uppercase">
                            <th class="py-3">Type</th>
                            <th class="py-3">Description</th>
                            <th class="py-3">Amount</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 font-semibold text-on-background font-mono">
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="py-3">
                                    <span class="px-2.5 py-0.5 rounded text-[9px] uppercase font-bold <?= in_array($t['type'], ['sale', 'commission', 'bonus']) ? 'bg-primary/10 text-primary' : 'bg-red-500/10 text-red-400' ?>">
                                        <?= Security::e($t['type']) ?>
                                    </span>
                                </td>
                                <td class="py-3 text-gray-300 font-sans text-xs"><?= Security::e($t['description']) ?></td>
                                <td class="py-3 font-geist text-sm <?= in_array($t['type'], ['sale', 'commission', 'bonus']) ? 'text-primary' : 'text-red-400' ?>">
                                    <?= in_array($t['type'], ['sale', 'commission', 'bonus']) ? '+' : '-' ?>$<?= number_format(abs($t['amount']), 2) ?>
                                </td>
                                <td class="py-3">
                                    <span class="text-xs <?= $t['status'] === 'completed' ? 'text-primary' : 'text-yellow-500' ?>"><?= Security::e($t['status']) ?></span>
                                </td>
                                <td class="py-3 text-[10px] text-gray-400"><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
