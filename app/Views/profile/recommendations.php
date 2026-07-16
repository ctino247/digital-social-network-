<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');

// Include global header
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Back to profile link -->
    <a href="/profile/<?= Security::e($profileUser['username']) ?>" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm font-bold">arrow_back</span>
        <span>Back to @<?= Security::e($profileUser['username']) ?>'s Profile</span>
    </a>

    <!-- Page Header Card -->
    <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-2 shadow-sm">
        <h1 class="text-xl font-extrabold text-[#004D40]">Recommendations by @<?= Security::e($profileUser['username']) ?></h1>
        <p class="text-xs text-on-surface-variant font-bold">Discover high-quality digital assets recommended and shared by @<?= Security::e($profileUser['username']) ?>.</p>
    </div>

    <!-- Recommendations Tabs Area -->
    <div class="space-y-6">
        <!-- 1. Recommended Offerings -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-1.5">Recommended Offerings</h3>
            <?php if (empty($recommendedProducts)): ?>
                <div class="text-center py-8 bg-white rounded-3xl border border-[#E0E6E2] text-on-surface-variant shadow-sm">
                    <span class="material-symbols-outlined text-3xl mb-1 text-[#004D40] opacity-35">deployed_code</span>
                    <p class="text-xs font-bold text-[#004D40]">No recommended products listed.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach ($recommendedProducts as $rp): ?>
                        <div class="p-4 bg-white rounded-3xl border border-[#E0E6E2] flex justify-between items-center group shadow-sm hover:border-[#004D40]/20 hover:shadow-md transition-all">
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

        <!-- 2. Recommendation Social Posts Feed -->
        <div class="space-y-4 pt-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-1.5">Recommendation Feed</h3>
            <?php if (empty($recommendationFeed)): ?>
                <div class="text-center py-8 bg-white rounded-3xl border border-[#E0E6E2] text-on-surface-variant shadow-sm">
                    <span class="material-symbols-outlined text-3xl mb-1 text-[#004D40] opacity-35">rss_feed</span>
                    <p class="text-xs font-bold text-[#004D40]">No recommendation thread posts yet.</p>
                </div>
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
</div>

<?php
// Include global footer
include ROOT_PATH . '/app/Views/partials/footer.php';
?>
