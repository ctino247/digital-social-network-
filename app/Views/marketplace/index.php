<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-8">

    <!-- Top Hero / Accent card -->
    <div class="bg-primary/5 p-8 rounded-3xl border border-primary/20 relative overflow-hidden">
        <div class="absolute right-0 bottom-0 w-64 h-64 bg-primary/10 rounded-full blur-3xl -mr-20 -mb-20 pointer-events-none"></div>
        <h1 class="text-3xl font-bold font-geist text-on-background mb-2">Digital Marketplace</h1>
        <p class="text-sm text-on-surface-variant max-w-lg leading-relaxed">Discover e-books, premium templates, custom courses, source code libraries, and tools uploaded by top creators, backed by a 5-level referral network.</p>
    </div>

    <!-- Search & Filters Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Search -->
        <form action="/marketplace" method="GET" class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-4 top-3 text-on-surface-variant">search</span>
            <input type="text" name="q" value="<?= Security::e($search) ?>" placeholder="Search catalog..." class="w-full pl-11 pr-4 py-2.5 bg-surface-container-low border border-white/5 rounded-full text-xs text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"/>
            <?php if ($selectedCategory): ?>
                <input type="hidden" name="category" value="<?= Security::e($selectedCategory['slug']) ?>"/>
            <?php endif; ?>
        </form>

        <!-- Category Horizontal Filter List -->
        <div class="flex items-center space-x-2 overflow-x-auto hide-scrollbar pb-1">
            <a href="/marketplace<?= !empty($search) ? '?q=' . urlencode($search) : '' ?>" class="px-4 py-2 rounded-full text-xs font-bold transition-all border <?= !$selectedCategory ? 'bg-primary text-on-primary border-primary' : 'bg-surface-container-low text-on-surface-variant border-white/5 hover:border-primary/20' ?>">
                All Categories
            </a>
            <?php foreach ($categories as $cat): ?>
                <?php
                $isActive = ($selectedCategory && $selectedCategory['id'] === $cat['id']);
                $url = '/marketplace?category=' . $cat['slug'] . (!empty($search) ? '&q=' . urlencode($search) : '');
                ?>
                <a href="<?= $url ?>" class="px-4 py-2 rounded-full text-xs font-bold transition-all border shrink-0 <?= $isActive ? 'bg-primary text-on-primary border-primary' : 'bg-surface-container-low text-on-surface-variant border-white/5 hover:border-primary/20' ?>">
                    <?= Security::e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Grid: Products & Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Products Grid -->
        <div class="lg:col-span-2 space-y-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">
                <?= $selectedCategory ? Security::e($selectedCategory['name']) : 'All Products' ?>
                <?= !empty($search) ? 'matching "' . Security::e($search) . '"' : '' ?>
            </h3>

            <?php if (empty($products)): ?>
                <div class="text-center py-16 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
                    <span class="material-symbols-outlined text-5xl mb-3 opacity-55">shopping_cart</span>
                    <h4 class="text-base font-bold font-geist text-on-background">No products found</h4>
                    <p class="text-xs mt-1">We couldn't find any products matching your selection.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php foreach ($products as $prod): ?>
                        <div class="bg-surface-container-low rounded-3xl border border-white/5 overflow-hidden flex flex-col justify-between hover:border-primary/20 transition-all group shadow-md">
                            <!-- Visual Accent Header Box -->
                            <div class="h-32 bg-gradient-to-tr from-primary/5 to-primary/15 flex items-center justify-center text-primary relative">
                                <span class="material-symbols-outlined text-5xl group-hover:scale-105 transition-transform">deployed_code</span>
                                <span class="absolute top-3 right-3 px-2.5 py-0.5 bg-surface/80 rounded-full text-[9px] font-bold text-primary border border-primary/15 uppercase"><?= Security::e($prod['type']) ?></span>
                            </div>

                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div class="space-y-1 mb-4">
                                    <p class="text-[10px] font-bold text-on-surface-variant uppercase">By @<?= Security::e($prod['creator_username']) ?></p>
                                    <h4 class="font-bold text-base text-on-background line-clamp-1 group-hover:text-primary transition-colors font-geist"><?= Security::e($prod['name']) ?></h4>
                                    <p class="text-xs text-on-surface-variant line-clamp-2 leading-relaxed"><?= Security::e(strip_tags($prod['description'])) ?></p>
                                </div>

                                <div class="flex justify-between items-center pt-3 border-t border-white/5">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold uppercase text-on-surface-variant">Price</span>
                                        <span class="text-base font-bold text-primary font-geist">$<?= number_format($prod['price'], 2) ?></span>
                                    </div>
                                    <a href="/product/<?= Security::e($prod['slug']) ?>" class="px-5 py-2 bg-primary/10 hover:bg-primary text-primary hover:text-on-primary text-xs font-bold rounded-full transition-all">Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <!-- Trending Products List -->
            <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 space-y-4">
                <div class="flex items-center space-x-2 text-primary">
                    <span class="material-symbols-outlined">trending_up</span>
                    <h3 class="font-bold font-geist text-sm uppercase tracking-wider">Trending Products</h3>
                </div>

                <?php if (empty($trendingProducts)): ?>
                    <p class="text-xs text-on-surface-variant">No data available.</p>
                <?php else: ?>
                    <div class="space-y-4 divide-y divide-white/5">
                        <?php foreach ($trendingProducts as $tProd): ?>
                            <a href="/product/<?= Security::e($tProd['slug']) ?>" class="block pt-3 first:pt-0 group">
                                <h4 class="text-xs font-bold text-on-background group-hover:text-primary transition-colors line-clamp-1 font-geist"><?= Security::e($tProd['name']) ?></h4>
                                <div class="flex justify-between items-center mt-1">
                                    <span class="text-[9px] font-bold text-primary px-1.5 py-0.5 bg-primary/5 rounded"><?= Security::e($tProd['type']) ?></span>
                                    <span class="text-xs font-bold text-on-background font-geist">$<?= number_format($tProd['price'], 2) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
