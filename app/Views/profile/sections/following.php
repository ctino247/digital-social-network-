<?php
use App\Core\Security;
?>
<!-- Following tab -->
<div id="content-following-tab" class="tab-content space-y-4 hidden">
    <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-1.5">Following list</h3>
    <?php if (empty($followingList)): ?>
        <p class="text-xs text-on-surface-variant text-center py-4 font-bold">Not following anyone yet.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php foreach ($followingList as $fng): ?>
                <div class="p-3 bg-white rounded-2xl border border-[#E0E6E2] flex items-center space-x-3 shadow-sm">
                    <div class="w-8 h-8 rounded-full bg-[#004D40]/10 flex items-center justify-center text-[#004D40] text-xs font-bold overflow-hidden shrink-0 border border-[#004D40]/10">
                        <?php if (!empty($fng['avatar_url'])): ?>
                            <img src="<?= Security::e($fng['avatar_url']) ?>" class="w-full h-full object-cover"/>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <a href="/profile/<?= Security::e($fng['username']) ?>" class="font-bold text-xs truncate text-[#004D40] block hover:underline">@<?= Security::e($fng['username']) ?></a>
                        <p class="text-[9px] text-on-surface-variant font-bold truncate"><?= Security::e($fng['full_name']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
