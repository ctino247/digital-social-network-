<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6 max-w-xl mx-auto">
    <!-- Back to directory -->
    <a href="/messages" class="inline-flex items-center space-x-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span>Back to Messages</span>
    </a>

    <!-- Search container card -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4 shadow-xl">
        <h1 class="text-xl font-bold font-geist text-white">Start a New Conversation</h1>

        <form action="/messages/new" method="GET" class="relative">
            <span class="material-symbols-outlined absolute left-4 top-3 text-on-surface-variant">search</span>
            <input type="text" name="q" value="<?= Security::e($search) ?>" placeholder="Search users by name or username..." class="w-full pl-11 pr-4 py-2.5 bg-background border border-white/5 rounded-full text-xs text-on-background focus:border-primary outline-none transition-all"/>
        </form>
    </div>

    <!-- Search Results Directory list -->
    <div class="space-y-4">
        <?php if (!empty($search)): ?>
            <h3 class="text-xs font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Matching Users</h3>
            <?php if (empty($users)): ?>
                <p class="text-xs text-on-surface-variant text-center py-6">No users found matching your search parameters.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($users as $u): ?>
                        <div class="bg-surface-container-low p-4 rounded-3xl border border-white/5 flex items-center justify-between gap-4">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden shrink-0 text-sm">
                                    <?php if (!empty($u['avatar_url'])): ?>
                                        <img src="<?= Security::e($u['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                    <?php endif; ?>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-xs truncate text-white font-geist"><?= Security::e($u['full_name']) ?></h4>
                                    <p class="text-[10px] text-gray-400">@<?= Security::e($u['username']) ?></p>
                                </div>
                            </div>

                            <a href="/messages/<?= Security::e($u['username']) ?>" class="px-4 py-2 bg-primary text-on-primary font-bold rounded-full text-[10px] uppercase shadow-sm">
                                Chat Now
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-xs text-on-surface-variant text-center py-10 bg-surface-container-low rounded-3xl border border-white/5">Enter a search query in the form above to start a private messaging thread with creators and partners.</p>
        <?php endif; ?>
    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
