<?php
use App\Core\Security;
$isCreator = in_array($profileUser['role'], ['creator', 'admin']);
?>
<?php if ($isCreator): ?>
    <div id="content-about" class="tab-content space-y-4 hidden">
        <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-2">Creator Performance Summary</h3>

            <div class="grid grid-cols-2 gap-4 text-xs font-bold text-on-surface-variant">
                <div class="p-4 bg-[#F5F7F4] rounded-2xl border border-[#E0E6E2] shadow-inner">
                    <p>Average Star Score</p>
                    <p class="text-lg font-extrabold text-[#004D40] mt-1 flex items-center">
                        <span class="material-symbols-outlined text-sm mr-1 text-amber-500" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span><?= number_format($avgRating, 1) ?> / 5.0</span>
                    </p>
                </div>
                <div class="p-4 bg-[#F5F7F4] rounded-2xl border border-[#E0E6E2] shadow-inner">
                    <p>Total Reviews Received</p>
                    <p class="text-lg font-extrabold text-[#004D40] mt-1"><?= $totalReviews ?> reviews</p>
                </div>
            </div>

            <div class="pt-2 text-xs text-on-surface-variant leading-relaxed font-semibold">
                <p>Verified Creator biography of <strong class="text-[#004D40]">@<?= Security::e($profileUser['username']) ?></strong>. This partner is authorized to sell and publish digital packages, PDF booklets, course references, and custom template downloads inside the Mimshack Marketplace.</p>
            </div>
        </div>
    </div>
<?php endif; ?>
