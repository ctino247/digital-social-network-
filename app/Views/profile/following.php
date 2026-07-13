<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6">
    <!-- Back to profile -->
    <a href="/profile/<?= Security::e($profileUser['username']) ?>" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span>Back to @<?= Security::e($profileUser['username']) ?></span>
    </a>

    <!-- Header & Search -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4 shadow-xl">
        <h1 class="text-xl font-bold font-geist text-white">Who @<?= Security::e($profileUser['username']) ?> is Following</h1>

        <form action="/profile/<?= Security::e($profileUser['username']) ?>/following" method="GET" class="relative">
            <span class="material-symbols-outlined absolute left-4 top-3 text-on-surface-variant">search</span>
            <input type="text" name="q" value="<?= Security::e($search) ?>" placeholder="Search directory by name or username..." class="w-full pl-11 pr-4 py-2.5 bg-background border border-white/5 rounded-full text-xs text-on-background focus:border-primary outline-none transition-all"/>
        </form>
    </div>

    <!-- Following Directory List -->
    <div class="space-y-4">
        <?php if (empty($following)): ?>
            <div class="text-center py-12 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl mb-2 opacity-55">group_add</span>
                <p class="text-sm font-medium">Not following anyone yet.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($following as $f): ?>
                    <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 flex items-center justify-between gap-4 hover:border-primary/10 transition-all">
                        <div class="flex items-center space-x-3 min-w-0">
                            <!-- Avatar -->
                            <a href="/profile/<?= Security::e($f['username']) ?>" class="w-11 h-11 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden shrink-0 text-sm">
                                <?php if (!empty($f['avatar_url'])): ?>
                                    <img src="<?= Security::e($f['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                <?php else: ?>
                                    <?= strtoupper(substr($f['username'], 0, 1)) ?>
                                <?php endif; ?>
                            </a>
                            <div class="min-w-0">
                                <a href="/profile/<?= Security::e($f['username']) ?>" class="font-bold text-xs truncate text-white block hover:text-primary transition-colors font-geist"><?= Security::e($f['full_name']) ?></a>
                                <p class="text-[10px] text-gray-400 truncate">@<?= Security::e($f['username']) ?></p>
                                <p class="text-[10px] text-gray-400 truncate mt-1 max-w-[150px] font-normal"><?= Security::e($f['bio'] ?? 'Mimshack Member') ?></p>
                            </div>
                        </div>

                        <!-- Follow Action Button -->
                        <?php if ($currentUser && (int)$f['id'] !== (int)$currentUser['id']): ?>
                            <button onclick="toggleFollow(<?= (int)$f['id'] ?>, this)" class="px-4 py-2 rounded-full font-bold text-[10px] uppercase tracking-wider transition-all border shrink-0 <?= $f['is_following'] ? 'bg-surface-container-high text-white border-white/5 hover:bg-red-500/10 hover:text-red-400' : 'bg-primary text-on-primary border-primary' ?>">
                                <?= $f['is_following'] ? 'Unfollow' : 'Follow' ?>
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
                    btn.innerText = 'Unfollow';
                    btn.className = "px-4 py-2 rounded-full font-bold text-[10px] uppercase tracking-wider transition-all border shrink-0 bg-surface-container-high text-white border-white/5 hover:bg-red-500/10 hover:text-red-400";
                } else {
                    btn.innerText = 'Follow';
                    btn.className = "px-4 py-2 rounded-full font-bold text-[10px] uppercase tracking-wider transition-all border shrink-0 bg-primary text-on-primary border-primary";
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
