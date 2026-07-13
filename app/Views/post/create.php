<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="max-w-xl mx-auto">
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 shadow-xl">
        <h2 class="text-xl font-bold font-geist text-on-background mb-4">Create New Thread</h2>

        <form action="/post/create" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

            <?php if ($quotedPost): ?>
                <input type="hidden" name="quote_id" value="<?= (int)$quotedPost['id'] ?>"/>
                <!-- Quoted preview -->
                <div class="p-4 rounded-2xl bg-background border border-white/5 mb-4">
                    <p class="text-[10px] font-bold text-primary mb-1 uppercase tracking-wider">Quoting:</p>
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-xs font-bold text-on-background">@<?= Security::e($quotedPost['username']) ?></span>
                    </div>
                    <p class="text-xs text-on-surface-variant line-clamp-2"><?= Security::e($quotedPost['content']) ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($recommendProduct) && $recommendProduct): ?>
                <input type="hidden" name="product_id" value="<?= (int)$recommendProduct['id'] ?>"/>
                <input type="hidden" name="referral_code" value="<?= Security::e($refCode) ?>"/>
                <!-- Recommendation preview -->
                <div class="p-4 rounded-2xl bg-primary/5 border border-primary/20 mb-4 space-y-2">
                    <p class="text-[10px] font-bold text-primary uppercase tracking-wider">Recommending Product:</p>
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-white"><?= Security::e($recommendProduct['name']) ?></h4>
                            <p class="text-[10px] text-gray-400">By @<?= Security::e($recommendProduct['creator_username']) ?></p>
                        </div>
                        <span class="text-xs font-bold text-primary">$<?= number_format($recommendProduct['price'], 2) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Content Area -->
            <div>
                <label for="post-content" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-2">Your message (Max 500 characters)</label>
                <textarea id="post-content" name="content" rows="5" maxlength="500" required placeholder="What's happening? Add hashtags (#tech) and mention (@user) other creators..." class="w-full p-4 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm leading-relaxed resize-none"></textarea>
                <div class="flex justify-end mt-1.5">
                    <span id="char-counter" class="text-[10px] font-bold text-on-surface-variant">0 / 500</span>
                </div>
            </div>

            <!-- Optional Poll Area Accordion -->
            <div class="border border-white/5 rounded-2xl p-4 bg-background/50">
                <button type="button" onclick="togglePoll()" class="w-full flex justify-between items-center text-xs font-bold uppercase tracking-wider text-on-surface-variant focus:outline-none">
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-outlined text-base">ballot</span>
                        <span>Add Poll (Optional)</span>
                    </div>
                    <span id="poll-chevron" class="material-symbols-outlined text-sm">expand_more</span>
                </button>

                <div id="poll-inputs" class="space-y-4 mt-4 hidden">
                    <div>
                        <label for="poll_question" class="block text-xs font-semibold text-on-surface-variant mb-1">Question</label>
                        <input type="text" id="poll_question" name="poll_question" placeholder="Ask a question..." class="w-full px-4 py-2.5 bg-background border border-white/10 rounded-2xl text-on-background text-xs focus:border-primary outline-none"/>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-on-surface-variant mb-1">Options (Add at least 2)</label>
                        <input type="text" name="poll_options[]" placeholder="Option 1" class="w-full px-4 py-2 bg-background border border-white/10 rounded-xl text-on-background text-xs focus:border-primary outline-none"/>
                        <input type="text" name="poll_options[]" placeholder="Option 2" class="w-full px-4 py-2 bg-background border border-white/10 rounded-xl text-on-background text-xs focus:border-primary outline-none"/>
                        <input type="text" name="poll_options[]" placeholder="Option 3 (Optional)" class="w-full px-4 py-2 bg-background border border-white/10 rounded-xl text-on-background text-xs focus:border-primary outline-none"/>
                        <input type="text" name="poll_options[]" placeholder="Option 4 (Optional)" class="w-full px-4 py-2 bg-background border border-white/10 rounded-xl text-on-background text-xs focus:border-primary outline-none"/>
                    </div>
                </div>
            </div>

            <div class="flex space-x-3 pt-2">
                <button type="submit" class="flex-1 py-3.5 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition shadow-lg shadow-primary/20 flex justify-center items-center space-x-2">
                    <span class="material-symbols-outlined text-lg">send</span>
                    <span>Publish Thread</span>
                </button>
                <a href="/" class="px-6 py-3.5 bg-surface-container-high hover:bg-white/5 text-on-surface-variant hover:text-on-background text-sm font-bold rounded-full transition flex items-center justify-center">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    const textarea = document.getElementById('post-content');
    const counter = document.getElementById('char-counter');

    textarea.addEventListener('input', () => {
        const len = textarea.value.length;
        counter.innerText = len + ' / 500';
        if (len >= 480) {
            counter.classList.add('text-red-400');
            counter.classList.remove('text-on-surface-variant');
        } else {
            counter.classList.remove('text-red-400');
            counter.classList.add('text-on-surface-variant');
        }
    });

    function togglePoll() {
        const inputs = document.getElementById('poll-inputs');
        const chevron = document.getElementById('poll-chevron');
        if (inputs.classList.contains('hidden')) {
            inputs.classList.remove('hidden');
            chevron.innerText = 'expand_less';
        } else {
            inputs.classList.add('hidden');
            chevron.innerText = 'expand_more';
        }
    }
</script>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
