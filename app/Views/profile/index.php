<?php
use App\Core\Security;
$session = new \App\Core\Session();
$currentUser = $session->get('user');
include ROOT_PATH . '/app/Views/partials/header.php';

$isCreator = in_array($profileUser['role'], ['creator', 'admin']);
$isSalesPartner = (int)$profileUser['is_sales_partner'] === 1;
?>

<div class="space-y-8">
    <!-- Cover Photo Mock Banner -->
    <div class="h-44 bg-gradient-to-r from-primary/20 via-primary/5 to-background rounded-3xl border border-white/5 relative overflow-hidden flex items-end p-6">
        <div class="absolute right-0 top-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
        <span class="px-3 py-1 bg-surface-container-low/80 backdrop-blur border border-white/5 rounded-full text-[9px] font-bold text-primary uppercase tracking-wider">Premium Partner space</span>
    </div>

    <!-- Profile Card details -->
    <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 relative overflow-hidden -mt-16">
        <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-6">
            <!-- Profile Photo/Avatar -->
            <div class="w-24 h-24 rounded-full bg-primary-container/20 flex items-center justify-center text-primary text-4xl font-bold border-4 border-background shrink-0 overflow-hidden relative shadow-lg">
                <?php if (!empty($profileUser['avatar_url'])): ?>
                    <img src="<?= Security::e($profileUser['avatar_url']) ?>" class="w-full h-full object-cover"/>
                <?php else: ?>
                    <?= strtoupper(substr($profileUser['username'], 0, 1)) ?>
                <?php endif; ?>
            </div>

            <!-- Profile Info -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 flex-wrap">
                    <h1 class="text-2xl font-bold font-geist text-white truncate"><?= Security::e($profileUser['full_name']) ?></h1>
                    <?php if ($profileUser['role'] === 'admin'): ?>
                        <span class="px-2.5 py-0.5 bg-red-500/10 text-red-400 border border-red-500/10 text-[10px] font-bold uppercase rounded-full">Admin</span>
                    <?php elseif ($profileUser['role'] === 'creator'): ?>
                        <span class="px-2.5 py-0.5 bg-primary/10 text-primary border border-primary/10 text-[10px] font-bold uppercase rounded-full">Creator Partner</span>
                    <?php endif; ?>
                    <?php if ($isSalesPartner): ?>
                        <span class="px-2.5 py-0.5 bg-blue-500/10 text-blue-400 border border-blue-500/10 text-[10px] font-bold uppercase rounded-full">Sales Partner</span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-on-surface-variant font-medium mt-0.5">@<?= Security::e($profileUser['username']) ?></p>
                <p class="text-sm text-[#E4E2E4] mt-3 whitespace-pre-line leading-relaxed"><?= !empty($profileUser['bio']) ? Security::e($profileUser['bio']) : 'This user hasn\'t written a bio yet.' ?></p>

                <!-- Clickable Follow stats -->
                <div class="flex space-x-6 mt-4 text-xs font-semibold text-on-surface-variant">
                    <a href="/profile/<?= Security::e($profileUser['username']) ?>/followers" class="hover:text-primary transition-colors">
                        <span class="text-white font-bold text-sm" id="followers-count"><?= $followersCount ?></span> Followers
                    </a>
                    <a href="/profile/<?= Security::e($profileUser['username']) ?>/following" class="hover:text-primary transition-colors">
                        <span class="text-white font-bold text-sm"><?= $followingCount ?></span> Following
                    </a>
                </div>
            </div>

            <!-- Follow / Action Button -->
            <div class="shrink-0 flex space-x-2 pt-2 md:pt-0">
                <?php if ($currentUser && $currentUser['id'] !== $profileUser['id']): ?>
                    <button onclick="toggleFollow(<?= (int)$profileUser['id'] ?>)" id="follow-btn" class="px-6 py-2.5 rounded-full font-bold text-sm transition-all shadow-md <?= $isFollowing ? 'bg-surface-container-high text-[#E4E2E4] border border-white/5 hover:bg-red-500/10 hover:text-red-400' : 'bg-primary text-on-primary hover:opacity-90' ?>">
                        <?= $isFollowing ? 'Following' : 'Follow' ?>
                    </button>
                    <a href="/messages/<?= Security::e($profileUser['username']) ?>" class="p-2.5 rounded-full bg-surface-container-high text-on-surface-variant hover:text-primary transition-colors border border-white/5 flex items-center justify-center">
                        <span class="material-symbols-outlined">mail</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tab Selection -->
    <div class="border-b border-white/5 flex space-x-2 overflow-x-auto hide-scrollbar">
        <button onclick="switchTab('posts')" id="tab-posts" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-primary text-primary whitespace-nowrap">Posts</button>
        <?php if ($isCreator): ?>
            <button onclick="switchTab('products')" id="tab-products" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary whitespace-nowrap">Products</button>
        <?php endif; ?>
        <?php if ($isSalesPartner || $isCreator): ?>
            <button onclick="switchTab('recommendations')" id="tab-recommendations" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary whitespace-nowrap">Recommendations</button>
        <?php endif; ?>
        <?php if ($isCreator): ?>
            <button onclick="switchTab('about')" id="tab-about" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary whitespace-nowrap">About Creator</button>
        <?php endif; ?>
        <button onclick="switchTab('followers-tab')" id="tab-followers-tab" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary whitespace-nowrap">Followers</button>
        <button onclick="switchTab('following-tab')" id="tab-following-tab" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary whitespace-nowrap">Following</button>

        <?php if ($isOwnProfile): ?>
            <button onclick="switchTab('wallet')" id="tab-wallet" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary whitespace-nowrap">Wallet & Earnings</button>
            <?php if ($profileUser['role'] === 'member'): ?>
                <button onclick="switchTab('apply-creator')" id="tab-apply-creator" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary whitespace-nowrap">Apply Creator</button>
            <?php endif; ?>
            <button onclick="switchTab('settings')" id="tab-settings" class="px-5 py-3 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-on-surface-variant hover:text-primary whitespace-nowrap">Settings</button>
        <?php endif; ?>
    </div>

    <!-- Tab Contents -->

    <!-- Posts Tab -->
    <div id="content-posts" class="tab-content space-y-4">
        <?php if (empty($posts)): ?>
            <div class="text-center py-12 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl mb-2 opacity-55">article</span>
                <p class="text-sm">No posts yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="bg-surface-container-low p-5 rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-primary-container/10 flex items-center justify-center text-primary font-bold overflow-hidden">
                            <?php if (!empty($post['avatar_url'])): ?>
                                <img src="<?= Security::e($post['avatar_url']) ?>" class="w-full h-full object-cover"/>
                            <?php else: ?>
                                <?= strtoupper(substr($post['username'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold font-geist">@<?= Security::e($post['username']) ?></h4>
                            <p class="text-[10px] text-on-surface-variant font-semibold uppercase"><?= date('M d, Y h:i A', strtotime($post['created_at'])) ?></p>
                        </div>
                    </div>
                    <p class="text-sm text-[#E4E2E4] leading-relaxed mb-4 whitespace-pre-line"><?= Security::e($post['content']) ?></p>

                    <!-- Recommendation Product Card if exists -->
                    <?php if (!empty($post['product'])): ?>
                        <div class="p-5 rounded-3xl bg-surface-container-high border border-primary/20 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 group">
                            <div class="flex items-center space-x-4">
                                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0 relative">
                                    <span class="material-symbols-outlined text-3xl">deployed_code</span>
                                    <span class="absolute -top-2 -right-2 px-1.5 py-0.5 bg-primary text-on-primary text-[8px] font-bold rounded-full uppercase"><?= Security::e($post['product']['type']) ?></span>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-sm text-white group-hover:text-primary transition-colors font-geist truncate"><?= Security::e($post['product']['name']) ?></h4>
                                    <p class="text-xs text-gray-400 mt-0.5">By <span class="font-semibold text-gray-300">@<?= Security::e($post['product']['creator_username']) ?></span></p>
                                    <div class="flex items-center space-x-2 mt-1.5 text-[10px] text-gray-400 font-semibold uppercase">
                                        <span class="flex items-center text-yellow-400">
                                            <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">star</span>
                                            <span class="ml-1 text-[#E4E2E4] font-geist"><?= $post['product']['avg_rating'] ? number_format($post['product']['avg_rating'], 1) : '5.0' ?></span>
                                        </span>
                                        <span>•</span>
                                        <span><?= (int)$post['product']['sales_count'] ?> sales</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2 shrink-0">
                                <a href="/product/<?= Security::e($post['product']['slug']) ?><?= $post['referral_code'] ? '?ref=' . Security::e($post['referral_code']) : '' ?>" class="px-5 py-2.5 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 text-xs shadow-md shadow-primary/20">
                                    Buy Now ($<?= number_format($post['product']['price'], 2) ?>)
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Action Bar -->
                    <div class="flex items-center space-x-6 text-xs font-semibold text-on-surface-variant">
                        <button onclick="toggleLike(<?= (int)$post['id'] ?>, this)" class="flex items-center space-x-1.5 <?= $post['is_liked'] ? 'text-primary' : 'hover:text-primary' ?> transition-colors">
                            <span class="material-symbols-outlined text-lg" style="<?= $post['is_liked'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">favorite</span>
                            <span><?= (int)$post['likes_count'] ?></span>
                        </button>
                        <a href="/post/<?= (int)$post['id'] ?>" class="flex items-center space-x-1.5 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-lg">chat_bubble</span>
                            <span><?= (int)$post['replies_count'] ?></span>
                        </a>
                        <button onclick="toggleBookmark(<?= (int)$post['id'] ?>, this)" class="flex items-center space-x-1.5 <?= $post['is_bookmarked'] ? 'text-primary' : 'hover:text-primary' ?> transition-colors">
                            <span class="material-symbols-outlined text-lg" style="<?= $post['is_bookmarked'] ? "font-variation-settings: 'FILL' 1;" : "" ?>">bookmark</span>
                            <span>Bookmark</span>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Products Tab -->
    <?php if ($isCreator): ?>
        <div id="content-products" class="tab-content space-y-4 hidden">
            <?php if (empty($products)): ?>
                <div class="text-center py-12 bg-surface-container-low rounded-3xl border border-white/5 text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl mb-2 opacity-55">shopping_bag</span>
                    <p class="text-sm">No digital products uploaded yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php foreach ($products as $prod): ?>
                        <div class="bg-surface-container-low rounded-3xl border border-white/5 overflow-hidden flex flex-col justify-between hover:border-primary/20 transition-all group">
                            <div class="h-32 bg-gradient-to-tr from-primary/10 to-primary/20 flex items-center justify-center text-primary relative">
                                <span class="material-symbols-outlined text-5xl">deployed_code</span>
                                <span class="absolute top-3 right-3 px-2.5 py-0.5 bg-surface/80 rounded-full text-[10px] font-bold text-primary border border-primary/20"><?= Security::e($prod['type']) ?></span>
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-base text-white line-clamp-1 mb-1 font-geist"><?= Security::e($prod['name']) ?></h4>
                                    <p class="text-xs text-on-surface-variant line-clamp-2 leading-relaxed mb-4"><?= Security::e(strip_tags($prod['description'])) ?></p>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-white/5">
                                    <span class="text-lg font-bold text-primary font-geist">$<?= number_format($prod['price'], 2) ?></span>
                                    <a href="/product/<?= Security::e($prod['slug']) ?>" class="px-4 py-2 bg-primary/10 hover:bg-primary text-primary hover:text-on-primary text-xs font-bold rounded-full transition-all">View Detail</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Recommendations Tab -->
    <?php if ($isSalesPartner || $isCreator): ?>
        <div id="content-recommendations" class="tab-content space-y-6 hidden">
            <!-- Recommended Products Section -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-1.5 font-geist">Recommended Offerings</h3>
                <?php if (empty($recommendedProducts)): ?>
                    <p class="text-xs text-gray-500">No recommended products listed.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($recommendedProducts as $rp): ?>
                            <div class="p-4 bg-surface-container-low rounded-2xl border border-white/5 flex justify-between items-center group">
                                <div class="min-w-0 flex items-center space-x-3">
                                    <span class="material-symbols-outlined text-primary text-2xl shrink-0">deployed_code</span>
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-white group-hover:text-primary transition-colors truncate font-geist"><?= Security::e($rp['name']) ?></h4>
                                        <p class="text-[9px] text-gray-400">By @<?= Security::e($rp['creator_username']) ?> • <span class="uppercase text-primary font-bold"><?= Security::e($rp['category_name']) ?></span></p>
                                    </div>
                                </div>
                                <a href="/product/<?= Security::e($rp['slug']) ?>" class="px-3.5 py-1.5 bg-primary/10 hover:bg-primary text-primary hover:text-on-primary text-[10px] font-bold rounded-full transition-all">Details</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recommendation social posts feed -->
            <div class="space-y-4 pt-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-1.5 font-geist">Recommendation Feed</h3>
                <?php if (empty($recommendationFeed)): ?>
                    <p class="text-xs text-gray-500">No recommendation thread posts yet.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recommendationFeed as $post): ?>
                            <article class="bg-surface-container-low p-5 rounded-3xl border border-white/5 hover:border-white/10 transition-colors">
                                <div class="flex items-center space-x-3 mb-3">
                                    <div class="w-10 h-10 rounded-full bg-primary-container/10 flex items-center justify-center text-primary font-bold overflow-hidden">
                                        <?php if (!empty($post['avatar_url'])): ?>
                                            <img src="<?= Security::e($post['avatar_url']) ?>" class="w-full h-full object-cover"/>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold font-geist">@<?= Security::e($post['username']) ?></h4>
                                        <p class="text-[9px] text-on-surface-variant font-semibold uppercase"><?= date('M d, Y', strtotime($post['created_at'])) ?></p>
                                    </div>
                                </div>
                                <p class="text-xs text-[#E4E2E4] mb-3 leading-relaxed"><?= Security::e($post['content']) ?></p>

                                <!-- Recommendation Card embedded -->
                                <div class="p-4 rounded-2xl bg-surface-container-high border border-primary/20 mb-4 flex items-center justify-between gap-4">
                                    <div class="min-w-0 flex items-center space-x-3">
                                        <span class="material-symbols-outlined text-primary text-xl shrink-0">deployed_code</span>
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-bold text-white truncate font-geist"><?= Security::e($post['product']['name']) ?></h4>
                                            <p class="text-[9px] text-gray-400">By @<?= Security::e($post['product']['creator_username']) ?></p>
                                        </div>
                                    </div>
                                    <a href="/product/<?= Security::e($post['product']['slug']) ?><?= $post['referral_code'] ? '?ref=' . Security::e($post['referral_code']) : '' ?>" class="px-4 py-2 bg-primary text-on-primary font-bold rounded-full text-[10px] uppercase shadow-sm shrink-0">Buy ($<?= number_format($post['product']['price'], 2) ?>)</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- About Creator Tab -->
    <?php if ($isCreator): ?>
        <div id="content-about" class="tab-content space-y-4 hidden">
            <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-2 font-geist">Creator Performance Summary</h3>

                <div class="grid grid-cols-2 gap-4 text-xs font-semibold text-gray-400">
                    <div class="p-4 bg-background/50 rounded-2xl border border-white/5">
                        <p>Average Star Score</p>
                        <p class="text-lg font-bold text-yellow-400 mt-1 font-geist flex items-center">
                            <span class="material-symbols-outlined text-sm mr-1" style="font-variation-settings: 'FILL' 1;">star</span>
                            <span><?= number_format($avgRating, 1) ?> / 5.0</span>
                        </p>
                    </div>
                    <div class="p-4 bg-background/50 rounded-2xl border border-white/5">
                        <p>Total Reviews Received</p>
                        <p class="text-lg font-bold text-white mt-1 font-geist"><?= $totalReviews ?> reviews</p>
                    </div>
                </div>

                <div class="pt-2 text-xs text-on-surface-variant leading-relaxed font-medium">
                    <p>Verified Creator biography of <strong class="text-white">@<?= Security::e($profileUser['username']) ?></strong>. This partner is authorized to sell and publish digital packages, PDF booklets, course references, and custom template downloads inside the Mimshack Marketplace.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Followers tab -->
    <div id="content-followers-tab" class="tab-content space-y-4 hidden">
        <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-1.5 font-geist">Followers list Preview</h3>
        <?php if (empty($followersList)): ?>
            <p class="text-xs text-gray-500 text-center py-4">No followers yet.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($followersList as $fol): ?>
                    <div class="p-3 bg-surface-container-low rounded-2xl border border-white/5 flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold overflow-hidden shrink-0">
                            <?php if (!empty($fol['avatar_url'])): ?>
                                <img src="<?= Security::e($fol['avatar_url']) ?>" class="w-full h-full object-cover"/>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0">
                            <a href="/profile/<?= Security::e($fol['username']) ?>" class="font-bold text-xs truncate text-white block hover:text-primary transition-colors font-geist">@<?= Security::e($fol['username']) ?></a>
                            <p class="text-[9px] text-gray-400 truncate"><?= Security::e($fol['full_name']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Following tab -->
    <div id="content-following-tab" class="tab-content space-y-4 hidden">
        <h3 class="text-sm font-bold uppercase tracking-wider text-primary border-b border-white/5 pb-1.5 font-geist">Following list Preview</h3>
        <?php if (empty($followingList)): ?>
            <p class="text-xs text-gray-500 text-center py-4">Not following anyone yet.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($followingList as $fng): ?>
                    <div class="p-3 bg-surface-container-low rounded-2xl border border-white/5 flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xs font-bold overflow-hidden shrink-0">
                            <?php if (!empty($fng['avatar_url'])): ?>
                                <img src="<?= Security::e($fng['avatar_url']) ?>" class="w-full h-full object-cover"/>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0">
                            <a href="/profile/<?= Security::e($fng['username']) ?>" class="font-bold text-xs truncate text-white block hover:text-primary transition-colors font-geist">@<?= Security::e($fng['username']) ?></a>
                            <p class="text-[9px] text-gray-400 truncate"><?= Security::e($fng['full_name']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Wallet & Earnings Tab -->
    <?php if ($isOwnProfile): ?>
        <div id="content-wallet" class="tab-content space-y-6 hidden">
            <!-- Wallet Dashboard Info cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-primary/5 p-6 rounded-3xl border border-primary/20 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-primary font-bold uppercase tracking-wider">Available Balance</p>
                        <h2 class="text-3xl font-bold font-geist text-primary mt-1">$<?= number_format($wallet['balance'] ?? 0.00, 2) ?></h2>
                    </div>
                    <span class="material-symbols-outlined text-4xl text-primary/55">account_balance_wallet</span>
                </div>
                <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-on-surface-variant font-bold uppercase tracking-wider">Pending Royalties/Commission</p>
                        <h2 class="text-3xl font-bold font-geist text-on-background mt-1">$<?= number_format($wallet['pending_balance'] ?? 0.00, 2) ?></h2>
                    </div>
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant/55">hourglass_empty</span>
                </div>
            </div>

            <!-- Link to new dedicated wallet page -->
            <div class="p-5 bg-background/50 rounded-2xl border border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <h4 class="text-xs font-bold text-white font-geist">Access Advanced Wallet Dashboard</h4>
                    <p class="text-[10px] text-gray-400 font-medium leading-relaxed">View granular earning statistics, track Flutterwave withdrawals, and review historic transactions ledgers.</p>
                </div>
                <a href="/wallet" class="px-5 py-2.5 bg-primary text-on-primary font-bold rounded-full text-xs shadow-sm hover:opacity-90 transition-opacity">Open Wallet Dashboard</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Apply Creator Tab -->
    <?php if ($isOwnProfile && $profileUser['role'] === 'member'): ?>
        <div id="content-apply-creator" class="tab-content space-y-6 hidden">
            <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5">
                <h3 class="text-lg font-bold font-geist text-on-background mb-2">Join the Creator Program</h3>
                <p class="text-sm text-on-surface-variant leading-relaxed mb-6">Unleash your digital commercial potential. Sell PDF books, ZIP software, custom templates, courses, source code, and video downloads directly to our vibrant, affiliate-powered community!</p>

                <?php if ($creatorApplication): ?>
                    <div class="p-4 rounded-2xl border mb-6 <?= $creatorApplication['status'] === 'pending' ? 'bg-yellow-500/10 border-yellow-500/20 text-yellow-400' : ($creatorApplication['status'] === 'approved' ? 'bg-primary/10 border-primary/20 text-primary' : 'bg-red-500/10 border-red-500/20 text-red-400') ?>">
                        <div class="flex items-center space-x-2 font-bold text-sm mb-1">
                            <span class="material-symbols-outlined">info</span>
                            <span>Application Status: <?= ucfirst($creatorApplication['status']) ?></span>
                        </div>
                        <p class="text-xs opacity-80 pl-7">Submitted on <?= date('M d, Y', strtotime($creatorApplication['created_at'])) ?></p>
                        <?php if (!empty($creatorApplication['admin_note'])): ?>
                            <p class="text-xs font-semibold pl-7 mt-2">Admin Review Note: "<?= Security::e($creatorApplication['admin_note']) ?>"</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form action="/profile/apply-creator" method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

                    <div>
                        <label for="creator_bio" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">What type of products will you upload?</label>
                        <textarea id="creator_bio" name="creator_bio" rows="4" placeholder="Briefly describe your digital products (e.g. templates, software, e-books)..." required class="w-full px-4 py-3 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm leading-relaxed"></textarea>
                    </div>

                    <div>
                        <label for="portfolio_url" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Portfolio or Website URL (Optional)</label>
                        <input type="url" id="portfolio_url" name="portfolio_url" placeholder="https://example.com" class="w-full px-4 py-3 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm"/>
                    </div>

                    <button type="submit" class="px-6 py-3.5 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition-all text-sm shadow-md">
                        Submit Creator Application
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Settings Tab -->
    <?php if ($isOwnProfile): ?>
        <div id="content-settings" class="tab-content space-y-6 hidden">
            <div class="bg-surface-container-low p-6 rounded-3xl border border-white/5">
                <h3 class="text-lg font-bold font-geist text-on-background mb-4">Edit Profile Settings</h3>
                <form action="/profile/update" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"/>

                    <div>
                        <label for="edit_full_name" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Full Name</label>
                        <input type="text" id="edit_full_name" name="full_name" value="<?= Security::e($profileUser['full_name']) ?>" required class="w-full px-4 py-3 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm"/>
                    </div>

                    <div>
                        <label for="edit_bio" class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Bio</label>
                        <textarea id="edit_bio" name="bio" rows="4" class="w-full px-4 py-3 bg-background border border-white/10 rounded-2xl text-on-background focus:border-primary focus:ring-1 focus:ring-primary outline-none text-sm leading-relaxed"><?= Security::e($profileUser['bio']) ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-on-surface-variant mb-1.5">Profile Picture (Avatar)</label>
                        <input type="file" name="avatar" accept="image/*" class="block w-full text-xs text-on-surface-variant file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary-container/10 file:text-primary hover:file:bg-primary-container/20 file:cursor-pointer"/>
                        <p class="text-[10px] text-on-surface-variant mt-1.5">PNG, JPG, JPEG, WEBP are accepted.</p>
                    </div>

                    <button type="submit" class="px-6 py-3 bg-primary text-on-primary font-bold rounded-full hover:opacity-90 transition-all text-sm shadow-md">
                        Save Profile Changes
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Ajax follow, likes, bookmarks handling script -->
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('content-' + tabId).classList.remove('hidden');

        // Reset tab styling
        document.querySelectorAll("[id^='tab-']").forEach(btn => {
            btn.classList.remove('border-primary', 'text-primary');
            btn.classList.add('border-transparent', 'text-on-surface-variant');
        });

        // Add active styling
        const activeBtn = document.getElementById('tab-' + tabId);
        activeBtn.classList.add('border-primary', 'text-primary');
        activeBtn.classList.remove('border-transparent', 'text-on-surface-variant');
    }

    async function toggleFollow(userId) {
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
                const btn = document.getElementById('follow-btn');
                if (data.action === 'followed') {
                    if (btn) {
                        btn.innerText = 'Following';
                        btn.className = "px-6 py-2.5 rounded-full font-bold text-sm transition-all shadow-md bg-surface-container-high text-[#E4E2E4] border border-white/5 hover:bg-red-500/10 hover:text-red-400";
                    }
                } else {
                    if (btn) {
                        btn.innerText = 'Follow';
                        btn.className = "px-6 py-2.5 rounded-full font-bold text-sm transition-all shadow-md bg-primary text-on-primary hover:opacity-90";
                    }
                }
                document.getElementById('followers-count').innerText = data.followersCount;
            } else {
                alert(data.error || 'An error occurred.');
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function toggleLike(postId, btn) {
        const token = '<?= $csrf_token ?>';
        try {
            const res = await fetch('/post/' + postId + '/like', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token },
                body: 'csrf_token=' + encodeURIComponent(token)
            });
            const data = await res.json();
            if (data.success) {
                const countSpan = btn.querySelector('span:last-child');
                const iconSpan = btn.querySelector('span:first-child');
                countSpan.innerText = data.likesCount;
                if (data.action === 'liked') {
                    btn.classList.add('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 1";
                } else {
                    btn.classList.remove('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 0";
                }
            }
        } catch (e) { console.error(e); }
    }

    async function toggleBookmark(postId, btn) {
        const token = '<?= $csrf_token ?>';
        try {
            const res = await fetch('/post/' + postId + '/bookmark', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token },
                body: 'csrf_token=' + encodeURIComponent(token)
            });
            const data = await res.json();
            if (data.success) {
                const iconSpan = btn.querySelector('span:first-child');
                if (data.action === 'bookmarked') {
                    btn.classList.add('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 1";
                } else {
                    btn.classList.remove('text-primary');
                    iconSpan.style.fontVariationSettings = "'FILL' 0";
                }
            }
        } catch (e) { console.error(e); }
    }

    // Smart Referral Landing Tab Auto-Selection on load
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            const allowedTabs = ['posts', 'products', 'recommendations', 'about', 'followers-tab', 'following-tab', 'wallet', 'apply-creator', 'settings'];
            if (allowedTabs.includes(tabParam)) {
                // If elements are present, switch
                if (document.getElementById('tab-' + tabParam)) {
                    switchTab(tabParam);
                }
            }
        }
    });
</script>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>
