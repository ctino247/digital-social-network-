<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';
?>

<div class="space-y-6">
    <!-- Search Bar -->
    <form action="/explore" method="GET" class="relative">
        <span class="material-symbols-outlined absolute left-4 top-3.5 text-on-surface-variant">search</span>
        <input type="text" name="q" value="<?= Security::e($search) ?>" placeholder="Search posts, hashtags, digital products, creators..." class="w-full pl-12 pr-4 py-3.5 bg-surface-container-low border border-white/5 rounded-full text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm transition-all shadow-md"/>
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left/Main Column: Search Results / Recommended Catalog -->
        <div class="lg:col-span-2 space-y-6">
            <?php if (!empty($search)): ?>
                <h3 class="text-lg font-bold font-geist text-on-background">Search Results for "<?= Security::e($search) ?>"</h3>

                <!-- Posts Search Results -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Threads</h4>
                    <?php if (empty($posts)): ?>
                        <p class="text-xs text-on-surface-variant">No threads match your search query.</p>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <article class="bg-surface-container-low p-5 rounded-3xl border border-white/5 flex flex-col">
                                <div class="flex items-center space-x-3 mb-3">
                                    <a href="/profile/<?= Security::e($post['username']) ?>" class="w-10 h-10 rounded-full bg-primary-container/10 flex items-center justify-center text-primary font-bold overflow-hidden shrink-0">
                                        <?php if (!empty($post['avatar_url'])): ?>
                                            <img src="<?= Security::e($post['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                        <?php endif; ?>
                                    </a>
                                    <div class="flex-1">
                                        <a href="/profile/<?= Security::e($post['username']) ?>" class="text-sm font-bold font-geist text-on-background hover:text-primary">@<?= Security::e($post['username']) ?></a>
                                        <p class="text-[10px] text-on-surface-variant font-semibold uppercase"><?= date('M d, Y', strtotime($post['created_at'])) ?></p>
                                    </div>
                                </div>
                                <p class="text-sm text-on-surface mb-3"><?= Security::e($post['content']) ?></p>
                                <div class="flex space-x-6 text-xs text-on-surface-variant font-semibold">
                                    <a href="/post/<?= (int)$post['id'] ?>" class="flex items-center space-x-1 hover:text-primary">
                                        <span class="material-symbols-outlined text-base">chat_bubble</span>
                                        <span><?= (int)$post['replies_count'] ?> Replies</span>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Products Search Results -->
                <div class="space-y-4 pt-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2">Digital Products</h4>
                    <?php if (empty($products)): ?>
                        <p class="text-xs text-on-surface-variant">No products match your search query.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($products as $prod): ?>
                                <div class="bg-surface-container-low p-4 rounded-3xl border border-white/5 flex flex-col justify-between group">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="px-2 py-0.5 bg-primary/10 text-primary rounded text-[9px] font-bold uppercase"><?= Security::e($prod['type']) ?></span>
                                            <span class="text-sm font-bold font-geist text-primary">$<?= number_format($prod['price'], 2) ?></span>
                                        </div>
                                        <h4 class="font-bold text-sm text-on-background line-clamp-1 group-hover:text-primary transition-colors font-geist"><?= Security::e($prod['name']) ?></h4>
                                        <p class="text-[11px] text-on-surface-variant line-clamp-2 mt-1 leading-relaxed"><?= Security::e(strip_tags($prod['description'])) ?></p>
                                    </div>
                                    <a href="/product/<?= Security::e($prod['slug']) ?>" class="block text-center mt-4 py-2 bg-primary-container/10 text-primary text-xs font-bold rounded-full hover:bg-primary hover:text-on-primary transition-colors">
                                        View Details
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Default Explore Landing Page -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-base font-bold font-geist text-on-background mb-1">Featured Creators Showcase</h3>
                        <p class="text-xs text-on-surface-variant">Recommended high-value authors creating exceptional tools and resources.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($creators as $cr): ?>
                            <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 flex items-center space-x-4">
                                <a href="/profile/<?= Security::e($cr['username']) ?>" class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold overflow-hidden shrink-0">
                                    <?php if (!empty($cr['avatar_url'])): ?>
                                        <img src="<?= Security::e($cr['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                    <?php endif; ?>
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="/profile/<?= Security::e($cr['username']) ?>" class="font-bold text-sm truncate block font-geist text-on-background hover:text-primary">@<?= Security::e($cr['username']) ?></a>
                                    <p class="text-[10px] text-on-surface-variant mt-0.5 truncate"><?= !empty($cr['bio']) ? Security::e($cr['bio']) : 'Digital Creator' ?></p>
                                </div>
                                <a href="/profile/<?= Security::e($cr['username']) ?>" class="px-3.5 py-1.5 bg-primary/10 hover:bg-primary hover:text-on-primary text-[10px] font-bold text-primary rounded-full transition-all">
                                    View
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Recommended products -->
                    <div class="pt-4 space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-base font-bold font-geist text-on-background">Featured Digital Products</h3>
                            <a href="/marketplace" class="text-xs text-primary font-bold hover:underline">View All</a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($products as $prod): ?>
                                <div class="bg-surface-container-low p-4 rounded-3xl border border-white/5 flex flex-col justify-between group">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="px-2 py-0.5 bg-primary/10 text-primary rounded text-[9px] font-bold uppercase"><?= Security::e($prod['type']) ?></span>
                                            <span class="text-sm font-bold font-geist text-primary">$<?= number_format($prod['price'], 2) ?></span>
                                        </div>
                                        <h4 class="font-bold text-sm text-on-background line-clamp-1 group-hover:text-primary transition-colors font-geist"><?= Security::e($prod['name']) ?></h4>
                                        <p class="text-[11px] text-on-surface-variant line-clamp-2 mt-1 leading-relaxed"><?= Security::e(strip_tags($prod['description'])) ?></p>
                                    </div>
                                    <a href="/product/<?= Security::e($prod['slug']) ?>" class="block text-center mt-4 py-2 bg-primary-container/10 text-primary text-xs font-bold rounded-full hover:bg-primary hover:text-on-primary transition-colors">
                                        View Details
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Trends -->
        <div class="space-y-6">
            <div class="bg-surface-container-low p-5 rounded-3xl border border-white/5 space-y-4">
                <div class="flex items-center space-x-2 text-primary">
                    <span class="material-symbols-outlined">trending_up</span>
                    <h3 class="font-bold font-geist text-sm uppercase tracking-wider">Active Hashtags</h3>
                </div>

                <?php if (empty($trendingHashtags)): ?>
                    <p class="text-xs text-on-surface-variant">No active topics.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($trendingHashtags as $hash): ?>
                            <a href="/explore?q=%23<?= urlencode($hash['tag']) ?>" class="block group">
                                <h4 class="text-xs font-bold text-on-background group-hover:text-primary transition-colors">#<?= Security::e($hash['tag']) ?></h4>
                                <p class="text-[10px] text-on-surface-variant mt-0.5"><?= (int)$hash['count'] ?> threads active</p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
