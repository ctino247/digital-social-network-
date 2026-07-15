<?php
use App\Core\Security;
?>
<div id="content-posts" class="tab-content space-y-4">
    <?php if (empty($posts)): ?>
        <div class="text-center py-12 bg-white rounded-3xl border border-[#E0E6E2] text-on-surface-variant shadow-sm">
            <span class="material-symbols-outlined text-4xl mb-2 text-[#004D40]/30">article</span>
            <p class="text-xs font-bold text-[#004D40]">No posts yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article class="bg-white p-6 rounded-3xl border border-[#E0E6E2] hover:border-[#004D40]/20 hover:shadow-md transition-all">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-[#004D40]/10 flex items-center justify-center text-[#004D40] font-bold overflow-hidden border border-[#004D40]/10">
                        <?php if (!empty($post['avatar_url'])): ?>
                            <img src="<?= Security::e($post['avatar_url']) ?>" class="w-full h-full object-cover"/>
                        <?php else: ?>
                            <?= strtoupper(substr($post['username'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-[#004D40]">@<?= Security::e($post['username']) ?></h4>
                        <p class="text-[9px] text-on-surface-variant font-extrabold uppercase tracking-wider"><?= date('M d, Y h:i A', strtotime($post['created_at'])) ?></p>
                    </div>
                </div>
                <p class="text-xs text-[#0F211C] leading-relaxed mb-4 whitespace-pre-line font-semibold"><?= Security::e($post['content']) ?></p>

                <!-- Recommendation Product Card if exists -->
                <?php if (!empty($post['product'])): ?>
                    <div class="p-5 rounded-3xl bg-[#FBFBF9] border border-[#E0E6E2] mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group shadow-sm">
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 rounded-2xl bg-[#004D40]/10 flex items-center justify-center text-[#004D40] shrink-0 relative border border-[#004D40]/10">
                                <span class="material-symbols-outlined text-3xl">deployed_code</span>
                                <span class="absolute -top-1.5 -right-1.5 px-2 py-0.5 bg-[#FFE500] text-[#004D40] text-[8px] font-bold rounded-full uppercase tracking-wider shadow-sm"><?= Security::e($post['product']['type']) ?></span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-sm text-[#004D40] group-hover:underline truncate"><?= Security::e($post['product']['name']) ?></h4>
                                <p class="text-xs text-on-surface-variant">By <span class="font-semibold text-[#004D40]/80">@<?= Security::e($post['product']['creator_username']) ?></span></p>
                                <div class="flex items-center space-x-2 mt-1.5 text-[10px] text-on-surface-variant font-bold uppercase tracking-wider">
                                    <span class="flex items-center text-amber-500">
                                        <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
                                        <span class="ml-1 text-[#004D40] font-bold"><?= $post['product']['avg_rating'] ? number_format($post['product']['avg_rating'], 1) : '5.0' ?></span>
                                    </span>
                                    <span>•</span>
                                    <span><?= (int)$post['product']['sales_count'] ?> sales</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2 shrink-0">
                            <a href="/product/<?= Security::e($post['product']['slug']) ?><?= $post['referral_code'] ? '?ref=' . Security::e($post['referral_code']) : '' ?>" class="px-5 py-2.5 bg-[#004D40] text-white font-bold rounded-full text-xs shadow-md">
                                Buy Now ($<?= number_format($post['product']['price'], 2) ?>)
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Action Bar -->
                <div class="flex items-center space-x-6 text-xs font-bold text-on-surface-variant border-t border-[#E0E6E2]/40 pt-3 mt-2">
                    <button data-action="like" data-post-id="<?= (int)$post['id'] ?>" class="like-btn flex items-center space-x-1.5 <?= $post['is_liked'] ? 'text-[#004D40]' : 'hover:text-[#004D40]' ?> transition-colors">
                        <span class="material-symbols-outlined text-lg" style="<?= $post['is_liked'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">favorite</span>
                        <span class="like-count"><?= (int)$post['likes_count'] ?></span>
                    </button>
                    <a href="/post/<?= (int)$post['id'] ?>" class="flex items-center space-x-1.5 hover:text-[#004D40] transition-colors">
                        <span class="material-symbols-outlined text-lg">chat_bubble</span>
                        <span><?= (int)$post['replies_count'] ?></span>
                    </a>
                    <button data-action="bookmark" data-post-id="<?= (int)$post['id'] ?>" class="bookmark-btn flex items-center space-x-1.5 <?= $post['is_bookmarked'] ? 'text-[#004D40]' : 'hover:text-[#004D40]' ?> transition-colors">
                        <span class="material-symbols-outlined text-lg" style="<?= $post['is_bookmarked'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">bookmark</span>
                        <span>Bookmark</span>
                    </button>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
