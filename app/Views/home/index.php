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
        <div class="flex space-x-6 border-b border-white/5 pb-2">
            <a href="/?feed=for_you" class="text-base font-bold pb-2 transition-all <?= $feedType === 'for_you' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary' ?>">
                For You
            </a>
            <?php if ($currentUser): ?>
                <a href="/?feed=following" class="text-base font-bold pb-2 transition-all <?= $feedType === 'following' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:text-primary' ?>">
                    Following
                </a>
            <?php endif; ?>
        </div>

        <!-- Quick Post Trigger (Desktop Only) -->
        <?php if ($currentUser): ?>
            <div class="bg-surface-container-low p-4 rounded-3xl border border-white/5 flex items-center space-x-4">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden shrink-0">
                    <?php if (!empty($currentUser['avatar_url'])): ?>
                        <img src="<?= Security::e($currentUser['avatar_url']) ?>" class="w-full h-full object-cover"/>
                    <?php else: ?>
                        <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <a href="/post/create" class="flex-1 px-5 py-3 bg-background border border-white/5 text-on-surface-variant hover:text-on-surface rounded-full text-sm font-medium transition-colors cursor-pointer">
                    What's on your mind? Create a post or a poll...
                </a>
                <a href="/post/create" class="text-primary hover:scale-105 transition-transform">
                    <span class="material-symbols-outlined text-2xl">add_box</span>
                </a>
            </div>
        <?php endif; ?>

        <!-- Posts List -->
        <div class="space-y-4">
            <?php if (empty($posts)): ?>
                <div class="text-center py-16 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
                    <span class="material-symbols-outlined text-5xl mb-3 opacity-55">feed</span>
                    <h3 class="text-lg font-bold font-geist text-on-background">Feed is empty</h3>
                    <p class="text-xs mt-1 max-w-xs mx-auto">Follow some creators or be the first to create an amazing text post!</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <article class="bg-surface-container-low p-5 rounded-3xl border border-white/5 hover:border-white/10 transition-all flex flex-col">

                        <!-- Header: User Info -->
                        <div class="flex items-center space-x-3 mb-3">
                            <a href="/profile/<?= Security::e($post['username']) ?>" class="w-10 h-10 rounded-full bg-primary-container/10 flex items-center justify-center text-primary font-bold overflow-hidden shrink-0">
                                <?php if (!empty($post['avatar_url'])): ?>
                                    <img src="<?= Security::e($post['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                <?php else: ?>
                                    <?= strtoupper(substr($post['username'], 0, 1)) ?>
                                <?php endif; ?>
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="/profile/<?= Security::e($post['username']) ?>" class="text-sm font-bold font-geist text-on-background hover:text-primary transition-colors truncate block">@<?= Security::e($post['username']) ?></a>
                                <p class="text-[10px] text-on-surface-variant uppercase font-semibold"><?= date('M d, Y h:i A', strtotime($post['created_at'])) ?></p>
                            </div>
                            <a href="/post/<?= (int)$post['id'] ?>" class="text-on-surface-variant hover:text-primary">
                                <span class="material-symbols-outlined text-lg">open_in_new</span>
                            </a>
                        </div>

                        <!-- Body: Content -->
                        <div class="pl-1 mb-4">
                            <p class="text-sm text-on-surface leading-relaxed whitespace-pre-line"><?= Security::e($post['content']) ?></p>
                        </div>

                        <!-- Embedded Poll if exists -->
                        <?php if (!empty($post['poll'])): ?>
                            <div class="bg-background/40 p-5 rounded-2xl border border-white/5 mb-4 space-y-3" id="poll-box-<?= (int)$post['poll']['id'] ?>">
                                <h4 class="text-xs font-bold text-on-background uppercase tracking-wider mb-2">Poll: <?= Security::e($post['poll']['question']) ?></h4>

                                <div class="space-y-2">
                                    <?php foreach ($post['poll']['options'] as $option): ?>
                                        <?php
                                        $votedClass = ($post['poll']['user_voted_option_id'] === (int)$option['id']) ? 'border-primary bg-primary/5 text-primary' : 'border-white/10 hover:border-primary/20 text-on-background';
                                        $percent = $post['poll']['total_votes'] > 0 ? round(($option['votes_count'] / $post['poll']['total_votes']) * 100) : 0;
                                        ?>
                                        <button
                                            onclick="votePoll(<?= (int)$post['poll']['id'] ?>, <?= (int)$option['id'] ?>, <?= (int)$post['id'] ?>)"
                                            <?= $post['poll']['has_voted'] || $post['poll']['is_expired'] ? 'disabled' : '' ?>
                                            class="w-full text-left p-3 rounded-xl border <?= $votedClass ?> transition-all flex justify-between items-center text-xs font-semibold relative overflow-hidden"
                                        >
                                            <!-- Percentage fill backdrop -->
                                            <?php if ($post['poll']['has_voted'] || $post['poll']['is_expired']): ?>
                                                <div class="absolute top-0 left-0 bottom-0 bg-primary/5 pointer-events-none" style="width: <?= $percent ?>%"></div>
                                            <?php endif; ?>

                                            <span class="relative z-10"><?= Security::e($option['option_text']) ?></span>
                                            <span class="relative z-10 text-on-surface-variant font-bold"><?= $percent ?>% (<?= (int)$option['votes_count'] ?>)</span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <p class="text-[10px] text-on-surface-variant font-semibold mt-1">Total Votes: <span id="poll-total-<?= (int)$post['id'] ?>"><?= (int)$post['poll']['total_votes'] ?></span> • <?= $post['poll']['is_expired'] ? 'Closed' : 'Active' ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Embedded Quoted Post if exists -->
                        <?php if (!empty($post['quoted_post'])): ?>
                            <div class="p-4 rounded-2xl bg-background/50 border border-white/5 mb-4">
                                <div class="flex items-center space-x-2 mb-2">
                                    <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-primary text-[10px] font-bold overflow-hidden">
                                        <?php if (!empty($post['quoted_post']['avatar_url'])): ?>
                                            <img src="<?= Security::e($post['quoted_post']['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-xs font-bold text-on-background font-geist">@<?= Security::e($post['quoted_post']['username']) ?></span>
                                </div>
                                <p class="text-xs text-on-surface/85 line-clamp-3 leading-relaxed"><?= Security::e($post['quoted_post']['content']) ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Action Bar (Likes, replies, quotes, bookmarks) -->
                        <div class="flex items-center justify-between text-xs font-semibold text-on-surface-variant border-t border-white/5 pt-3.5 mt-2">
                            <button onclick="toggleLike(<?= (int)$post['id'] ?>, this)" class="flex items-center space-x-2 <?= $post['is_liked'] ? 'text-primary' : 'hover:text-primary' ?> transition-colors">
                                <span class="material-symbols-outlined text-lg" style="<?= $post['is_liked'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">favorite</span>
                                <span><?= (int)$post['likes_count'] ?></span>
                            </button>
                            <a href="/post/<?= (int)$post['id'] ?>" class="flex items-center space-x-2 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-lg">chat_bubble</span>
                                <span><?= (int)$post['replies_count'] ?></span>
                            </a>
                            <a href="/post/create?quote_id=<?= (int)$post['id'] ?>" class="flex items-center space-x-2 hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-lg">format_quote</span>
                                <span>Quote</span>
                            </a>
                            <button onclick="toggleBookmark(<?= (int)$post['id'] ?>, this)" class="flex items-center space-x-2 <?= $post['is_bookmarked'] ? 'text-primary' : 'hover:text-primary' ?> transition-colors">
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
            <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 space-y-4">
                <div class="flex items-center space-x-2 text-primary">
                    <span class="material-symbols-outlined">campaign</span>
                    <h3 class="font-bold font-geist text-sm uppercase tracking-wider">Announcements</h3>
                </div>
                <div class="space-y-3 divide-y divide-white/5">
                    <?php foreach ($announcements as $ann): ?>
                        <div class="pt-2 first:pt-0">
                            <h4 class="text-xs font-bold text-on-background mb-1"><?= Security::e($ann['title']) ?></h4>
                            <p class="text-[10px] text-on-surface-variant leading-relaxed"><?= Security::e($ann['content']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Trending Topics -->
        <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 space-y-4">
            <div class="flex items-center space-x-2 text-primary">
                <span class="material-symbols-outlined">trending_up</span>
                <h3 class="font-bold font-geist text-sm uppercase tracking-wider">Trending Topics</h3>
            </div>

            <?php if (empty($trendingHashtags)): ?>
                <p class="text-xs text-on-surface-variant">No active topics yet.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($trendingHashtags as $hash): ?>
                        <a href="/explore?q=%23<?= urlencode($hash['tag']) ?>" class="block group">
                            <h4 class="text-xs font-bold text-on-background group-hover:text-primary transition-colors">#<?= Security::e($hash['tag']) ?></h4>
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
                let html = '<h4 class="text-xs font-bold text-on-background uppercase tracking-wider mb-2">Poll results:</h4><div class="space-y-2">';
                data.options.forEach(opt => {
                    const percent = data.total > 0 ? Math.round((opt.votes / data.total) * 100) : 0;
                    const selectedBorder = (opt.id == optionId) ? 'border-primary bg-primary/5 text-primary' : 'border-white/10 text-on-background';
                    html += `<div class="w-full text-left p-3 rounded-xl border ${selectedBorder} text-xs font-semibold relative overflow-hidden">
                        <div class="absolute top-0 left-0 bottom-0 bg-primary/5 pointer-events-none" style="width: ${percent}%"></div>
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
