<?php
use App\Core\Security;
?>
<?php if ($isOwnProfile): ?>
    <div id="content-wallet" class="tab-content space-y-6 hidden">
        <!-- Wallet Dashboard Info cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-emerald-50 p-6 rounded-3xl border border-emerald-200 flex items-center justify-between shadow-sm">
                <div>
                    <p class="text-xs text-emerald-800 font-extrabold uppercase tracking-wider">Available Balance</p>
                    <h2 class="text-3xl font-extrabold text-[#004D40] mt-1">$<?= number_format($wallet['balance'] ?? 0.00, 2) ?></h2>
                </div>
                <span class="material-symbols-outlined text-4xl text-emerald-600 font-bold">account_balance_wallet</span>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] flex items-center justify-between shadow-sm">
                <div>
                    <p class="text-xs text-on-surface-variant font-extrabold uppercase tracking-wider">Pending Royalties</p>
                    <h2 class="text-3xl font-extrabold text-[#004D40] mt-1">$<?= number_format($wallet['pending_balance'] ?? 0.00, 2) ?></h2>
                </div>
                <span class="material-symbols-outlined text-4xl text-on-surface-variant/60">hourglass_empty</span>
            </div>
        </div>

        <!-- Link to dedicated wallet page -->
        <div class="p-5 bg-white rounded-2xl border border-[#E0E6E2] flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
            <div class="space-y-0.5">
                <h4 class="text-xs font-bold text-[#004D40]">Access Advanced Wallet Dashboard</h4>
                <p class="text-[10px] text-on-surface-variant font-bold leading-relaxed">View granular earning statistics, track Flutterwave withdrawals, and review historic transactions ledgers.</p>
            </div>
            <a href="/wallet" class="px-5 py-2.5 bg-[#004D40] text-white font-bold rounded-full text-xs shadow-md hover:bg-[#00332A] transition-colors">Open Wallet Dashboard</a>
        </div>
    </div>
<?php endif; ?>
