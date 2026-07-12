<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold font-geist text-on-background">Your Shopping Cart</h1>

    <?php if (empty($cart)): ?>
        <div class="text-center py-16 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
            <span class="material-symbols-outlined text-5xl mb-3 opacity-55">shopping_cart</span>
            <p class="text-sm font-medium">Your cart is empty.</p>
            <a href="/marketplace" class="mt-4 inline-block px-5 py-2.5 bg-primary text-on-primary font-bold rounded-full text-xs shadow-md">Browse Catalog</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Products List (Left side, takes 2 cols) -->
            <div class="md:col-span-2 space-y-4">
                <?php foreach ($cart as $item): ?>
                    <div class="bg-surface-container-low p-4 rounded-3xl border border-white/5 flex justify-between items-center">
                        <div class="flex items-center space-x-3 min-w-0">
                            <span class="material-symbols-outlined text-primary text-2xl shrink-0">deployed_code</span>
                            <div class="min-w-0">
                                <h4 class="font-bold text-sm text-on-background truncate font-geist"><?= Security::e($item['name']) ?></h4>
                                <span class="px-2 py-0.5 bg-white/5 rounded text-[8px] font-bold text-on-surface-variant uppercase mt-1 inline-block"><?= Security::e($item['type']) ?></span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <span class="text-sm font-bold text-primary font-geist">$<?= number_format($item['price'], 2) ?></span>
                            <form action="/cart/remove" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                                <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"/>
                                <button type="submit" class="text-red-400 hover:text-red-300 transition-colors">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Coupon Box -->
                <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">Apply Discount Coupon</h4>
                    <form action="/cart/coupon" method="POST" class="flex gap-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                        <input type="text" name="coupon_code" placeholder="ENTER CODE" value="<?= $coupon ? Security::e($coupon['code']) : '' ?>" class="flex-1 px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-xs text-on-background focus:border-primary outline-none uppercase font-semibold font-mono"/>
                        <button type="submit" class="px-5 py-2.5 bg-primary/10 hover:bg-primary hover:text-on-primary text-xs font-bold text-primary rounded-full transition-all">
                            <?= $coupon ? 'Remove' : 'Apply' ?>
                        </button>
                    </form>
                    <?php if ($coupon): ?>
                        <p class="text-[10px] text-primary font-bold">✓ Coupon active: <?= (int)($coupon['discount_percent'] ?? 0) ?>% discount applied!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pricing Summary (Right side, takes 1 col) -->
            <div class="space-y-4">
                <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-on-background font-geist">Order Summary</h3>

                    <div class="space-y-2 text-xs font-semibold text-on-surface-variant border-b border-white/5 pb-3">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span class="text-on-background font-geist">$<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <?php if ($discount > 0): ?>
                            <div class="flex justify-between text-primary">
                                <span>Discount:</span>
                                <span class="font-geist">-$<?= number_format($discount, 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <span>Platform Fee (<?= (int)$platformFeePercent ?? 5 ?>%):</span>
                            <span class="text-on-background font-geist">$<?= number_format($platformFee, 2) ?></span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-1">
                        <span class="text-xs font-bold text-on-background">Total Price:</span>
                        <span class="text-xl font-bold text-primary font-geist">$<?= number_format($finalAmount + $platformFee, 2) ?></span>
                    </div>

                    <div class="pt-4 border-t border-white/5 space-y-4">
                        <p class="text-[10px] text-on-surface-variant leading-relaxed">Purchases are processed instantly and secure download links will be unlocked on your profile dashboard.</p>

                        <?php if ($currentUser): ?>
                            <form action="/cart/checkout" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                                <button type="submit" class="w-full py-3 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition-opacity flex justify-center items-center space-x-2 text-xs shadow-lg shadow-primary/20">
                                    <span class="material-symbols-outlined text-sm">payment</span>
                                    <span>Simulate Checkout</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="/auth/login" class="block text-center w-full py-3 bg-primary text-on-primary font-bold rounded-full text-xs shadow-md">
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
