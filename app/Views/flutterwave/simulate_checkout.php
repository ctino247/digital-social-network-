<?php
use App\Core\Security;
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Flutterwave Secured Checkout - Mimshack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              colors: {
                "primary": "#10B981", // Emerald Green
                "on-primary": "#064E3B",
                "background": "#131315",
                "surface": "#1F1F21",
                "surface-container-low": "#1B1B1D"
              }
            }
          }
        }
    </script>
</head>
<body class="bg-background text-[#E4E2E4] min-h-screen flex items-center justify-center p-6 font-sans">

    <div class="w-full max-w-md bg-surface p-8 rounded-3xl border border-white/5 shadow-2xl space-y-6 relative overflow-hidden">
        <!-- Flutterwave Brand Watermark -->
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-orange-500/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <div class="flex items-center space-x-2">
                <span class="material-symbols-outlined text-[#F58634] text-3xl">payments</span>
                <div>
                    <h1 class="text-sm font-bold font-geist text-white">flutterwave</h1>
                    <p class="text-[9px] uppercase tracking-wider text-gray-400 font-semibold">Secure Checkout</p>
                </div>
            </div>
            <span class="text-xs px-2.5 py-1 bg-green-500/10 text-green-400 font-bold rounded-full">SANDBOX ACTIVE</span>
        </div>

        <!-- Checkout Details -->
        <div class="p-4 bg-surface-container-low rounded-2xl border border-white/5 space-y-3 text-xs">
            <div class="flex justify-between">
                <span class="text-gray-400 font-medium">Product Item:</span>
                <span class="font-bold text-white"><?= Security::e($payment['product_name']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400 font-medium">Buyer Username:</span>
                <span class="font-bold text-white">@<?= Security::e($payment['buyer_username']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400 font-medium">Tx Ref:</span>
                <span class="font-mono text-gray-400"><?= Security::e($payment['tx_ref']) ?></span>
            </div>
            <div class="flex justify-between border-t border-white/5 pt-2 text-sm">
                <span class="text-gray-300 font-bold">Total Price:</span>
                <span class="font-bold text-primary font-geist">$<?= number_format($payment['amount'], 2) ?></span>
            </div>
        </div>

        <!-- Checkout Simulation controls -->
        <div class="space-y-4">
            <div class="space-y-2 text-xs">
                <label class="block text-gray-400 font-semibold uppercase tracking-wider">Select Simulated Payment Option</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" class="p-3 bg-primary/10 border border-primary/20 text-primary font-bold rounded-xl text-center text-xs flex items-center justify-center space-x-1.5 focus:outline-none">
                        <span class="material-symbols-outlined text-sm">credit_card</span>
                        <span>Debit Card</span>
                    </button>
                    <button type="button" class="p-3 bg-surface-container-low border border-white/5 text-gray-300 font-bold rounded-xl text-center text-xs flex items-center justify-center space-x-1.5 opacity-60">
                        <span class="material-symbols-outlined text-sm">account_balance</span>
                        <span>Bank Transfer</span>
                    </button>
                </div>
            </div>

            <!-- Simulated Form Inputs -->
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Card Number</label>
                    <input type="text" readonly value="4000 1234 5678 9010" class="w-full px-4 py-2.5 bg-surface-container-low border border-white/10 rounded-xl text-gray-400 font-mono outline-none"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-gray-400 font-semibold uppercase tracking-wider mb-1.5">Expiry</label>
                        <input type="text" readonly value="12 / 28" class="w-full px-4 py-2.5 bg-surface-container-low border border-white/10 rounded-xl text-gray-400 text-center outline-none"/>
                    </div>
                    <div>
                        <label class="block text-gray-400 font-semibold uppercase tracking-wider mb-1.5">CVV</label>
                        <input type="text" readonly value="123" class="w-full px-4 py-2.5 bg-surface-container-low border border-white/10 rounded-xl text-gray-400 text-center outline-none"/>
                    </div>
                </div>
            </div>
        </div>

        <form action="/flutterwave/process-simulation" method="POST" class="pt-2">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
            <input type="hidden" name="tx_ref" value="<?= Security::e($payment['tx_ref']) ?>"/>
            <input type="hidden" name="redirect" value="<?= Security::e($redirectUrl) ?>"/>

            <button type="submit" class="w-full py-3.5 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition shadow-lg shadow-primary/20 flex justify-center items-center space-x-2 text-xs">
                <span class="material-symbols-outlined text-sm">lock</span>
                <span>Pay $<?= number_format($payment['amount'], 2) ?> Securely</span>
            </button>
        </form>

        <p class="text-[10px] text-center text-gray-500 font-medium">Secured by Flutterwave. Payment is simulated end-to-end for Mimshack sandbox evaluations.</p>
    </div>

</body>
</html>
