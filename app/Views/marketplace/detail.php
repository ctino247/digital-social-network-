<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';

$isCreator = ($currentUser && (int)$product['creator_id'] === (int)$currentUser['id']);
?>

<div class="space-y-6">
    <!-- Back Link -->
    <a href="/marketplace" class="inline-flex items-center space-x-2 text-xs font-bold text-[#004D40] hover:underline transition-colors">
        <span class="material-symbols-outlined text-sm font-bold">arrow_back</span>
        <span>Back to Catalog</span>
    </a>

    <!-- Main Grid: Showcase Details & Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left: Product Specs & Content -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] relative overflow-hidden shadow-sm">
                <!-- Banner decorative accent (amber line from original-dc9a931bf81d755b4d3a031e6d21f8fa.png) -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#FFE500]"></div>

                <div class="flex items-center space-x-3 mb-4 mt-2">
                    <span class="px-3 py-1 bg-[#004D40]/10 text-[#004D40] border border-[#004D40]/20 text-[9px] font-extrabold uppercase rounded-full tracking-wider"><?= Security::e($product['type']) ?></span>
                    <span class="text-xs text-on-surface-variant font-bold">• Uploaded in <?= Security::e($product['category_name']) ?></span>
                </div>

                <h1 class="text-2xl font-extrabold text-[#004D40] leading-tight mb-2"><?= Security::e($product['name']) ?></h1>

                <!-- Creator Bio Header block (Styled with luxurious borders and off-white backdrop) -->
                <div class="flex items-center space-x-3 mb-6 bg-[#FBFBF9] p-4 rounded-2xl border border-[#E0E6E2]/60">
                    <div class="w-10 h-10 rounded-full bg-[#004D40]/10 flex items-center justify-center text-[#004D40] font-bold overflow-hidden text-sm shrink-0 border border-[#004D40]/10">
                        <?php if (!empty($product['creator_avatar'])): ?>
                            <img src="<?= Security::e($product['creator_avatar']) ?>" class="w-full h-full object-cover"/>
                        <?php else: ?>
                            <?= strtoupper(substr($product['creator_username'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center space-x-1.5">
                            <a href="/profile/<?= Security::e($product['creator_username']) ?>" class="text-xs text-[#004D40] font-extrabold hover:underline">@<?= Security::e($product['creator_username']) ?></a>
                            <span class="px-2 py-0.5 bg-[#004D40]/10 text-[#004D40] text-[8px] font-bold uppercase rounded-full border border-[#004D40]/20">Creator Partner</span>
                        </div>
                        <p class="text-[10px] text-on-surface-variant font-bold uppercase mt-0.5"><?= $creatorFollowers ?> Followers</p>
                    </div>
                </div>

                <!-- Product Gallery Section (Styled like elegant frames from original-dc9a931bf81d755b4d3a031e6d21f8fa.png details page) -->
                <div class="space-y-3 mb-6">
                    <h3 class="text-[10px] font-extrabold uppercase tracking-wider text-[#004D40] mb-2">Product Gallery</h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="aspect-video bg-[#FBFBF9] rounded-2xl border border-[#E0E6E2] flex items-center justify-center text-[#004D40] hover:border-[#004D40]/30 transition-all cursor-pointer shadow-sm">
                            <span class="material-symbols-outlined text-2xl">image</span>
                        </div>
                        <div class="aspect-video bg-[#FBFBF9] rounded-2xl border border-[#E0E6E2] flex items-center justify-center text-[#004D40]/40 hover:border-[#004D40]/30 transition-all cursor-pointer shadow-sm">
                            <span class="material-symbols-outlined text-2xl">play_circle</span>
                        </div>
                        <div class="aspect-video bg-[#FBFBF9] rounded-2xl border border-[#E0E6E2] flex items-center justify-center text-[#004D40]/40 hover:border-[#004D40]/30 transition-all cursor-pointer shadow-sm">
                            <span class="material-symbols-outlined text-2xl">photo_library</span>
                        </div>
                    </div>
                </div>

                <!-- Specs stats cards -->
                <div class="grid grid-cols-3 gap-4 border-y border-[#E0E6E2]/60 py-4 mb-6">
                    <div class="text-center">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant">Avg Rating</p>
                        <p class="text-sm font-extrabold text-[#004D40] mt-1 flex items-center justify-center space-x-1">
                            <span class="material-symbols-outlined text-xs text-amber-500" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span><?= $product['avg_rating'] ? number_format($product['avg_rating'], 1) : '5.0' ?></span>
                        </p>
                    </div>
                    <div class="text-center border-x border-[#E0E6E2]/60">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant">Reviews count</p>
                        <p class="text-sm font-extrabold text-[#004D40] mt-1"><?= (int)$product['reviews_count'] ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-on-surface-variant">Total Sales</p>
                        <p class="text-sm font-extrabold text-[#004D40] mt-1"><?= $salesCount ?> sold</p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="text-[10px] font-extrabold uppercase tracking-wider text-[#004D40] mb-2">Description</h3>
                    <div class="text-xs text-[#0F211C] leading-relaxed whitespace-pre-line font-medium"><?= Security::e($product['description']) ?></div>
                </div>
            </div>

            <!-- Similar & Recommended Products Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Similar Products -->
                <div class="bg-white p-5 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-2">Similar Products</h3>
                    <?php if (empty($similarProducts)): ?>
                        <p class="text-xs text-on-surface-variant font-semibold">No similar products found.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($similarProducts as $sp): ?>
                                <a href="/product/<?= Security::e($sp['slug']) ?>" class="flex items-center justify-between p-3 rounded-2xl bg-[#FBFBF9] hover:bg-white border border-[#E0E6E2] hover:border-[#004D40]/30 transition-all group">
                                    <div class="min-w-0 flex items-center space-x-2.5">
                                        <span class="material-symbols-outlined text-[#004D40] text-xl shrink-0">deployed_code</span>
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-bold text-[#004D40] group-hover:underline truncate"><?= Security::e($sp['name']) ?></h4>
                                            <p class="text-[9px] text-on-surface-variant font-bold">By @<?= Security::e($sp['creator_username']) ?></p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-[#004D40] shrink-0">$<?= number_format($sp['price'], 2) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recommended Products -->
                <div class="bg-white p-5 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-2">Other Recommended</h3>
                    <?php if (empty($recommendedProducts)): ?>
                        <p class="text-xs text-on-surface-variant font-semibold">No other recommendations available.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($recommendedProducts as $rp): ?>
                                <a href="/product/<?= Security::e($rp['slug']) ?>" class="flex items-center justify-between p-3 rounded-2xl bg-[#FBFBF9] hover:bg-white border border-[#E0E6E2] hover:border-[#004D40]/30 transition-all group">
                                    <div class="min-w-0 flex items-center space-x-2.5">
                                        <span class="material-symbols-outlined text-[#004D40] text-xl shrink-0">deployed_code</span>
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-bold text-[#004D40] group-hover:underline truncate"><?= Security::e($rp['name']) ?></h4>
                                            <p class="text-[9px] text-on-surface-variant font-bold">By @<?= Security::e($rp['creator_username']) ?></p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-[#004D40] shrink-0">$<?= number_format($rp['price'], 2) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviews Stream section -->
            <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-6 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-2">Customer Reviews (<?= (int)$product['reviews_count'] ?>)</h3>

                <!-- Submit Review form (If user purchased product) -->
                <?php if ($currentUser && $hasPurchased): ?>
                    <form action="/product/<?= Security::e($product['slug']) ?>" method="POST" class="p-4 bg-[#FBFBF9] rounded-2xl border border-[#E0E6E2] space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-bold text-on-surface-variant uppercase">Your Score:</span>
                            <div class="flex items-center space-x-1" id="star-selector">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <button type="button" onclick="selectStar(<?= $i ?>)" class="text-on-surface-variant/40 hover:text-amber-500 focus:outline-none transition-colors">
                                        <span class="material-symbols-outlined text-xl" id="star-<?= $i ?>">star</span>
                                    </button>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="review-rating" value="5"/>
                        </div>

                        <div>
                            <textarea name="review_text" rows="3" required placeholder="Write your review or feedback on this digital product..." class="w-full p-4 bg-white border border-[#E0E6E2] rounded-2xl text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none text-xs resize-none font-medium"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-[#004D40] text-white font-bold rounded-full hover:bg-[#00332A] text-xs shadow-md">
                                Submit Feedback
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

                <!-- Reviews list stream -->
                <?php if (empty($reviews)): ?>
                    <p class="text-xs text-on-surface-variant font-semibold">No reviews yet for this product. Be the first to purchase and review!</p>
                <?php else: ?>
                    <div class="space-y-4 divide-y divide-[#E0E6E2]">
                        <?php foreach ($reviews as $rev): ?>
                            <div class="pt-4 first:pt-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full bg-[#004D40]/10 flex items-center justify-center text-[#004D40] text-[9px] font-bold overflow-hidden shrink-0 border border-[#004D40]/10">
                                            <?php if (!empty($rev['avatar_url'])): ?>
                                                <img src="<?= Security::e($rev['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-xs font-bold text-[#004D40]">@<?= Security::e($rev['username']) ?></span>
                                    </div>

                                    <!-- Stars indicator -->
                                    <div class="flex items-center text-amber-500">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <span class="material-symbols-outlined text-xs" style="<?= $i <= (int)$rev['rating'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">star</span>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-xs text-[#0F211C] leading-relaxed pl-1 font-medium"><?= Security::e($rev['review_text']) ?></p>
                                <p class="text-[9px] text-on-surface-variant font-extrabold uppercase mt-1 pl-1 tracking-wider"><?= date('M d, Y', strtotime($rev['created_at'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Purchase Cards & Affiliate Links -->
        <div class="space-y-6">

            <!-- Standard Purchase box -->
            <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] shadow-sm text-center space-y-6">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant">Purchase Price</span>
                    <h2 class="text-4xl font-extrabold text-[#004D40] mt-1">$<?= number_format($product['price'], 2) ?></h2>
                </div>

                <?php if ($currentUser && $hasPurchased): ?>
                    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-left">
                        <div class="flex items-center space-x-2 text-emerald-800 font-bold text-sm mb-1">
                            <span class="material-symbols-outlined text-emerald-600 font-bold">verified</span>
                            <span>Product Owned</span>
                        </div>
                        <p class="text-xs text-on-surface-variant font-medium">You have purchased this digital asset. Click the button below to download the original files securely.</p>
                    </div>

                    <a href="/product/<?= (int)$product['id'] ?>/download" class="w-full py-4 bg-[#004D40] text-white font-extrabold rounded-full hover:bg-[#00332A] transition-colors flex justify-center items-center space-x-2 shadow-md">
                        <span class="material-symbols-outlined">download</span>
                        <span>Download Files</span>
                    </a>
                <?php else: ?>
                    <div class="space-y-3">
                        <form action="/cart/buy-now" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"/>

                            <button type="submit" class="w-full py-4 bg-[#FFE500] text-[#004D40] font-extrabold rounded-full hover:bg-[#E6CE00] transition-colors flex justify-center items-center space-x-2 shadow-sm">
                                <span class="material-symbols-outlined font-bold">bolt</span>
                                <span>Buy Now</span>
                            </button>
                        </form>

                        <form action="/cart/add" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>
                            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"/>

                            <button type="submit" class="w-full py-3 border border-[#E0E6E2] hover:bg-[#F5F7F4] text-[#004D40] font-bold rounded-full transition-all flex justify-center items-center space-x-2 text-xs">
                                <span class="material-symbols-outlined font-bold text-sm">shopping_cart</span>
                                <span>Add to Cart</span>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Creator Specific Buttons -->
                <?php if ($isCreator): ?>
                    <div class="pt-4 border-t border-[#E0E6E2] space-y-2.5">
                        <a href="/creator/products/<?= (int)$product['id'] ?>/edit" class="block w-full py-3 bg-[#FBFBF9] hover:bg-[#F5F7F4] border border-[#E0E6E2] text-[#004D40] font-bold rounded-full text-xs transition-all">
                            Edit Product Description
                        </a>
                        <a href="/creator/dashboard" class="block w-full py-3 bg-[#004D40]/10 text-[#004D40] font-bold rounded-full text-xs hover:bg-[#004D40] hover:text-white transition-all">
                            View Product Analytics
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Affiliate recommendation card (Only visible if visitor is a Sales Partner) -->
            <?php if ($currentUser && $currentUser['is_sales_partner']): ?>
                <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
                    <div class="flex items-center space-x-2 text-[#004D40]">
                        <span class="material-symbols-outlined font-bold">share</span>
                        <h3 class="font-extrabold text-sm uppercase tracking-wider">Recommend Product</h3>
                    </div>
                    <p class="text-xs text-on-surface-variant leading-relaxed font-semibold">As an active Sales Partner, you can recommend this digital asset to earn up to 5 levels of commission splits!</p>

                    <div class="space-y-3">
                        <div>
                            <p class="text-[9px] font-extrabold text-[#004D40]/75 uppercase tracking-wider mb-1.5">Your Unique Referral Link</p>
                            <input type="text" readonly id="affiliate-link" value="<?= Security::e($referralLink) ?>" class="w-full px-3 py-3 bg-[#F5F7F4] border border-[#E0E6E2] rounded-xl text-on-surface-variant text-[11px] font-mono outline-none font-bold shadow-inner"/>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <button onclick="copyAffiliateLink()" class="py-2.5 bg-white text-[#004D40] hover:bg-[#F5F7F4] border border-[#E0E6E2] text-[10px] font-bold rounded-full transition-all flex justify-center items-center space-x-1">
                                <span class="material-symbols-outlined text-xs font-bold">content_copy</span>
                                <span id="copy-btn-text">Copy Link</span>
                            </button>
                            <a href="/post/create?product_id=<?= (int)$product['id'] ?>&ref=<?= Security::e($referralCodeKey) ?>" class="py-2.5 bg-[#004D40]/10 text-[#004D40] hover:bg-[#004D40] hover:text-white border border-[#004D40]/20 text-[10px] font-bold rounded-full transition-all flex justify-center items-center space-x-1">
                                <span class="material-symbols-outlined text-xs font-bold">send</span>
                                <span>Share Internally</span>
                            </a>
                        </div>
                        <a href="/recommendation/<?= Security::e($referralCodeKey) ?>/analytics" class="block text-center w-full py-3 bg-[#F5F7F4] hover:bg-[#EBF0EC] text-[10px] font-bold text-[#004D40] rounded-full transition-all border border-[#E0E6E2]">
                            View Recommendation Analytics
                        </a>
                    </div>
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
                star.classList.add('text-amber-500');
                star.classList.remove('text-on-surface-variant/40');
            } else {
                star.style.fontVariationSettings = "'FILL' 0";
                star.classList.remove('text-amber-500');
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
