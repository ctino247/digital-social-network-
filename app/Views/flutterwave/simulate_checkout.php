<?php
use App\Core\Security;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Flutterwave Secured Checkout - Mimshack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                "primary": "#004D40", // Premium Deep Emerald Green
                "on-primary": "#FFFFFF"
              }
            }
          }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #F5F7F4 0%, #E9EEEA 100%);
        }
    </style>
</head>
<body class="text-[#0F211C] min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md bg-white p-8 rounded-3xl border border-[#E0E6E2] shadow-2xl space-y-6 relative overflow-hidden">
        <!-- Flutterwave Brand Watermark -->
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-[#F58634]/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex items-center justify-between border-b border-[#E0E6E2] pb-4">
            <div class="flex items-center space-x-2">
                <span class="material-symbols-outlined text-[#F58634] text-3xl font-bold">payments</span>
                <div>
                    <h1 class="text-sm font-extrabold text-[#F58634] tracking-tight">flutterwave</h1>
                    <p class="text-[9px] uppercase tracking-wider text-on-surface-variant font-bold">Secure Gateway Sandbox</p>
                </div>
            </div>
            <span class="text-[9px] px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-800 font-extrabold rounded-full uppercase tracking-wider">SANDBOX</span>
        </div>

        <!-- Checkout Details -->
        <div class="p-4 bg-[#FBFBF9] rounded-2xl border border-[#E0E6E2] space-y-3 text-xs shadow-inner">
            <div class="flex justify-between">
                <span class="text-on-surface-variant font-bold">Product Item:</span>
                <span class="font-extrabold text-[#004D40]"><?= Security::e($payment['product_name']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-on-surface-variant font-bold">Buyer Username:</span>
                <span class="font-extrabold text-[#004D40]">@<?= Security::e($payment['buyer_username']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-on-surface-variant font-bold">Tx Ref:</span>
                <span class="font-mono text-on-surface-variant font-bold text-[10px]"><?= Security::e($payment['tx_ref']) ?></span>
            </div>
            <div class="flex justify-between border-t border-[#E0E6E2] pt-2.5 text-sm">
                <span class="text-[#004D40] font-extrabold uppercase tracking-wider">Total Price:</span>
                <span class="font-extrabold text-[#004D40]">$<?= number_format($payment['amount'], 2) ?></span>
            </div>
        </div>

        <!-- Checkout Simulation controls -->
        <div class="space-y-4">
            <div class="space-y-2 text-xs">
                <label class="block text-[10px] font-extrabold uppercase tracking-wider text-[#004D40] opacity-75">Simulated Payment Option</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" class="p-3 bg-[#004D40]/10 border border-[#004D40]/20 text-[#004D40] font-bold rounded-2xl text-center text-xs flex items-center justify-center space-x-1.5 focus:outline-none">
                        <span class="material-symbols-outlined text-sm font-bold">credit_card</span>
                        <span>Debit Card</span>
                    </button>
                    <button type="button" class="p-3 bg-[#FBFBF9] border border-[#E0E6E2] text-on-surface-variant font-bold rounded-2xl text-center text-xs flex items-center justify-center space-x-1.5 opacity-50 cursor-not-allowed">
                        <span class="material-symbols-outlined text-sm">account_balance</span>
                        <span>Bank Transfer</span>
                    </button>
                </div>
            </div>

            <!-- Simulated Form Inputs -->
            <div class="space-y-3 text-xs font-bold">
                <div>
                    <label class="block text-[10px] font-extrabold uppercase tracking-wider text-on-surface-variant mb-1.5">Card Number</label>
                    <input type="text" readonly value="4000 1234 5678 9010" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-surface-variant font-mono outline-none"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-wider text-on-surface-variant mb-1.5">Expiry</label>
                        <input type="text" readonly value="12 / 28" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-surface-variant text-center outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[10px] font-extrabold uppercase tracking-wider text-on-surface-variant mb-1.5">CVV</label>
                        <input type="text" readonly value="123" class="w-full px-4 py-3 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-on-surface-variant text-center outline-none"/>
                    </div>
                </div>
            </div>
        </div>

        <form action="/flutterwave/process-simulation" method="POST" class="pt-2">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
            <input type="hidden" name="tx_ref" value="<?= Security::e($payment['tx_ref']) ?>"/>
            <input type="hidden" name="redirect" value="<?= Security::e($redirectUrl) ?>"/>

            <button type="submit" class="w-full py-4 bg-[#004D40] text-white font-bold rounded-full hover:bg-[#00332A] transition-all flex justify-center items-center space-x-2 text-xs shadow-md">
                <span class="material-symbols-outlined text-sm font-bold">lock</span>
                <span>Pay $<?= number_format($payment['amount'], 2) ?> Securely</span>
            </button>
        </form>

        <p class="text-[10px] text-center text-on-surface-variant font-bold leading-relaxed">Secured by Flutterwave. Payment is simulated end-to-end for Mimshack sandbox evaluations.</p>
    </div>

</body>
</html>
