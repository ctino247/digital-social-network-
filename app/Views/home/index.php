<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Main Feed Section -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Feed Toggle Header -->
        <div class="flex space-x-6 border-b border-[#E0E6E2] pb-2">
            <a href="/?feed=for_you" class="text-base font-bold pb-2 transition-all <?= $feedType === 'for_you' ? 'text-[#004D40] border-b-2 border-[#004D40]' : 'text-on-surface-variant hover:text-[#004D40]' ?>">
                For You
            </a>
            <?php if ($currentUser): ?>
                <a href="/?feed=following" class="text-base font-bold pb-2 transition-all <?= $feedType === 'following' ? 'text-[#004D40] border-b-2 border-[#004D40]' : 'text-on-surface-variant hover:text-[#004D40]' ?>">
                    Following
                </a>
            <?php endif; ?>
        </div>

        <!-- Quick Post Trigger (Desktop Only) -->
        <?php if ($currentUser): ?>
            <div class="bg-white p-5 rounded-3xl border border-[#E0E6E2] flex items-center space-x-4 shadow-sm">
                <div class="w-10 h-10 rounded-full bg-[#004D40] flex items-center justify-center text-white font-bold overflow-hidden shrink-0 border border-[#004D40]/10">
                    <?php if (!empty($currentUser['avatar_url'])): ?>
                        <img src="<?= Security::e($currentUser['avatar_url']) ?>" class="w-full h-full object-cover"/>
                    <?php else: ?>
                        <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <a href="/post/create" class="flex-1 px-5 py-3 bg-[#F5F7F4] border border-[#E0E6E2] text-on-surface-variant hover:text-[#004D40] rounded-full text-xs font-semibold transition-all">
                    What's on your mind? Share a post or start a poll...
                </a>
                <a href="/post/create" class="text-[#004D40] hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-2xl font-bold">add_box</span>
                </a>
            </div>
        <?php endif; ?>

        <!-- Posts List -->
        <div class="space-y-5">
            <?php if (empty($posts)): ?>
                <div class="text-center py-16 bg-white rounded-3xl border border-[#E0E6E2] text-on-surface-variant shadow-sm">
                    <span class="material-symbols-outlined text-5xl mb-3 text-[#004D40]/30">feed</span>
                    <h3 class="text-lg font-extrabold text-[#004D40]">Feed is empty</h3>
                    <p class="text-xs mt-1 max-w-xs mx-auto text-on-surface-variant/80">Follow creators or be the first to post something amazing!</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="bg-white p-6 rounded-3xl border border-[#E0E6E2]/80 hover:border-[#004D40]/20 hover:shadow-md transition-all flex flex-col relative overflow-hidden">

                        <!-- Header: User Info -->
                        <div class="flex items-center space-x-3 mb-4">
                            <a href="/profile/<?= Security::e($post['username']) ?>" class="w-10 h-10 rounded-full bg-[#F5F7F4] border border-[#E0E6E2] flex items-center justify-center text-[#004D40] font-bold overflow-hidden shrink-0">
                                <?php if (!empty($post['avatar_url'])): ?>
                                    <img src="<?= Security::e($post['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                <?php else: ?>
                                    <?= strtoupper(substr($post['username'], 0, 1)) ?>
                                <?php endif; ?>
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="/profile/<?= Security::e($post['username']) ?>" class="text-sm font-bold text-[#004D40] hover:underline truncate block">@<?= Security::e($post['username']) ?></a>
                                <p class="text-[10px] text-on-surface-variant uppercase font-semibold tracking-wider"><?= date('M d, Y h:i A', strtotime($post['created_at'])) ?></p>
                            </div>
                            <a href="/post/<?= (int)$post['id'] ?>" class="text-on-surface-variant hover:text-[#004D40]">
                                <span class="material-symbols-outlined text-lg">open_in_new</span>
                            </a>
                        </div>

                        <!-- Body: Content -->
                        <div class="pl-1 mb-4">
                            <p class="text-sm text-[#0F211C] leading-relaxed whitespace-pre-line font-medium"><?= Security::e($post['content']) ?></p>
                        </div>

                        <!-- Embedded Poll if exists -->
                        <?php if (!empty($post['poll'])): ?>
                            <div class="bg-[#F5F7F4] p-5 rounded-2xl border border-[#E0E6E2] mb-4 space-y-3" id="poll-box-<?= (int)$post['poll']['id'] ?>">
                                <h4 class="text-xs font-extrabold text-[#004D40] uppercase tracking-wider mb-2">Poll: <?= Security::e($post['poll']['question']) ?></h4>

                                <div class="space-y-2">
                                    <?php foreach ($post['poll']['options'] as $option): ?>
                                        <?php
                                        $votedClass = ($post['poll']['user_voted_option_id'] === (int)$option['id']) ? 'border-[#004D40] bg-[#004D40]/5 text-[#004D40]' : 'border-[#E0E6E2] hover:border-[#004D40]/30 text-on-surface';
                                        $percent = $post['poll']['total_votes'] > 0 ? round(($option['votes_count'] / $post['poll']['total_votes']) * 100) : 0;
                                        ?>
                                        <button
                                            onclick="votePoll(<?= (int)$post['poll']['id'] ?>, <?= (int)$option['id'] ?>, <?= (int)$post['id'] ?>)"
                                            <?= $post['poll']['has_voted'] || $post['poll']['is_expired'] ? 'disabled' : '' ?>
                                            class="w-full text-left p-3 rounded-xl border <?= $votedClass ?> transition-all flex justify-between items-center text-xs font-bold relative overflow-hidden"
                                        >
                                            <!-- Percentage fill backdrop -->
                                            <?php if ($post['poll']['has_voted'] || $post['poll']['is_expired']): ?>
                                                <div class="absolute top-0 left-0 bottom-0 bg-[#004D40]/10 pointer-events-none" style="width: <?= $percent ?>%"></div>
                                            <?php endif; ?>

                                            <span class="relative z-10"><?= Security::e($option['option_text']) ?></span>
                                            <span class="relative z-10 text-on-surface-variant font-bold"><?= $percent ?>% (<?= (int)$option['votes_count'] ?>)</span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <p class="text-[10px] text-on-surface-variant font-semibold mt-1">Total Votes: <span id="poll-total-<?= (int)$post['id'] ?>"><?= (int)$post['poll']['total_votes'] ?></span> • <?= $post['poll']['is_expired'] ? 'Closed' : 'Active' ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Recommendation Product Card (Redesigned with Premium Off-White Luxury look from Mockup) -->
                        <?php if (!empty($post['product'])): ?>
                            <div class="p-5 rounded-3xl bg-[#FBF9F6] border border-[#EAE6DF] mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group shadow-sm">
                                <div class="flex items-center space-x-4">
                                    <div class="w-14 h-14 rounded-2xl bg-[#004D40]/10 flex items-center justify-center text-[#004D40] shrink-0 relative">
                                        <span class="material-symbols-outlined text-3xl">deployed_code</span>
                                        <span class="absolute -top-1.5 -right-1.5 px-2 py-0.5 bg-[#FFE500] text-[#004D40] text-[8px] font-bold rounded-full uppercase tracking-wider shadow-sm"><?= Security::e($post['product']['type']) ?></span>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-extrabold text-sm text-[#004D40] group-hover:underline transition-all truncate"><?= Security::e($post['product']['name']) ?></h4>
                                        <p class="text-xs text-on-surface-variant mt-0.5">By <span class="font-semibold text-[#004D40]/80">@<?= Security::e($post['product']['creator_username']) ?></span></p>
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
                                    <a href="/product/<?= Security::e($post['product']['slug']) ?><?= $post['referral_code'] ? '?ref=' . Security::e($post['referral_code']) : '' ?>" class="px-5 py-2.5 bg-[#004D40] text-white font-bold rounded-full hover:bg-[#00332A] text-xs shadow-md">
                                        Buy Now ($<?= number_format($post['product']['price'], 2) ?>)
                                    </a>
                                    <?php if ($currentUser && $currentUser['is_sales_partner']): ?>
                                        <a href="/product/<?= Security::e($post['product']['slug']) ?>" class="p-2.5 rounded-full bg-white border border-[#E0E6E2] text-[#004D40] hover:bg-[#F5F7F4] transition-colors" title="Recommend Product">
                                            <span class="material-symbols-outlined text-sm font-bold">share</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Embedded Quoted Post if exists -->
                        <?php if (!empty($post['quoted_post'])): ?>
                            <div class="p-4 rounded-2xl bg-[#F5F7F4] border border-[#E0E6E2] mb-4">
                                <div class="flex items-center space-x-2 mb-2">
                                    <div class="w-6 h-6 rounded-full bg-[#004D40]/10 flex items-center justify-center text-[#004D40] text-[10px] font-bold overflow-hidden border border-[#004D40]/10">
                                        <?php if (!empty($post['quoted_post']['avatar_url'])): ?>
                                            <img src="<?= Security::e($post['quoted_post']['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-xs font-bold text-[#004D40]">@<?= Security::e($post['quoted_post']['username']) ?></span>
                                </div>
                                <p class="text-xs text-[#0F211C] line-clamp-3 leading-relaxed"><?= Security::e($post['quoted_post']['content']) ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Action Bar (Likes, replies, quotes, bookmarks) -->
                        <div class="flex items-center justify-between text-xs font-bold text-on-surface-variant border-t border-[#E0E6E2] pt-4 mt-2">
                            <button onclick="toggleLike(<?= (int)$post['id'] ?>, this)" class="flex items-center space-x-2 <?= $post['is_liked'] ? 'text-primary' : 'hover:text-primary' ?> transition-all">
                                <span class="material-symbols-outlined text-lg" style="<?= $post['is_liked'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">favorite</span>
                                <span><?= (int)$post['likes_count'] ?></span>
                            </button>
                            <a href="/post/<?= (int)$post['id'] ?>" class="flex items-center space-x-2 hover:text-primary transition-all">
                                <span class="material-symbols-outlined text-lg">chat_bubble</span>
                                <span><?= (int)$post['replies_count'] ?></span>
                            </a>
                            <a href="/post/create?quote_id=<?= (int)$post['id'] ?>" class="flex items-center space-x-2 hover:text-primary transition-all">
                                <span class="material-symbols-outlined text-lg">format_quote</span>
                                <span>Quote</span>
                            </a>
                            <button onclick="toggleBookmark(<?= (int)$post['id'] ?>, this)" class="flex items-center space-x-2 <?= $post['is_bookmarked'] ? 'text-primary' : 'hover:text-primary' ?> transition-all">
                                <span class="material-symbols-outlined text-lg" style="<?= $post['is_bookmarked'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">bookmark</span>
                                <span>Bookmark</span>
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Section (Trends & Alerts) -->
    <div class="hidden lg:block space-y-6">

        <!-- Platform Announcements -->
        <?php if (!empty($announcements)): ?>
            <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
                <div class="flex items-center space-x-2 text-[#004D40]">
                    <span class="material-symbols-outlined font-bold">campaign</span>
                    <h3 class="font-extrabold text-sm uppercase tracking-wider">Announcements</h3>
                </div>
                <div class="space-y-3 divide-y divide-[#E0E6E2]">
                    <?php foreach ($announcements as $ann): ?>
                        <div class="pt-2.5 first:pt-0">
                            <h4 class="text-xs font-bold text-[#004D40] mb-1"><?= Security::e($ann['title']) ?></h4>
                            <p class="text-[11px] text-on-surface-variant leading-relaxed"><?= Security::e($ann['content']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Trending Topics -->
        <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
            <div class="flex items-center space-x-2 text-[#004D40]">
                <span class="material-symbols-outlined font-bold">trending_up</span>
                <h3 class="font-extrabold text-sm uppercase tracking-wider">Trending Topics</h3>
            </div>

            <?php if (empty($trendingHashtags)): ?>
                <p class="text-xs text-on-surface-variant">No active topics yet.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($trendingHashtags as $hash): ?>
                        <a href="/explore?q=%23<?= urlencode($hash['tag']) ?>" class="block group">
                            <h4 class="text-xs font-bold text-[#004D40] group-hover:underline">#<?= Security::e($hash['tag']) ?></h4>
                            <p class="text-[10px] text-on-surface-variant mt-0.5"><?= (int)$hash['count'] ?> threads</p>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
    async function toggleLike(postId, btn) {
        const token = '<?= $csrf_token ?>';
        try {
            const res = await fetch('/post/' + postId + '/like', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token },
                body: 'csrf_token=' + encodeURIComponent(token)
            });
            const data = await res.json();
            if (data.success) {
                const countSpan = btn.querySelector('span:last-child');
                const iconSpan = btn.querySelector('span:first-child');
                countSpan.innerText = data.likesCount;
                if (data.action === 'liked') {
                    btn.classList.add('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 1";
                } else {
                    btn.classList.remove('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 0";
                }
            }
        } catch (e) { console.error(e); }
    }

    async function toggleBookmark(postId, btn) {
        const token = '<?= $csrf_token ?>';
        try {
            const res = await fetch('/post/' + postId + '/bookmark', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token },
                body: 'csrf_token=' + encodeURIComponent(token)
            });
            const data = await res.json();
            if (data.success) {
                const iconSpan = btn.querySelector('span:first-child');
                if (data.action === 'bookmarked') {
                    btn.classList.add('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 1";
                } else {
                    btn.classList.remove('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 0";
                }
            }
        } catch (e) { console.error(e); }
    }

    async function votePoll(pollId, optionId, postId) {
        const token = '<?= $csrf_token ?>';
        try {
            const res = await fetch('/poll/vote', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token },
                body: 'csrf_token=' + encodeURIComponent(token) + '&poll_id=' + pollId + '&option_id=' + optionId
            });
            const data = await res.json();
            if (data.success) {
                // Refresh poll UI dynamically
                const pollBox = document.getElementById('poll-box-' + pollId);
                let html = '<h4 class="text-xs font-bold text-[#004D40] uppercase tracking-wider mb-2">Poll results:</h4><div class="space-y-2">';
                data.options.forEach(opt => {
                    const percent = data.total > 0 ? Math.round((opt.votes / data.total) * 100) : 0;
                    const selectedBorder = (opt.id == optionId) ? 'border-[#004D40] bg-[#004D40]/5 text-[#004D40]' : 'border-[#E0E6E2] text-on-surface';
                    html += `<div class="w-full text-left p-3 rounded-xl border ${selectedBorder} text-xs font-semibold relative overflow-hidden">
                        <div class="absolute top-0 left-0 bottom-0 bg-[#004D40]/10 pointer-events-none" style="width: ${percent}%"></div>
                        <span class="relative z-10">${opt.option_text}</span>
                        <span class="relative z-10 text-on-surface-variant font-bold">${percent}% (${opt.votes})</span>
                    </div>`;
                });
                html += `</div><p class="text-[10px] text-on-surface-variant font-semibold mt-1">Total Votes: ${data.total} • Completed</p>`;
                pollBox.innerHTML = html;
            } else {
                alert(data.error || 'Unable to vote.');
            }
        } catch (e) { console.error(e); }
    }
</script>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
