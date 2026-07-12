<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-8">
    <!-- Partner Bio Header card -->
    <div class="bg-primary/5 p-8 rounded-3xl border border-primary/20 relative overflow-hidden">
        <div class="absolute right-0 bottom-0 w-64 h-64 bg-primary/10 rounded-full blur-3xl -mr-20 -mb-20 pointer-events-none"></div>
        <h1 class="text-3xl font-bold font-geist text-on-background mb-2">Creator Dashboard</h1>
        <p class="text-sm text-on-surface-variant max-w-lg leading-relaxed">Welcome back to your partner panel! From here you can manage your listed digital assets, check royalty earnings, track clicks, and create custom coupons.</p>
    </div>

    <!-- Quick Stats rows -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider">Total Sales</p>
                <h2 class="text-3xl font-bold font-geist text-primary mt-1"><?= $salesCount ?> units</h2>
            </div>
            <span class="material-symbols-outlined text-4xl text-primary/45">local_mall</span>
        </div>
        <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider">Royalty Earnings</p>
                <h2 class="text-3xl font-bold font-geist text-primary mt-1">$<?= number_format($totalRoyalties, 2) ?></h2>
            </div>
            <span class="material-symbols-outlined text-4xl text-primary/45">monetization_on</span>
        </div>
        <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 flex items-center justify-between shadow-sm">
            <div>
                <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider">Followers</p>
                <h2 class="text-3xl font-bold font-geist text-on-background mt-1"><?= count($followers) ?> users</h2>
            </div>
            <span class="material-symbols-outlined text-4xl text-on-surface-variant/45">group</span>
        </div>
    </div>

    <!-- Control Buttons row -->
    <div class="flex flex-wrap gap-4">
        <a href="/creator/products/new" class="px-6 py-3 bg-primary text-on-primary font-bold rounded-full hover:opacity-95 text-xs shadow-md flex items-center space-x-1.5 shadow-primary/20">
            <span class="material-symbols-outlined text-sm">cloud_upload</span>
            <span>Upload New Product</span>
        </a>
        <a href="/creator/coupons" class="px-6 py-3 bg-surface-container-high text-on-background hover:bg-white/5 border border-white/5 font-bold rounded-full text-xs shadow-sm flex items-center space-x-1.5">
            <span class="material-symbols-outlined text-sm">percent</span>
            <span>Manage My Coupons</span>
        </a>
    </div>

    <!-- Main Products list / Followers table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left: Uploaded products catalog -->
        <div class="lg:col-span-2 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">My Digital Offerings</h3>

            <?php if (empty($products)): ?>
                <div class="text-center py-12 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl mb-2 opacity-55">article</span>
                    <p class="text-sm font-medium">You haven't uploaded any products yet.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($products as $prod): ?>
                        <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 flex justify-between items-center hover:border-white/10 transition-colors">
                            <div class="space-y-1 min-w-0">
                                <span class="px-2 py-0.5 bg-primary/10 text-primary border border-primary/20 rounded text-[8px] font-bold uppercase font-mono"><?= Security::e($prod['type']) ?></span>
                                <h4 class="font-bold text-sm text-on-background truncate font-geist"><?= Security::e($prod['name']) ?></h4>
                                <p class="text-[10px] text-on-surface-variant">Listed Price: <span class="text-primary font-bold font-geist">$<?= number_format($prod['price'], 2) ?></span> • Total Sales: <span class="text-on-background font-bold font-geist"><?= (int)$prod['sales_qty'] ?></span></p>
                            </div>

                            <div class="flex items-center space-x-3">
                                <a href="/creator/products/<?= (int)$prod['id'] ?>/edit" class="p-2 bg-surface-container-high hover:bg-primary hover:text-on-primary text-on-surface-variant hover:scale-105 rounded-full transition-all flex items-center justify-center border border-white/5">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </a>
                                <a href="/product/<?= Security::e($prod['slug']) ?>" class="px-4 py-2 bg-primary/10 hover:bg-primary text-primary hover:text-on-primary text-xs font-bold rounded-full transition-all">Preview</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right side: Followers Overview -->
        <div class="space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Recent Followers</h3>
            <?php if (empty($followers)): ?>
                <div class="p-5 bg-surface-container-low rounded-3xl border border-white/5 text-center">
                    <p class="text-xs text-on-surface-variant">No followers yet. Keep publishing amazing threads to build your network!</p>
                </div>
            <?php else: ?>
                <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 space-y-4">
                    <?php foreach ($followers as $fol): ?>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold overflow-hidden shrink-0">
                                <?php if (!empty($fol['avatar_url'])): ?>
                                    <img src="<?= Security::e($fol['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-xs truncate text-on-background font-geist">@<?= Security::e($fol['username']) ?></h4>
                                <p class="text-[10px] text-on-surface-variant truncate"><?= Security::e($fol['full_name']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
