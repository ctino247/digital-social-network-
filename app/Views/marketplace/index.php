<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-8">

    <!-- Top Hero / Accent card (Redesigned matching original-dc9a931bf81d755b4d3a031e6d21f8fa.png banner look) -->
    <div class="bg-[#FBFBF9] p-8 rounded-4xl border border-[#E0E6E2] relative overflow-hidden shadow-sm">
        <div class="absolute right-0 bottom-0 w-64 h-64 bg-[#FFE500]/20 rounded-full blur-3xl -mr-20 -mb-20 pointer-events-none"></div>
        <h1 class="text-3xl font-extrabold text-[#004D40] mb-2 flex items-center space-x-2">
            <span class="w-2.5 h-7 bg-[#FFE500] rounded-full inline-block"></span>
            <span>Digital Marketplace</span>
        </h1>
        <p class="text-xs font-semibold text-on-surface-variant max-w-lg leading-relaxed">Discover e-books, premium templates, courses, source code, and developer tools uploaded by top creators, backed by our multi-level affiliate revenue-share system.</p>
    </div>

    <!-- Search & Filters Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Search -->
        <form action="/marketplace" method="GET" class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-4 top-3 text-[#004D40]">search</span>
            <input type="text" name="q" value="<?= Security::e($search) ?>" placeholder="Search catalog..." class="w-full pl-11 pr-4 py-2.5 bg-white border border-[#E0E6E2] rounded-full text-xs text-on-background focus:border-[#004D40] focus:ring-1 focus:ring-[#004D40] outline-none transition font-bold shadow-sm"/>
            <?php if ($selectedCategory): ?>
                <input type="hidden" name="category" value="<?= Security::e($selectedCategory['slug']) ?>"/>
            <?php endif; ?>
        </form>

        <!-- Category Horizontal Filter List -->
        <div class="flex items-center space-x-2 overflow-x-auto hide-scrollbar pb-1">
            <a href="/marketplace<?= !empty($search) ? '?q=' . urlencode($search) : '' ?>" class="px-4 py-2.5 rounded-full text-xs font-extrabold transition-all border shrink-0 <?= !$selectedCategory ? 'bg-[#004D40] text-white border-[#004D40]' : 'bg-white text-on-surface-variant border-[#E0E6E2] hover:border-[#004D40]/30' ?>">
                All Categories
            </a>
            <?php foreach ($categories as $cat): ?>
                <?php
                $isActive = ($selectedCategory && $selectedCategory['id'] === $cat['id']);
                $url = '/marketplace?category=' . $cat['slug'] . (!empty($search) ? '&q=' . urlencode($search) : '');
                ?>
                <a href="<?= $url ?>" class="px-4 py-2.5 rounded-full text-xs font-extrabold transition-all border shrink-0 <?= $isActive ? 'bg-[#004D40] text-white border-[#004D40]' : 'bg-white text-on-surface-variant border-[#E0E6E2] hover:border-[#004D40]/30' ?>">
                    <?= Security::e($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Grid: Products & Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Products Grid -->
        <div class="lg:col-span-2 space-y-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-[#004D40] border-b border-[#E0E6E2] pb-2">
                <?= $selectedCategory ? Security::e($selectedCategory['name']) : 'All Products' ?>
                <?= !empty($search) ? 'matching "' . Security::e($search) . '"' : '' ?>
            </h3>

            <?php if (empty($products)): ?>
                <div class="text-center py-16 bg-white rounded-3xl border border-[#E0E6E2] text-on-surface-variant shadow-sm">
                    <span class="material-symbols-outlined text-5xl mb-3 text-[#004D40]/30">shopping_cart</span>
                    <h4 class="text-base font-extrabold text-[#004D40]">No products found</h4>
                    <p class="text-xs mt-1 text-on-surface-variant/80">We couldn't find any products matching your selection.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php foreach ($products as $prod): ?>
                        <div class="bg-white rounded-3xl border border-[#E0E6E2] overflow-hidden flex flex-col justify-between hover:border-[#004D40]/30 hover:shadow-lg transition-all group shadow-sm">
                            <!-- Visual Accent Header Box (Replicating elegant card background from Mockup image) -->
                            <div class="h-40 bg-[#FAF7F3] flex items-center justify-center text-[#004D40] relative border-b border-[#E0E6E2]/40">
                                <span class="material-symbols-outlined text-5xl group-hover:scale-105 transition-all">deployed_code</span>
                                <span class="absolute top-4 right-4 px-3 py-1 bg-white rounded-full text-[9px] font-extrabold text-[#004D40] border border-[#E0E6E2] uppercase tracking-wider shadow-sm"><?= Security::e($prod['type']) ?></span>
                            </div>

                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div class="space-y-1.5 mb-4">
                                    <p class="text-[9px] font-extrabold text-[#004D40]/70 uppercase tracking-wider">By @<?= Security::e($prod['creator_username']) ?></p>
                                    <h4 class="font-extrabold text-sm text-[#004D40] line-clamp-1 group-hover:underline"><?= Security::e($prod['name']) ?></h4>
                                    <p class="text-xs text-on-surface-variant line-clamp-2 leading-relaxed font-medium"><?= Security::e(strip_tags($prod['description'])) ?></p>
                                </div>

                                <div class="flex justify-between items-center pt-4 border-t border-[#E0E6E2]/60">
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-bold uppercase text-on-surface-variant">Price</span>
                                        <span class="text-base font-extrabold text-[#004D40]">$<?= number_format($prod['price'], 2) ?></span>
                                    </div>
                                    <a href="/product/<?= Security::e($prod['slug']) ?>" class="px-5 py-2 bg-[#004D40] text-white hover:bg-[#FFE500] hover:text-[#004D40] text-xs font-bold rounded-full transition-all shadow-sm">Details</a>
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
            <div class="bg-white p-6 rounded-3xl border border-[#E0E6E2] space-y-4 shadow-sm">
                <div class="flex items-center space-x-2 text-[#004D40]">
                    <span class="material-symbols-outlined font-bold">trending_up</span>
                    <h3 class="font-extrabold text-sm uppercase tracking-wider">Trending Products</h3>
                </div>

                <?php if (empty($trendingProducts)): ?>
                    <p class="text-xs text-on-surface-variant font-semibold">No data available.</p>
                <?php else: ?>
                    <div class="space-y-4 divide-y divide-[#E0E6E2]">
                        <?php foreach ($trendingProducts as $tProd): ?>
                            <a href="/product/<?= Security::e($tProd['slug']) ?>" class="block pt-3 first:pt-0 group">
                                <h4 class="text-xs font-bold text-[#004D40] group-hover:underline line-clamp-1"><?= Security::e($tProd['name']) ?></h4>
                                <div class="flex justify-between items-center mt-1.5">
                                    <span class="text-[9px] font-extrabold text-[#004D40] px-2 py-0.5 bg-[#004D40]/10 rounded uppercase tracking-wider"><?= Security::e($tProd['type']) ?></span>
                                    <span class="text-xs font-extrabold text-[#004D40]">$<?= number_format($tProd['price'], 2) ?></span>
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
