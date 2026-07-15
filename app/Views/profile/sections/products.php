<?php
use App\Core\Security;
$isCreator = in_array($profileUser['role'], ['creator', 'admin']);
?>
<?php if ($isCreator): ?>
    <div id="content-products" class="tab-content space-y-4 hidden">
        <?php if (empty($products)): ?>
            <div class="text-center py-12 bg-white rounded-3xl border border-[#E0E6E2] text-on-surface-variant shadow-sm">
                <span class="material-symbols-outlined text-4xl mb-2 text-[#004D40]/30">shopping_bag</span>
                <p class="text-xs font-bold text-[#004D40]">No digital products uploaded yet.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <?php foreach ($products as $prod): ?>
                    <div class="bg-white rounded-3xl border border-[#E0E6E2] overflow-hidden flex flex-col justify-between hover:border-[#004D40]/30 hover:shadow-lg transition-all group shadow-sm">
                        <div class="h-36 bg-[#FBFBF9] flex items-center justify-center text-[#004D40] relative">
                            <span class="material-symbols-outlined text-5xl">deployed_code</span>
                            <span class="absolute top-4 right-4 px-3 py-1 bg-white rounded-full text-[9px] font-extrabold text-[#004D40] border border-[#E0E6E2] uppercase tracking-wider shadow-sm"><?= Security::e($prod['type']) ?></span>
                        </div>
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-extrabold text-sm text-[#004D40] line-clamp-1 mb-1"><?= Security::e($prod['name']) ?></h4>
                                <p class="text-xs text-on-surface-variant line-clamp-2 leading-relaxed mb-4 font-semibold"><?= Security::e(strip_tags($prod['description'])) ?></p>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-[#E0E6E2]/40">
                                <span class="text-base font-extrabold text-[#004D40]">$<?= number_format($prod['price'], 2) ?></span>
                                <a href="/product/<?= Security::e($prod['slug']) ?>" class="px-4 py-2 bg-[#004D40] text-white hover:bg-[#FFE500] hover:text-[#004D40] text-xs font-bold rounded-full transition-all shadow-sm">View Detail</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
