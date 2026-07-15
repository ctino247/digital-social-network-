<?php
use App\Core\Security;
?>
<!-- Followers tab -->
<div id="content-followers-tab" class="tab-content space-y-4 hidden">
    <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-1.5">Followers list</h3>
    <?php if (empty($followersList)): ?>
        <p class="text-xs text-on-surface-variant text-center py-4 font-bold">No followers yet.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php foreach ($followersList as $fol): ?>
                <div class="p-3 bg-white rounded-2xl border border-[#E0E6E2] flex items-center space-x-3 shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-[#004D40]/10 flex items-center justify-center text-[#004D40] text-xs font-bold overflow-hidden shrink-0 border border-[#004D40]/10">
                        <?php if (!empty($fol['avatar_url'])): ?>
                            <img src="<?= Security::e($fol['avatar_url']) ?>" class="w-full h-full object-cover"/>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <a href="/profile/<?= Security::e($fol['username']) ?>" class="font-bold text-xs truncate text-[#004D40] block hover:underline">@<?= Security::e($fol['username']) ?></a>
                        <p class="text-[9px] text-on-surface-variant font-bold truncate"><?= Security::e($fol['full_name']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
