<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-8">

    <!-- Top Dashboard Header Card -->
    <div class="bg-white p-8 rounded-3xl border border-[#E0E6E2] relative overflow-hidden shadow-sm">
        <div class="absolute right-0 bottom-0 w-64 h-64 bg-[#FFE500]/10 rounded-full blur-3xl pointer-events-none"></div>
        <h1 class="text-3xl font-extrabold text-[#004D40] mb-2 flex items-center space-x-2">
            <span class="w-2.5 h-7 bg-[#FFE500] rounded-full inline-block"></span>
            <span>My Secured Wallet</span>
        </h1>
        <p class="text-xs font-semibold text-on-surface-variant max-w-lg leading-relaxed">Check available balances, monitor commission splits across 5 level lines, and request payout withdrawals instantly.</p>
    </div>

    <!-- Metrics Card row grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Available balance -->
        <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[9px] font-bold text-[#004D40]/70 uppercase tracking-wider">Withdrawable Balance</p>
                <h2 class="text-2xl font-extrabold text-[#004D40] mt-1">$<?= number_format($wallet['balance'] ?? 0.00, 2) ?></h2>
                <p class="text-[8px] text-on-surface-variant mt-1 uppercase font-bold">Available for transfer</p>
            </div>
            <span class="material-symbols-outlined text-3xl text-[#004D40] font-bold">account_balance_wallet</span>
        </div>
        <!-- Pending balance -->
        <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[9px] font-bold text-[#004D40]/70 uppercase tracking-wider">Pending Balance</p>
                <h2 class="text-2xl font-extrabold text-amber-600 mt-1">$<?= number_format($wallet['pending_balance'] ?? 0.00, 2) ?></h2>
                <p class="text-[8px] text-on-surface-variant mt-1 uppercase font-bold">Attribution Escrow window</p>
            </div>
            <span class="material-symbols-outlined text-3xl text-amber-500 font-bold">hourglass_empty</span>
        </div>
        <!-- Total earnings -->
        <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[9px] font-bold text-[#004D40]/70 uppercase tracking-wider">Total Earnings</p>
                <h2 class="text-2xl font-extrabold text-[#004D40] mt-1">$<?= number_format($totalEarnings, 2) ?></h2>
                <p class="text-[8px] text-on-surface-variant mt-1 uppercase font-bold">Accumulated historical balance</p>
            </div>
            <span class="material-symbols-outlined text-3xl text-on-surface-variant font-bold">equalizer</span>
        </div>
    </div>

    <!-- Earnings Breakdown grid -->
    <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-2">Revenue Streams Breakdown</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-bold text-[#004D40]/80">
            <div class="p-4 bg-[#F5F7F4] rounded-2xl border border-[#E0E6E2] shadow-inner">
                <p class="text-on-surface-variant">Creator Royalties</p>
                <p class="text-base font-extrabold text-[#004D40] mt-1">$<?= number_format($creatorEarnings, 2) ?></p>
            </div>
            <div class="p-4 bg-[#F5F7F4] rounded-2xl border border-[#E0E6E2] shadow-inner">
                <p class="text-on-surface-variant">Indirect Referral Earnings</p>
                <p class="text-base font-extrabold text-[#004D40] mt-1">$<?= number_format($referralEarnings, 2) ?></p>
            </div>
            <div class="p-4 bg-[#F5F7F4] rounded-2xl border border-[#E0E6E2] shadow-inner">
                <p class="text-on-surface-variant">Direct Commissions</p>
                <p class="text-base font-extrabold text-[#004D40] mt-1">$<?= number_format($recommendationEarnings, 2) ?></p>
            </div>
        </div>
    </div>

    <!-- Two-column: Withdrawal Trigger form & History -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Withdrawal Form -->
        <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-2">Withdraw Earnings</h3>

            <?php if (!$withdrawalsEnabled): ?>
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-2xl font-bold shadow-sm">
                    Payout withdrawals are temporarily disabled by the system administrator.
                </div>
            <?php else: ?>
                <div class="text-[10px] text-on-surface-variant space-y-1 bg-[#FBFBF9] p-4 rounded-2xl border border-[#EAE6DF] font-bold uppercase leading-relaxed shadow-inner">
                    <p>Minimum payout limit: <span class="text-[#004D40] font-extrabold">$<?= number_format($minWithdrawal, 2) ?></span></p>
                    <p>Maximum payout limit: <span class="text-[#004D40] font-extrabold">$<?= number_format($maxWithdrawal, 2) ?></span></p>
                    <p>Processing fee: <span class="text-rose-700 font-extrabold">$<?= number_format($withdrawalFee, 2) ?></span></p>
                </div>

                <form action="/profile/withdraw" method="POST" class="space-y-4" id="withdrawal-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="amount" class="block text-[10px] font-bold uppercase tracking-wider text-[#004D40] mb-1.5">Amount ($)</label>
                            <input type="number" step="0.01" min="<?= $minWithdrawal ?>" max="<?= min($maxWithdrawal, $wallet['balance']) ?>" name="amount" id="amount" required placeholder="50.00" class="w-full px-4 py-2.5 bg-white border border-[#E0E6E2] rounded-2xl text-xs text-[#004D40] focus:border-[#004D40] outline-none font-bold shadow-sm"/>
                        </div>
                        <div>
                            <label for="payout_destination" class="block text-[10px] font-bold uppercase tracking-wider text-[#004D40] mb-1.5">Bank/Account Details</label>
                            <input type="text" name="destination" id="payout_destination" required placeholder="BankCode:AccountNumber" class="w-full px-4 py-2.5 bg-white border border-[#E0E6E2] rounded-2xl text-xs text-[#004D40] focus:border-[#004D40] outline-none font-bold shadow-sm"/>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 bg-[#004D40] text-white font-bold rounded-full hover:bg-[#00332A] transition-colors text-xs flex justify-center items-center space-x-1.5 shadow-md">
                        <span class="material-symbols-outlined text-sm font-bold">local_atm</span>
                        <span>Submit Withdrawal Request</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Withdrawal History list -->
        <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-2">Withdrawal History</h3>

            <?php if (empty($withdrawalsHistory)): ?>
                <p class="text-xs text-on-surface-variant py-4 text-center font-bold">No withdrawal request history.</p>
            <?php else: ?>
                <div class="space-y-3 max-h-[250px] overflow-y-auto pr-1">
                    <?php foreach ($withdrawalsHistory as $wh): ?>
                        <div class="p-3 bg-[#FBFBF9] rounded-2xl border border-[#EAE6DF] flex justify-between items-center text-xs shadow-sm">
                            <div class="space-y-1">
                                <p class="font-bold text-[#004D40]">$<?= number_format($wh['amount'], 2) ?></p>
                                <p class="text-[9px] text-on-surface-variant font-bold truncate max-w-[150px]"><?= Security::e($wh['destination']) ?></p>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-0.5 rounded text-[8px] font-extrabold uppercase <?= $wh['status'] === 'approved' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : ($wh['status'] === 'pending' ? 'bg-amber-50 border border-amber-200 text-amber-800' : 'bg-rose-50 border border-rose-200 text-rose-800') ?>">
                                    <?= Security::e($wh['status']) ?>
                                </span>
                                <p class="text-[8px] text-on-surface-variant font-bold mt-1"><?= date('M d, Y', strtotime($wh['created_at'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Transaction Ledger -->
    <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-2">Transactions History ledger</h3>

        <?php if (empty($transactions)): ?>
            <p class="text-xs text-on-surface-variant py-4 text-center font-extrabold">No ledger records found.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse font-bold">
                    <thead>
                        <tr class="border-b border-[#E0E6E2] text-[#004D40] font-extrabold uppercase text-[10px] tracking-wider">
                            <th class="py-3">Type</th>
                            <th class="py-3">Description</th>
                            <th class="py-3">Amount</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E0E6E2] text-on-surface font-semibold font-mono">
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="py-3">
                                    <span class="px-2.5 py-0.5 rounded text-[9px] uppercase font-extrabold <?= in_array($t['type'], ['sale', 'commission', 'bonus']) ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
                                        <?= Security::e($t['type']) ?>
                                    </span>
                                </td>
                                <td class="py-3 text-[#004D40] font-bold text-xs"><?= Security::e($t['description']) ?></td>
                                <td class="py-3 text-sm font-bold <?= in_array($t['type'], ['sale', 'commission', 'bonus']) ? 'text-[#004D40]' : 'text-rose-700' ?>">
                                    <?= in_array($t['type'], ['sale', 'commission', 'bonus']) ? '+' : '-' ?>$<?= number_format(abs($t['amount']), 2) ?>
                                </td>
                                <td class="py-3">
                                    <span class="text-xs font-bold <?= $t['status'] === 'completed' ? 'text-[#004D40]' : 'text-amber-600' ?>"><?= Security::e($t['status']) ?></span>
                                </td>
                                <td class="py-3 text-[10px] text-on-surface-variant font-bold"><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
