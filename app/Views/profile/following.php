<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6">
    <!-- Back to profile -->
    <a href="/profile/<?= Security::e($profileUser['username']) ?>" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm font-bold">arrow_back</span>
        <span>Back to @<?= Security::e($profileUser['username']) ?></span>
    </a>

    <!-- Header & Search -->
    <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
        <h1 class="text-xl font-extrabold text-[#004D40]">Who @<?= Security::e($profileUser['username']) ?> is Following</h1>

        <form action="/profile/<?= Security::e($profileUser['username']) ?>/following" method="GET" class="relative">
            <span class="material-symbols-outlined absolute left-4 top-3 text-[#004D40] opacity-55">search</span>
            <input type="text" name="q" value="<?= Security::e($search) ?>" placeholder="Search directory by name or username..." class="w-full pl-11 pr-4 py-2.5 bg-[#FBFBF9] border border-[#E0E6E2] rounded-full text-xs text-on-background focus:border-[#004D40] outline-none transition-all font-bold"/>
        </form>
    </div>

    <!-- Following Directory List -->
    <div class="space-y-4">
        <?php if (empty($following)): ?>
            <div class="text-center py-12 bg-white rounded-3xl border border-[#E0E6E2] text-on-surface-variant shadow-sm">
                <span class="material-symbols-outlined text-4xl mb-2 text-[#004D40] opacity-35">group_add</span>
                <p class="text-xs font-bold text-[#004D40]">Not following anyone yet.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($following as $f): ?>
                    <div class="bg-white p-5 rounded-3xl border border-[#E0E6E2] flex items-center justify-between gap-4 hover:border-[#004D40]/20 hover:shadow-md transition-all">
                        <div class="flex items-center space-x-3 min-w-0">
                            <!-- Avatar -->
                            <a href="/profile/<?= Security::e($f['username']) ?>" class="w-11 h-11 rounded-full bg-[#004D40]/10 flex items-center justify-center text-[#004D40] font-bold overflow-hidden shrink-0 text-sm border border-[#004D40]/10 shadow-sm">
                                <?php if (!empty($f['avatar_url'])): ?>
                                    <img src="<?= Security::e($f['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                <?php else: ?>
                                    <?= strtoupper(substr($f['username'], 0, 1)) ?>
                                <?php endif; ?>
                            </a>
                            <div class="min-w-0">
                                <a href="/profile/<?= Security::e($f['username']) ?>" class="font-bold text-xs truncate text-[#004D40] block hover:underline"><?= Security::e($f['full_name']) ?></a>
                                <p class="text-[10px] text-on-surface-variant font-bold">@<?= Security::e($f['username']) ?></p>
                                <p class="text-[10px] text-on-surface-variant mt-1.5 max-w-[180px] font-semibold truncate leading-normal"><?= Security::e($f['bio'] ?? 'Mimshack Member') ?></p>
                            </div>
                        </div>

                        <!-- Follow Action Button -->
                        <?php if ($currentUser && (int)$f['id'] !== (int)$currentUser['id']): ?>
                            <button onclick="toggleFollow(<?= (int)$f['id'] ?>, this)" class="px-5 py-2 rounded-full font-bold text-[10px] uppercase tracking-wider transition-all shrink-0 <?= $f['is_following'] ? 'bg-[#F5F7F4] text-[#004D40] border border-[#E0E6E2] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200' : 'bg-[#004D40] text-white hover:bg-[#00332A]' ?>">
                                <?= $f['is_following'] ? 'Following' : 'Follow' ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    async function toggleFollow(userId, btn) {
        const token = '<?= $csrf_token ?>';
        try {
            const res = await fetch('/profile/' + userId + '/follow', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': token
                },
                body: 'csrf_token=' + encodeURIComponent(token)
            });
            const data = await res.json();
            if (data.success) {
                if (data.action === 'followed') {
                    btn.innerText = 'Following';
                    btn.className = "px-5 py-2 rounded-full font-bold text-[10px] uppercase tracking-wider transition-all shrink-0 bg-[#F5F7F4] text-[#004D40] border border-[#E0E6E2] hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200";
                } else {
                    btn.innerText = 'Follow';
                    btn.className = "px-5 py-2 rounded-full font-bold text-[10px] uppercase tracking-wider transition-all shrink-0 bg-[#004D40] text-white hover:bg-[#00332A]";
                }
            } else {
                alert(data.error || 'An error occurred.');
            }
        } catch (e) {
            console.error(e);
        }
    }
</script>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
