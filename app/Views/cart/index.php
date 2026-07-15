<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';

// Fetch platform settings
$settingsModel = new \App\Models\Product();
$settings = $settingsModel->fetch("SELECT platform_fee_percent FROM commission_settings WHERE id = 1");
$platformFeePercent = (float)($settings['platform_fee_percent'] ?? 5.00);
?>

<div class="max-w-4xl mx-auto space-y-6">
    <h1 class="text-2xl font-extrabold text-[#004D40] flex items-center space-x-2">
        <span class="material-symbols-outlined text-2xl font-bold text-primary">shopping_cart</span>
        <span>Your Shopping Cart</span>
    </h1>

    <?php if (empty($cart)): ?>
        <div class="text-center py-16 bg-white rounded-3xl border border-[#E0E6E2] text-on-surface-variant shadow-sm">
            <span class="material-symbols-outlined text-5xl mb-3 text-[#004D40] opacity-35">shopping_cart</span>
            <p class="text-xs font-bold text-[#004D40]">Your cart is empty.</p>
            <a href="/marketplace" class="mt-4 inline-block px-6 py-2.5 bg-[#004D40] text-white font-bold rounded-full text-xs shadow-md hover:bg-[#00332A] transition-all">Browse Catalog</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Products List (Left side, takes 2 cols) -->
            <div class="md:col-span-2 space-y-4">
                <?php foreach ($cart as $item): ?>
                    <div class="bg-white p-4 rounded-3xl border border-[#E0E6E2] flex justify-between items-center shadow-sm hover:border-[#004D40]/20 transition-all">
                        <div class="flex items-center space-x-3 min-w-0">
                            <span class="material-symbols-outlined text-[#004D40] text-2xl shrink-0">deployed_code</span>
                            <div class="min-w-0">
                                <h4 class="font-bold text-xs text-[#004D40] truncate"><?= Security::e($item['name']) ?></h4>
                                <span class="px-2.5 py-0.5 bg-[#004D40]/10 rounded text-[8px] font-extrabold text-[#004D40] uppercase mt-1 inline-block tracking-wider"><?= Security::e($item['type']) ?></span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <span class="text-xs font-extrabold text-[#004D40]">$<?= number_format($item['price'], 2) ?></span>
                            <form action="/cart/remove" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                                <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"/>
                                <button type="submit" class="text-rose-600 hover:text-rose-700 transition-colors flex items-center">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Coupon Box -->
                <div class="bg-white p-5 rounded-3xl border border-[#E0E6E2] space-y-3 shadow-sm">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#004D40]">Apply Discount Coupon</h4>
                    <form action="/cart/coupon" method="POST" class="flex gap-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                        <input type="text" name="coupon_code" placeholder="ENTER CODE" value="<?= $coupon ? Security::e($coupon['code']) : '' ?>" class="flex-1 px-4 py-2.5 bg-[#FBFBF9] border border-[#E0E6E2] rounded-2xl text-xs text-on-background focus:border-[#004D40] outline-none uppercase font-bold tracking-wider"/>
                        <button type="submit" class="px-6 py-2.5 bg-[#004D40]/10 hover:bg-[#004D40] hover:text-white text-xs font-bold text-[#004D40] rounded-full transition-all">
                            <?= $coupon ? 'Remove' : 'Apply' ?>
                        </button>
                    </form>
                    <?php if ($coupon): ?>
                        <p class="text-[10px] text-emerald-800 font-extrabold">✓ Coupon active: <?= (int)($coupon['discount_percent'] ?? 0) ?>% discount applied!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pricing Summary (Right side, takes 1 col) -->
            <div class="space-y-4">
                <div class="bg-white p-5 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-[#004D40]">Order Summary</h3>

                    <div class="space-y-2 text-xs font-semibold text-on-surface-variant border-b border-[#E0E6E2] pb-3">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span class="text-[#004D40] font-bold">$<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <?php if ($discount > 0): ?>
                            <div class="flex justify-between text-[#004D40] font-extrabold">
                                <span>Discount:</span>
                                <span>-$<?= number_format($discount, 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <span>Platform Fee (<?= (int)$platformFeePercent ?>%):</span>
                            <span class="text-[#004D40] font-bold">$<?= number_format($platformFee, 2) ?></span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-1 font-bold">
                        <span class="text-xs font-extrabold text-[#004D40] uppercase tracking-wider">Total Price:</span>
                        <span class="text-xl font-extrabold text-[#004D40]">$<?= number_format($finalAmount + $platformFee, 2) ?></span>
                    </div>

                    <div class="pt-4 border-t border-[#E0E6E2] space-y-4">
                        <p class="text-[10px] text-on-surface-variant font-semibold leading-relaxed">Purchases are processed instantly and secure download links will be unlocked on your profile dashboard.</p>

                        <?php if ($currentUser): ?>
                            <form action="/cart/checkout" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                                <button type="submit" class="w-full py-3.5 bg-[#004D40] text-white font-bold rounded-full hover:bg-[#00332A] transition-all flex justify-center items-center space-x-2 text-xs shadow-md">
                                    <span class="material-symbols-outlined text-sm font-bold">payment</span>
                                    <span>Checkout</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="/auth/login" class="block text-center w-full py-3.5 bg-[#004D40] text-white font-bold rounded-full text-xs shadow-md hover:bg-[#00332A] transition-all">
                                Login to Checkout
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
