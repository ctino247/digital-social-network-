<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6">
    <!-- Back Link -->
    <a href="/marketplace" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span>Back to Catalog</span>
    </a>

    <!-- Main Grid: Showcase Details & Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left: Product Specs & Content -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 relative overflow-hidden">
                <!-- Banner decorative accent -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-primary via-blue-500 to-primary"></div>

                <div class="flex items-center space-x-3 mb-4 mt-2">
                    <span class="px-2.5 py-0.5 bg-primary/10 text-primary border border-primary/20 text-[10px] font-bold uppercase rounded-full"><?= Security::e($product['type']) ?></span>
                    <span class="text-xs text-on-surface-variant font-medium">• Uploaded in <?= Security::e($product['category_name']) ?></span>
                </div>

                <h1 class="text-2xl font-bold font-geist text-on-background leading-tight mb-2"><?= Security::e($product['name']) ?></h1>

                <!-- Creator Bio Header block -->
                <div class="flex items-center space-x-2.5 mb-6">
                    <div class="w-7 h-7 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden text-xs">
                        <?php if (!empty($product['creator_avatar'])): ?>
                            <img src="<?= Security::e($product['creator_avatar']) ?>" class="w-full h-full object-cover"/>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs text-on-surface-variant font-medium">Offered by <a href="/profile/<?= Security::e($product['creator_username']) ?>" class="text-on-background font-bold hover:text-primary transition-colors">@<?= Security::e($product['creator_username']) ?></a></span>
                </div>

                <!-- Specs stats cards -->
                <div class="grid grid-cols-3 gap-4 border-y border-white/5 py-4 mb-6">
                    <div class="text-center">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant">Rating</p>
                        <p class="text-sm font-bold text-primary mt-1 font-geist flex items-center justify-center space-x-1">
                            <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span><?= $product['avg_rating'] ? number_format($product['avg_rating'], 1) : '5.0' ?></span>
                        </p>
                    </div>
                    <div class="text-center border-x border-white/5">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant">Reviews</p>
                        <p class="text-sm font-bold text-on-background mt-1 font-geist"><?= (int)$product['reviews_count'] ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant">Secure Delivery</p>
                        <p class="text-sm font-bold text-on-background mt-1 font-geist flex items-center justify-center">
                            <span class="material-symbols-outlined text-sm text-green-400">verified</span>
                        </p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">Description</h3>
                    <div class="text-sm text-on-surface leading-relaxed whitespace-pre-line"><?= Security::e($product['description']) ?></div>
                </div>
            </div>

            <!-- Reviews Stream section -->
            <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-6">
                <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Customer Reviews (<?= (int)$product['reviews_count'] ?>)</h3>

                <!-- Submit Review form (If user purchased product) -->
                <?php if ($currentUser && $hasPurchased): ?>
                    <form action="/product/<?= Security::e($product['slug']) ?>" method="POST" class="p-4 bg-background/50 rounded-2xl border border-white/5 space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-bold text-on-surface-variant uppercase">Your Score:</span>
                            <div class="flex items-center space-x-1" id="star-selector">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <button type="button" onclick="selectStar(<?= $i ?>)" class="text-on-surface-variant/40 hover:text-yellow-400 focus:outline-none transition-colors">
                                        <span class="material-symbols-outlined text-xl" id="star-<?= $i ?>">star</span>
                                    </button>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="review-rating" value="5"/>
                        </div>

                        <div>
                            <textarea name="review_text" rows="3" required placeholder="Write your review or feedback on this digital product..." class="w-full p-4 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-xs resize-none"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 text-xs shadow-md">
                                Submit Feedback
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

                <!-- Reviews list stream -->
                <?php if (empty($reviews)): ?>
                    <p class="text-xs text-on-surface-variant">No reviews yet for this product. Be the first to purchase and review!</p>
                <?php else: ?>
                    <div class="space-y-4 divide-y divide-white/5">
                        <?php foreach ($reviews as $rev): ?>
                            <div class="pt-4 first:pt-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary text-[9px] font-bold overflow-hidden shrink-0">
                                            <?php if (!empty($rev['avatar_url'])): ?>
                                                <img src="<?= Security::e($rev['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-xs font-bold text-on-background font-geist">@<?= Security::e($rev['username']) ?></span>
                                    </div>

                                    <!-- Stars indicator -->
                                    <div class="flex items-center text-yellow-400">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <span class="material-symbols-outlined text-xs" style="<?= $i <= (int)$rev['rating'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">star</span>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-xs text-on-surface leading-relaxed pl-1"><?= Security::e($rev['review_text']) ?></p>
                                <p class="text-[9px] text-on-surface-variant font-bold uppercase mt-1 pl-1"><?= date('M d, Y', strtotime($rev['created_at'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Purchase Cards & Affiliate Links -->
        <div class="space-y-6">

            <!-- Standard Purchase box -->
            <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 shadow-xl text-center space-y-6">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Purchase Price</span>
                    <h2 class="text-4xl font-bold font-geist text-primary mt-1">$<?= number_format($product['price'], 2) ?></h2>
                </div>

                <?php if ($currentUser && $hasPurchased): ?>
                    <div class="bg-primary/10 border border-primary/20 rounded-2xl p-4 text-left">
                        <div class="flex items-center space-x-2 text-primary font-bold text-sm mb-1">
                            <span class="material-symbols-outlined">verified</span>
                            <span>Product Owned</span>
                        </div>
                        <p class="text-xs text-on-surface-variant">You have purchased this digital asset. Click the button below to download the original files securely.</p>
                    </div>

                    <a href="/product/<?= (int)$product['id'] ?>/download" class="w-full py-4 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition-opacity flex justify-center items-center space-x-2 shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined">download</span>
                        <span>Download Files</span>
                    </a>
                <?php else: ?>
                    <form action="/cart/add" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"/>

                        <button type="submit" class="w-full py-4 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition-opacity flex justify-center items-center space-x-2 shadow-lg shadow-primary/20">
                            <span class="material-symbols-outlined">shopping_cart</span>
                            <span>Add to Cart</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Affiliate recommendation card -->
            <?php if ($currentUser): ?>
                <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4">
                    <div class="flex items-center space-x-2 text-primary">
                        <span class="material-symbols-outlined">share</span>
                        <h3 class="font-bold font-geist text-sm uppercase tracking-wider">Recommend & Earn</h3>
                    </div>
                    <p class="text-xs text-on-surface-variant leading-relaxed">Share this product with your followers! Anyone who buys through your unique recommendation link generates up to 5 levels of affiliate royalties for you!</p>

                    <div class="space-y-2">
                        <input type="text" readonly id="affiliate-link" value="<?= Security::e($referralLink) ?>" class="w-full px-3 py-2.5 bg-background border border-white/15 rounded-xl text-on-surface-variant text-[11px] font-mono outline-none"/>

                        <button onclick="copyAffiliateLink()" class="w-full py-2.5 bg-surface-container-high text-on-background hover:bg-white/5 text-xs font-bold rounded-full transition-all flex justify-center items-center space-x-1.5">
                            <span class="material-symbols-outlined text-sm">content_copy</span>
                            <span id="copy-btn-text">Copy Link</span>
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 text-center">
                    <p class="text-xs text-on-surface-variant"><a href="/auth/login" class="text-primary font-bold hover:underline">Sign In</a> to generate recommendation links and earn commissions on sales.</p>
                </div>
            <?php endif; ?>

        </div>

    </div>
</div>

<script>
    function selectStar(rating) {
        document.getElementById('review-rating').value = rating;
        for(let i=1; i<=5; i++) {
            const star = document.getElementById('star-' + i);
            if (i <= rating) {
                star.style.fontVariationSettings = "'FILL' 1";
                star.classList.add('text-yellow-400');
                star.classList.remove('text-on-surface-variant/40');
            } else {
                star.style.fontVariationSettings = "'FILL' 0";
                star.classList.remove('text-yellow-400');
                star.classList.add('text-on-surface-variant/40');
            }
        }
    }

    function copyAffiliateLink() {
        const copyText = document.getElementById("affiliate-link");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);

        const btnText = document.getElementById("copy-btn-text");
        btnText.innerText = "Copied!";
        setTimeout(() => { btnText.innerText = "Copy Link"; }, 2000);
    }
</script>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
