<?php
use App\Core\Security;
$isCreator = in_array($profileUser['role'], ['creator', 'admin']);
$isSalesPartner = (int)$profileUser['is_sales_partner'] === 1;
?>
<?php if ($isSalesPartner || $isCreator): ?>
    <div id="content-recommendations" class="tab-content space-y-6 hidden">
        <!-- Recommended Products Section -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-1.5">Recommended Offerings</h3>
            <?php if (empty($recommendedProducts)): ?>
                <p class="text-xs text-on-surface-variant font-semibold">No recommended products listed.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach ($recommendedProducts as $rp): ?>
                        <div class="p-4 bg-white rounded-2xl border border-[#E0E6E2] flex justify-between items-center group shadow-sm">
                            <div class="min-w-0 flex items-center space-x-3">
                                <span class="material-symbols-outlined text-[#004D40] text-2xl shrink-0">deployed_code</span>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-[#004D40] group-hover:underline truncate"><?= Security::e($rp['name']) ?></h4>
                                    <p class="text-[9px] text-on-surface-variant font-bold">By @<?= Security::e($rp['creator_username']) ?> • <span class="uppercase text-[#004D40]/80"><?= Security::e($rp['category_name']) ?></span></p>
                                </div>
                            </div>
                            <a href="/product/<?= Security::e($rp['slug']) ?>" class="px-3.5 py-1.5 bg-[#004D40]/10 hover:bg-[#004D40] text-[#004D40] hover:text-white text-[10px] font-bold rounded-full transition-all">Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recommendation social posts feed -->
        <div class="space-y-4 pt-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-1.5">Recommendation Feed</h3>
            <?php if (empty($recommendationFeed)): ?>
                <p class="text-xs text-on-surface-variant font-semibold">No recommendation thread posts yet.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recommendationFeed as $post): ?>
                        <article class="bg-white p-5 rounded-3xl border border-[#E0E6E2] hover:border-[#004D40]/20 hover:shadow-md transition-all shadow-sm">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-[#004D40]/10 flex items-center justify-center text-[#004D40] font-bold overflow-hidden border border-[#004D40]/10">
                                    <?php if (!empty($post['avatar_url'])): ?>
                                        <img src="<?= Security::e($post['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-[#004D40]">@<?= Security::e($post['username']) ?></h4>
                                    <p class="text-[9px] text-on-surface-variant font-extrabold uppercase tracking-wider"><?= date('M d, Y', strtotime($post['created_at'])) ?></p>
                                </div>
                            </div>
                            <p class="text-xs text-on-surface mb-3 leading-relaxed font-semibold"><?= Security::e($post['content']) ?></p>

                            <!-- Recommendation Card embedded -->
                            <div class="p-4 rounded-2xl bg-[#FBFBF9] border border-[#EAE6DF] mb-4 flex items-center justify-between gap-4 shadow-sm">
                                <div class="min-w-0 flex items-center space-x-3">
                                    <span class="material-symbols-outlined text-[#004D40] text-xl shrink-0">deployed_code</span>
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-extrabold text-[#004D40] truncate"><?= Security::e($post['product']['name']) ?></h4>
                                        <p class="text-[9px] text-on-surface-variant font-bold">By @<?= Security::e($post['product']['creator_username']) ?></p>
                                    </div>
                                </div>
                                <a href="/product/<?= Security::e($post['product']['slug']) ?><?= $post['referral_code'] ? '?ref=' . Security::e($post['referral_code']) : '' ?>" class="px-4 py-2 bg-[#004D40] text-white font-bold rounded-full text-[10px] uppercase shadow-sm shrink-0">Buy ($<?= number_format($post['product']['price'], 2) ?>)</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
