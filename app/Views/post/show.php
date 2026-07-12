<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Back Button -->
    <a href="/" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span>Back to Home Feed</span>
    </a>

    <!-- Main Original Post Card -->
    <article class="bg-surface-container-low p-6 rounded-3xl border border-white/5 shadow-xl flex flex-col">
        <!-- Header User Details -->
        <div class="flex items-center space-x-3 mb-4">
            <a href="/profile/<?= Security::e($post['username']) ?>" class="w-11 h-11 rounded-full bg-primary-container/10 flex items-center justify-center text-primary font-bold overflow-hidden shrink-0">
                <?php if (!empty($post['avatar_url'])): ?>
                    <img src="<?= Security::e($post['avatar_url']) ?>" class="w-full h-full object-cover"/>
                <?php else: ?>
                    <?= strtoupper(substr($post['username'], 0, 1)) ?>
                <?php endif; ?>
            </a>
            <div class="flex-1 min-w-0">
                <a href="/profile/<?= Security::e($post['username']) ?>" class="font-bold text-base font-geist text-on-background hover:text-primary truncate block">@<?= Security::e($post['username']) ?></a>
                <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider"><?= date('M d, Y h:i A', strtotime($post['created_at'])) ?></p>
            </div>
        </div>

        <!-- Body text -->
        <div class="pl-1 mb-6">
            <p class="text-base text-on-background leading-relaxed whitespace-pre-line"><?= Security::e($post['content']) ?></p>
        </div>

        <!-- Optional Poll display -->
        <?php if (!empty($post['poll'])): ?>
            <div class="bg-background/40 p-5 rounded-2xl border border-white/5 mb-6 space-y-3" id="poll-box-<?= (int)$post['poll']['id'] ?>">
                <h4 class="text-xs font-bold text-on-background uppercase tracking-wider">Poll: <?= Security::e($post['poll']['question']) ?></h4>
                <div class="space-y-2">
                    <?php foreach ($post['poll']['options'] as $option): ?>
                        <?php
                        $votedClass = ($post['poll']['user_voted_option_id'] === (int)$option['id']) ? 'border-primary bg-primary/5 text-primary' : 'border-white/10 hover:border-primary/20 text-on-background';
                        $percent = $post['poll']['total_votes'] > 0 ? round(($option['votes_count'] / $post['poll']['total_votes']) * 100) : 0;
                        ?>
                        <button
                            onclick="votePoll(<?= (int)$post['poll']['id'] ?>, <?= (int)$option['id'] ?>, <?= (int)$post['id'] ?>)"
                            <?= $post['poll']['has_voted'] || $post['poll']['is_expired'] ? 'disabled' : '' ?>
                            class="w-full text-left p-3.5 rounded-xl border <?= $votedClass ?> transition-all flex justify-between items-center text-xs font-semibold relative overflow-hidden"
                        >
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
            <div class="p-4 rounded-2xl bg-background border border-white/5 mb-6">
                <div class="flex items-center space-x-2 mb-2">
                    <span class="text-xs font-bold text-on-background">@<?= Security::e($post['quoted_post']['username']) ?></span>
                </div>
                <p class="text-xs text-on-surface/85 leading-relaxed"><?= Security::e($post['quoted_post']['content']) ?></p>
            </div>
        <?php endif; ?>

        <!-- Action indicators -->
        <div class="flex items-center justify-between text-xs font-semibold text-on-surface-variant border-t border-white/5 pt-4">
            <button onclick="toggleLike(<?= (int)$post['id'] ?>, this)" class="flex items-center space-x-2 <?= $post['is_liked'] ? 'text-primary' : 'hover:text-primary' ?> transition-colors">
                <span class="material-symbols-outlined text-lg" style="<?= $post['is_liked'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">favorite</span>
                <span><?= (int)$post['likes_count'] ?> Likes</span>
            </button>
            <span class="flex items-center space-x-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-lg">chat_bubble</span>
                <span><?= count($replies) ?> Replies</span>
            </span>
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

    <!-- Reply Creation Box -->
    <?php if ($currentUser): ?>
        <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 shadow-xl">
            <h3 class="text-sm font-bold font-geist text-on-background mb-3">Post a Reply</h3>
            <form action="/post/<?= (int)$post['id'] ?>/reply" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

                <textarea name="content" rows="3" maxlength="500" required placeholder="Write your reply... (Max 500 characters)" class="w-full p-4 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm resize-none"></textarea>

                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition-all text-xs flex items-center space-x-1.5 shadow-md">
                        <span class="material-symbols-outlined text-sm">reply</span>
                        <span>Reply</span>
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="text-center p-6 bg-surface-container-low rounded-3xl border border-white/5">
            <p class="text-sm text-on-surface-variant"><a href="/auth/login" class="text-primary font-bold hover:underline">Sign In</a> to participate in this discussion thread.</p>
        </div>
    <?php endif; ?>

    <!-- Replies Feed list -->
    <div class="space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Discussion Replies</h3>
        <?php if (empty($replies)): ?>
            <p class="text-xs text-on-surface-variant text-center py-8">Be the first to reply to this thread!</p>
        <?php else: ?>
            <?php foreach ($replies as $rep): ?>
                <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 flex flex-col">
                    <div class="flex items-center space-x-3 mb-2.5">
                        <a href="/profile/<?= Security::e($rep['username']) ?>" class="w-8 h-8 rounded-full bg-primary-container/10 flex items-center justify-center text-primary font-bold overflow-hidden shrink-0 text-xs">
                            <?php if (!empty($rep['avatar_url'])): ?>
                                <img src="<?= Security::e($rep['avatar_url']) ?>" class="w-full h-full object-cover"/>
                            <?php endif; ?>
                        </a>
                        <div class="flex-1">
                            <a href="/profile/<?= Security::e($rep['username']) ?>" class="font-bold text-xs font-geist text-on-background hover:text-primary">@<?= Security::e($rep['username']) ?></a>
                            <span class="text-[9px] text-on-surface-variant font-semibold uppercase ml-2"><?= date('M d, Y h:i A', strtotime($rep['created_at'])) ?></span>
                        </div>
                    </div>
                    <p class="text-xs text-on-surface pl-1 leading-relaxed whitespace-pre-line"><?= Security::e($rep['content']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
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
                if (data.action === 'liked') {
                    btn.classList.add('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 1";
                    countSpan.innerText = data.likesCount + ' Likes';
                } else {
                    btn.classList.remove('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 0";
                    countSpan.innerText = data.likesCount + ' Likes';
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
